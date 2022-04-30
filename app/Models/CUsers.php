<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CUsers extends Model
{
    use HasFactory;
    public $table = 'cUsers';
    public $timestamps = false;

    //子账号登录
    public static function signCUser($request) {
        $msg = [];
        if(in_array('',[$request->username,$request->userId,$request->password,$request->passwordComfirm])) {
            $msg ['status'] = false;
            $msg ['info'] = '数据不能为空!';
            return $msg;
        }

        if($request->password != $request->passwordComfirm) { 
            $msg ['status'] = false;
            $msg ['info'] = '密码不一致!';
            return $msg;
        }

        if(strlen($request->password) < 8) { 
            $msg ['status'] = false;
            $msg ['info'] = '密码不能少于8位!';
            return $msg;
        }
        
        $isBlank = strpos($request->username, ' ');
        if($isBlank) {
            $msg ['status'] = false;
            $msg ['info'] = '名字不能有空格!';
            return $msg;            
        }

        $isExistCUser = self::isExistCUser($request);
        // dump($isExistCUser);die;
        if(empty($isExistCUser)) {
            $time = time();
            DB::table('cUsers')->insert([
                'CUserName' => $request->username,
                'userId' => $request->userId,
                'passwd' => md5($request->password),
                'createTime' => $time,
                'updateTime' => $time
            ]);
        }
        return checkSqlIsSuccess(empty($isExistCUser),$msg,'账号已经注册过','创建成功');
        
    }

    //员工账号登录
    public static function cUserlogin($request) {
        
        $msg = [];
        if(in_array('',[$request->username,$request->password])) {
            $msg ['status'] = false;
            $msg ['info'] = '请输入账号密码!';
            return $msg;
        }
        $isCountExist = DB::table('cUsers')->select('cUserId','userId')->where('cUserName','=',$request->username)->where('passwd','=',md5($request->password))->where('isDel','=','0')->first();
        // dump($isCountExist,$password);die;
        if(isset($isCountExist)) {
            $msg['cUserId'] = $isCountExist->cUserId;
            $msg['userId'] = $isCountExist->userId;
        }

        return checkSqlIsSuccess(isset($isCountExist),$msg,'账号或密码错误','登录成功');
    }


    //储存员工账号信息到session
    public static function savecUserLoginInfoToSession($cUserId){
        if(empty(Session::get('cUserInfo')[$cUserId])) {
            $getLoginInfo = (array)DB::table('cUsers')->select('cUserId','userId','cUserName')->where('cUserId','=',$cUserId)->first();
            foreach($getLoginInfo as $k => $v) {
                $info[$cUserId][$k] = $v;
            }
            Session::put('cUserInfo',$info);
            session()->save();
        }
        $userInfo = Session::get('cUserInfo')[$cUserId];
        return $userInfo;
    }


    //获取子账号信息
    public static function getManageChild($userId) {
        return DB::table('cUsers')->select('cUserId','cUserName')->where('userId','=',$userId)->get();
    }

    //删除子账号
    public static function delChild($request) {
        $msg = [];
        $isCuccess = DB::table('cUsers')->where('cUserId','=',$request->cUserId)->delete();
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');  
    }

    //判断子账号自否存在
    public static function isExistCUser($request){
        return DB::table('cUsers')->where('cUserName','=',$request->username)->where('userId','=',$request->userId)->count();
    }
}
