<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLoginInfo extends Model
{
    use HasFactory;
    public $table = 'userLoginInfo';
    public $timestamps = false;

    //修改登录状态信息
    public static function updateLoginInfo($userId,$code) {
        return DB::table('userLoginInfo')->where('userId','=',$userId)->update([
            'code' => $code
        ]);
    }

    //检查登录
    public static function checkLogin($userId) {
        // dump($userId);die;
        return DB::table('userLoginInfo')->select('code','userIp','deviceInfo')->where('userId','=',$userId)->take(1)->get();
        
    }
}
