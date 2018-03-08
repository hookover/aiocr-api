<?php
/**
 * Created by PhpStorm.
 * User: hookover
 * Date: 17-10-30
 * Time: 下午10:47
 */

namespace App\Services;

use App\Contracts\IncrementIDContract;

class IncrementIDService extends IncrementIDContract
{
    protected $server_id = 0;

    public function __construct()
    {
        $this->server_id = env('SERVER_ID', 0);
    }

    //生成一个ID
    public function nextId()
    {
        //基于https://github.com/simplephp/snowflake
        $id = \PhpSnowFlake::nextId($this->server_id);
        return $id;
    }
}