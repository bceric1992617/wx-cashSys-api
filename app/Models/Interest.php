<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Interest extends Model
{
    use HasFactory;
    public $table = 'interest';
    public $timestamps = false;

    //获取盈利信息
    public static function getInterest($userId) {
        return DB::table('interest')->select('today as 今日','01m as 1月','02m as 2月','03m as 3月','04m as 4月','05m as 5月','06m as 6月','07m as 7月','08m as 8月','09m as 9月','10m as 10月','11m as 11月','12m as 12月')->where('userId','=',$userId)->get();
    }
}
