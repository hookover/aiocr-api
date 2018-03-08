<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Translation\Exception\InvalidResourceException;

class ImageHelper extends BaseHelper
{
    protected function decodeBase64Image(/*文件base64数据(包括图片标记)*/
        $base64_image_string, /*允许的文件类型后缀数组*/
        array $allow = ['jpeg', 'png', 'gif', 'bmp', 'jpg'])
    {
        $splited = explode(',', substr($base64_image_string, 5), 2);
        if (count($splited) != 2) {
            throw new InvalidResourceException('数据未包含图片类型');
        }

        $mime = $splited[0];
        $data = $splited[1];

        //数据格式不是base64类型
        if (!base64_encode(base64_decode($data))) {
            throw new InvalidResourceException('此接口只支持base64类型的图片数据');
        }

        $mime_split_without_base64 = explode(';', $mime, 2);
        $mime_split                = explode('/', $mime_split_without_base64[0], 2);

        if (count($mime_split) == 2) {
            $extension = $mime_split[1];
            if ($extension == 'jpeg') {
                $extension = 'jpg';
            }

            if (in_array($extension, $allow)) {
                return [
                    'extension' => $extension,
                    'data'      => $data,
                ];
            }
        }

        throw new InvalidResourceException('图片类型错误');
    }

    protected function removeFile($path)
    {
        return Storage::disk('images_container')->delete($path);
    }

    protected function saveFileImage(UploadedFile $file,/*图片对象*/
                                     $file_name = null, /*文件名称*/
                                     $path = null /*文件路径*/)
    {
        if (!$path) {
            $path = $this->generateImageStoragePathByDatetime();
        }

        $id = app('increment.id')->nextId();

        if (!$file_name) {
            $file_name = $id;
        }

        $ext = $file->getClientOriginalExtension();

        $file_name = $file_name . '.' . $ext;

        $realPath = $file->getRealPath();

        $save_path_file_name = $path . $file_name;

        try {
            Storage::disk('images_container')->put($save_path_file_name, file_get_contents($realPath));
        } catch (\Exception $exception) {
            throw new \Exception('文件保存失败' . $exception->getMessage());
        }

        return [
            'path'      => $save_path_file_name,
            'real_path' => store_real_path($save_path_file_name),
            'id'        => $id,
            //            'url'       => store_url($save_path_file_name),
        ];
    }


    protected function saveBase64Image(/*去掉标记后的图片base64数据*/
        $base64_image_data, /*图片后缀*/
        $file_ext, /*文件名称*/
        $file_name = null, /*文件路径*/
        $path = null)
    {
        if (!$path) {
            $path = $this->generateImageStoragePathByDatetime();
        }

        $id = app('increment.id')->nextId();

        if (!$file_name) {
            $file_name = $id;
        }
        $file_name = $file_name . '.' . $file_ext;


        $save_path_file_name = $path . $file_name;

        try {
//          Storage::disk('images_container')->put($save_path_file_name, base64_decode($base64_image_data));
        } catch (\Exception $exception) {
            throw new HttpException('文件保存失败');

        }

        return [
            'path'      => $save_path_file_name,
            'real_path' => store_real_path($save_path_file_name),
            'id'        => $id,
            //            'url'       => store_url($save_path_file_name),
        ];
    }

    protected function autoSaveBase64Image(/*文件base64数据(包括图片标记)*/
        $base64_image_string, /*文件名称*/
        $file_name = null, /*文件路径*/
        $path = null)
    {
        $data        = $this->decodeBase64Image($base64_image_string);
        $ext         = $data['extension'];
        $base64_data = $data['data'];

        return $this->saveBase64Image($base64_data, $ext, $file_name, $path);
    }

    protected function generateImageStoragePathByDatetime()
    {
        return date("/Ymd/H/") . date('i') % 10 . '/';
    }
}