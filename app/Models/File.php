<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $hidden = [
        'preparation_a',
        'preparation_b',
        'preparation_c',
        'preparation_d',
        'preparation_e',
        'deleted_at',
    ];

    protected $guarded = [
        'preparation_a',
        'preparation_b',
        'preparation_c',
        'preparation_d',
        'preparation_e',
    ];


    const STATUS_PENDING    = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SUCCESS    = 2;
    const STATUS_FAILED     = 3;
    const STATUS_TIMEOUT    = 4;


    const REPORT_STATUS_YES = 1;
    const REPORT_STATUS_NOT = null;


    public static $status = [
        self::STATUS_PENDING    => '待处理',
        self::STATUS_PROCESSING => '处理中',
        self::STATUS_SUCCESS    => '成功',
        self::STATUS_FAILED     => '失败',
        self::STATUS_TIMEOUT    => '超时',
    ];


    public static $report_status = [
        self::REPORT_STATUS_YES => '已报错',
        self::REPORT_STATUS_NOT => '未报错',
    ];


    //将ID分割
    public static function excisionID(&$id)
    {
        $id_a = substr($id, 0, 13);
        $id_b = substr($id, 13);

        return [
            'id_a' => $id_a,
            'id_b' => $id_b,
        ];
    }

    protected static function combineID($id_a,$id_b)
    {
        return $id_a . $id_b;
    }

    protected function id()
    {
        return $this->id_a . $this->id_b;
    }
}
