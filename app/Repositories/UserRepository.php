<?php
/**
 * Created by PhpStorm.
 * User: hookover
 * Date: 17-10-16
 * Time: 下午3:38
 */

namespace App\Repositories;


use App\Exceptions\ApiException;
use App\Exceptions\ApiStatusException;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository
{
    /*
     * 用户返费
     */
    protected function pointsAdd($user_id, $cost)
    {
        $status = DB::update("UPDATE users SET point_pay_current=point_pay_current+{$cost} WHERE user_id={$user_id}");
        if ($status) {
            return true;
        }
        //'帐户返费异常，请联系管理员'
        throw new ApiStatusException(ApiException::STATUS_ACCOUNT_RETURN_POINTS);
    }

    /*
         * 用户扣费
         */
    protected function pointsSubtraction($user_id, $cost)
    {
        try {
            $status = DB::update("UPDATE users SET point_pay_current=point_pay_current-{$cost} WHERE user_id={$user_id} AND point_pay_current >= {$cost}");
            if ($status) {
                return true;
            }
            $this->removeCacheByUID($user_id);
        } catch (\Exception $exception) {
            //帐户扣费异常，请联系管理员
            throw new ApiStatusException(ApiException::STATUS_ACCOUNT_DEDUCT_POINTS);
        }
        //帐户余额不足,请及时充值
        throw new ApiStatusException(ApiException::STATUS_USER_NOT_HAVE_POINT);
    }


    /*
     * 根据token获取用户，这是一个特别的token,因为带了UID
     */
    protected function findByToken($token_uid)
    {
        $uid   = substr($token_uid, 64);
        $token = substr($token_uid, 0, 64);

        if (!$uid || !$token) {
            //token格式错误
            throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_FORMAT);
        }

        $user = $this->findUserByUID($uid);

        if (!$user) {
            //用户不存在
            throw new ApiStatusException(ApiException::STATUS_USER_NOT_FOUND);
        }

        return $user;
    }

    /*
     * 根据JWT token获取用户
     */
    protected function findByJWTToken($token)
    {
        $token_arr = explode('.', $token);

        if (count($token_arr) == 3) {
            $payload = (array)json_decode(base64_decode($token_arr[1]));

            if (!array_key_exists('id', $payload) || !is_numeric($payload['id'])) {
                //token 校验失败
                throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_VERIFICATION);
            }

            if (!array_key_exists('exp', $payload) || !is_numeric($payload['exp']) || $payload['exp'] < time()) {
                //token 已过期
                throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_EXPIRED);
            }

            $user = $this->findUserByUID($payload['id']);

            if (!$user) {
                //token对应的用户不存在
                throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_USER_NOT_FOUND);
            }

            try {
                if (JWT::decode($token, $user->salt, ['HS256'])) {
                    return $user;
                }
                //token验证失败
                throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_VERIFICATION);
            } catch (\Exception $exception) {
                //token验证失败
                throw new ApiStatusException(ApiException::STATUS_TOKEN_FAIL_VERIFICATION);
            }
        }
    }

    public function findUserByUID($user_id)
    {
        $user = $this->findCacheByUID($user_id);
        if ($user) {
            return $user;
        }

        $user = User::select(['id', 'user_id', 'email', 'password', 'api_token', 'salt', 'point_pay_current'])->where('user_id', '=', $user_id)->first();

        if ($user) {
            $this->setCacheUser($user);
        }

        return $user;
    }


    /*
     * 根据user_id从缓存中查找用户
     */
    protected function findCacheByUID($user_id)
    {
        if (!$user_id) {
            return false;
        }

        $user = \Cache::get($this->getEnvCacheKEY($user_id, 'CACHE_KEY_USER_INFO'));

        return $user ? unserialize($user) : null;
    }

    /*
     * 将用户信息存入缓存
     */
    protected function setCacheUser(User $user)
    {
        if ($user->user_id) {
            $key = $this->getEnvCacheKEY($user->user_id, 'CACHE_KEY_USER_INFO');
            \Cache::put($key, serialize($user), env('CACHE_KEY_USER_INFO_TTL', 5));
            $this->setCacheLinkByEmail($key, $user->email);
        }
    }

    /*
     * 设置用户缓存KEY与email之间的联系
     */
    protected function setCacheLinkByEmail($cache_id, $email)
    {
        if (!($cache_id && $email)) {
            return false;
        }

        return \Cache::put($this->getEnvCacheKEY($email, 'CACHE_KEY_USER_INFO_LINK_EMAIL'), $cache_id, env('CACHE_KEY_USER_INFO_TTL', 5));
    }

    /*
     * 根据用户ID删除缓存
     */
    protected function removeCacheByUID($user_id)
    {
        return \Cache::forget($this->getEnvCacheKEY($user_id, 'CACHE_KEY_USER_INFO'));
    }

    /*
     * 根据邮件地址从数据库中取出用户
     */
    protected function findByEmail($email)
    {
        return User::select(['id', 'user_id', 'email', 'password', 'api_token', 'salt', 'point_pay_current'])->where('email', '=', $email)->first();
    }

    /*
     * 根据邮件地址先从缓存中取出用户，若缓存中不存在，则从数据库中获取用户
     */
    protected function findCacheByEmail($email)
    {
        $link_user_key = \Cache::get($this->getEnvCacheKEY($email, 'CACHE_KEY_USER_INFO_LINK_EMAIL'));
        if ($link_user_key) {
            $user = \Cache::get($link_user_key);

            if ($user) {
                return unserialize($user);
            }
        }

        $user = $this->findByEmail($email);

        if ($user) {
            $this->setCacheUser($user);
        }

        return $user;
    }
}