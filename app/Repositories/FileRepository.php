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
use App\Helpers\ImageHelper;
use App\Models\File;
use App\Services\Facades\ChannelLog;
use Illuminate\Support\Facades\DB;

class FileRepository extends BaseRepository
{
    /**
     * 从数据库中获取识别结果
     */
    protected function getResult($id, $user_id)
    {
        $ids  = File::excisionID($id);
        $code = File::select(['result'])->where([
            ['id_a', '=', $ids['id_a']],
            ['id_b', '=', $ids['id_b']],
            ['user_id', '=', $user_id],
        ])->first();

        if ($code) {
            return $code->result;   //null
        }

        return false;
    }

    /**
     *
     * 将数据写入数据库，并扣费
     *
     */
    protected function deductionAndStorage($user_id, array $file_attributes)
    {
        if (!is_numeric($user_id)) {
            //user id 错误，请联系客服
            ChannelLog::write('error', 'user id 错误，请联系客服');
            throw new ApiStatusException(ApiException::STATUS_USER_NOT_FOUND_ID);
        }

        //先扣费，如果后面入库失败，再把钱加回来
        UserRepository::pointsSubtraction($user_id, $file_attributes['cost']);

        try {
            //入库
            File::create($file_attributes);
        } catch (\Exception $exception) {
            ImageHelper::removeFile($file_attributes['path']);

            //返费
            UserRepository::pointsAdd($user_id, $file_attributes['cost']);

            ChannelLog::write('error', $exception->getMessage());

            if ($exception instanceof ApiStatusException) {
                throw $exception;
            }
            //文件入库失败
            throw new ApiStatusException(ApiStatusException::STATUS_DB_SAVE_ERROR_FILE);
        }

        return true;
    }

    protected function saveData($info, $paths)
    {
        //基于https://github.com/simplephp/snowflake
        $id   = $paths['id'];
        $id_a = substr($id, 0, 13);
        $id_b = substr($id, 13);

        //产生记录并扣分入库
        $attributes = [
            'id_a'         => $id_a,
            'id_b'         => $id_b,
            //            'url'          => $paths['url'],
            'server_id'    => env('SERVER_ID', 0),
            'path'         => $paths['path'],
            'file_type_id' => $info['file_type']->file_type_id,
            'app_id'       => $info['app']->app_id,
            'user_id'      => $info['user_id'],
            'developer_id' => $info['app']->developer_id,
            'cost'         => $info['file_type']->cost,
            'ip'           => ip2long($info['ip']),
        ];

        $this->deductionAndStorage($info['user_id'], $attributes);

        $this->cacheDecodeResult($id, "\0");

        return (string)$id;
    }

    /**
     * @param $id
     * 报错，并返还积分，
     * 这是需要统计报错率，并且做出限制
     */

    protected function report($id, $user_id)
    {
        $ids  = File::excisionID($id);
        $file = File::select(['report', 'cost'])->where([
            ['user_id', '=', $user_id],
            ['id_a', '=', $ids['id_a']],
            ['id_b', '=', $ids['id_b']],
        ])->first();

        if (!$file) {
            //id错误或超过可报错时间
            $this->reportCache($id, ApiStatusException::STATUS_FILE_REPORT_FAIL_REPLAY_OR_TIMEOUT);
            throw new ApiStatusException(ApiStatusException::STATUS_FILE_REPORT_FAIL_REPLAY_OR_TIMEOUT);
        }


        if ($file->report != File::REPORT_STATUS_NOT) {
            $this->reportCache($id, ApiStatusException::STATUS_FILE_REPORT_FAIL_REPLAY_CANT);
            //已报错
            throw new ApiStatusException(ApiStatusException::STATUS_FILE_REPORT_FAIL_REPLAY_CANT);
        }


        try {
            $status = UserRepository::pointsAdd($user_id, $file->cost);
            if (!$status) {
                throw new ApiStatusException(ApiStatusException::STATUS_ACCOUNT_RETURN_POINTS);
            }
        } catch (\Exception $exception) {
            //账户扣点异常
            if ($exception instanceof ApiStatusException) {
                throw $exception;
            } else {
                ChannelLog::write('error', $exception->getMessage());
                throw new ApiStatusException(ApiStatusException::STATUS_ACCOUNT_RETURN_POINTS);
            }
        }

        try {

            $status = DB::update(" update `files` set `report` = ?, `updated_at` = ? where `id_a`= ? and `id_b` = ?", [File::REPORT_STATUS_YES, date('Y-m-d H:i:s'), $ids['id_a'], $ids['id_b']]);

            if (!$status) {
                throw new ApiStatusException(ApiStatusException::STATUS_FILE_REPORT_FAIL);
            }
        } catch (\Exception $exception) {
            ChannelLog::write('error', $exception->getMessage());
            throw new ApiStatusException(ApiStatusException::STATUS_FILE_REPORT_FAIL_UPDATE_SQL_ERROR);
        }
        return $status;
    }


    /**
     *
     * 根据id从缓存中读写识别结果
     */
    protected function cacheDecodeResult($id, $value = null)
    {
        if (!$value) {
            return \Cache::get($this->getEnvCacheKEY($id, 'CACHE_KEY_DECODE_CODE'));
        }

        return \Cache::put($this->getEnvCacheKEY($id, 'CACHE_KEY_DECODE_CODE'), $value, env('CACHE_DECODE_CODE_TTL', 1));
    }

    /***
     *
     * 根據id和env key判斷緩存中是否有對應的KEY
     *
     */
    protected function hasCache($id, $env_key)
    {
        return \Cache::has($this->getEnvCacheKEY($id, $env_key));
    }

    /**
     *
     * 根据id从缓存中读取是否已报错
     *
     */
    protected function reportCache($id, $value = null)
    {

        if (!$value) {
            return \Cache::get($this->getEnvCacheKEY($id, 'CACHE_KEY_REPORT'));
        }

        return \Cache::put($this->getEnvCacheKEY($id, 'CACHE_KEY_REPORT'), $value, env('CACHE_KEY_REPORT_TTL', 1));
    }

}