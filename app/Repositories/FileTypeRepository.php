<?php
/**
 * Created by PhpStorm.
 * User: hookover
 * Date: 17-10-16
 * Time: 下午3:38
 */

namespace App\Repositories;

use App\Models\FileType;

class FileTypeRepository extends BaseRepository
{
    protected function retrieveByFileTypeID($file_type_id)
    {
        //缓存有么？
        $file_type = \Cache::get($this->getEnvCacheKEY($file_type_id, 'CACHE_KEY_FILE_TYPE_KEY'));
        if ($file_type) {
            return $file_type != "\0" ? unserialize($file_type) : false;

        }

        $file_type = FileType::select(["file_type_id", "cost", "length", "ai_enable"])
            ->where(['file_type_id' => $file_type_id, 'status' => 1])
            ->first();

        if ($file_type) {
            \Cache::put($this->getEnvCacheKEY($file_type_id, 'CACHE_KEY_FILE_TYPE_KEY'), serialize($file_type), env('CACHE_FILE_TYP_TTL', 2880));

            return $file_type;
        }


        //如果不存在，就放存个空key
        \Cache::put($this->getEnvCacheKEY($file_type_id, 'CACHE_KEY_FILE_TYPE_KEY'), "\0", env('CACHE_FILE_TYP_TTL', 2880));

        return false;
    }
}