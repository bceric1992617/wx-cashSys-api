<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Printer;
use App\Models\DiningTable;

class Users extends Model
{
    use HasFactory;
    public $table = 'users';
    public $timestamps = false;


    public static function login($request) {             
        $msg = [];
        if(in_array('',[$request->username,$request->password])) {
            $msg ['status'] = false;
            $msg ['info'] = '请输入账号密码!';
            return $msg;
        }
        $isCountExist = DB::table('users')->select('userId')->where('phone','=',$request->username)->where('passwd','=',md5($request->password))->where('isDel','=','0')->first();
        // dump($username,$password,$deviceInfo,$code);die;
        if(isset($isCountExist)) {
            $msg['userId'] = $isCountExist->userId;
            DB::table('userLoginInfo')->where('userId','=',$msg['userId'])->update([ //用户登录信息
                // 'deviceInfo' => $_SERVER['HTTP_USER_AGENT'],
                'deviceInfo' => $request->deviceInfo,
                'userIp' => $_SERVER['REMOTE_ADDR'],
                'code' => $request->code,
                'loginTime' => time()
            ]);
            
        }

        // dump($msg);die;
        return checkSqlIsSuccess(isset($isCountExist),$msg,'账号或密码错误','登录成功');
    }


    //储存主账号信息到session
    public static function saveLoginInfoToSession($userId){
        if(empty(Session::get('userInfo')[$userId])) {
            $getLoginInfo = (array)DB::table('users')->select('userId','userName','phone','email','VIP','storeName')->where('userId','=',$userId)->first();
            foreach($getLoginInfo as $k => $v) {
                $info[$userId][$k] = $v;
            }

            $printerInfo = Printer::getPrinterInfo($userId);
            // dump($printerInfo[0]->printerSN);die;
            if(!empty($printerInfo[0]->printerSN)){
                $info[$userId]['printerSN'] = $printerInfo[0]->printerSN;
            }

            Session::put('userInfo',$info);
            session()->save();
        }
        $userInfo = Session::get('userInfo')[$userId];
        return $userInfo;
    }



    //注册
    public static function sign($request) {
        
        $msg = [];
        if(in_array('',[$request->phone,$request->username,$request->storeName,$request->password,$request->passwordComfirm])) {
            $msg ['status'] = false;
            $msg ['info'] = '数据不能为空!';
            return $msg;
        }

        $isBlank = strpos($request->username, ' ') || strpos($request->storeName, ' ');
        if($isBlank) {
            $msg ['status'] = false;
            $msg ['info'] = '名字和店名不能为空格!';
            return $msg;            
        }

        $isExist = self::isPhoneExist($request->phone);
        if($isExist) {
            $msg ['status'] = false;
            $msg ['info'] = '手机号码注册过!';
            return $msg;
        }

        if(strlen($request->password) < 8) {
            $msg ['status'] = false;
            $msg ['info'] = '密码不能少于8!';
            return $msg;
        }

        if($request->password != $request->passwordComfirm) {
            $msg ['status'] = false;
            $msg ['info'] = '密码不一致!';
            return $msg;
        }
        
        $time = time();
        $md5Pw = md5($request->password);
        $userId = DB::table('users')->insertGetId([
            'userName' => $request->username,
            'storeName' => $request->storeName,
            'passwd' => $md5Pw,
            'phone' => $request->phone,
            'createTime' => $time,
            'updateTime' => $time
        ]);
        
        if($userId) {
            $tableArr = [];
            
            $userCodeImgDir = public_path() . '/uploads/codeImg/' . $userId . '/orderCode';
            $qcode = getOrderCodeSetting();
            if(!is_dir($userCodeImgDir)) {
                mkdir($userCodeImgDir,0777,true);
            }
            for($i = 1; $i <= 20; $i++) { 
                $imgPath = $userCodeImgDir . '/' . $i . '.png';
                createOrderCode($qcode, $imgPath, $i, $userId); //生成对应的点餐二维码
                $tableArr[] = [
                    'userId' => $userId,
                    'tableNum' => $i,
                    'orderCodeAddr' => '/uploads/codeImg/' . $userId . '/orderCode/' . $i . '.png'
                ];
            }

            DB::table('diningTable')->insert($tableArr); //生成20张台
            
            DB::table('menuType')->insert([ //生成默认菜单
                'userId' => $userId,
                'menuType' => '其他'
            ]);
            DB::table('interest')->insert([ //生成默认利润
                'userId' => $userId
            ]);
            DB::table('userLoginInfo')->insert([ //生成默认用户登录信息
                'userId' => $userId,
                'deviceInfo' => '',
                'userIp' => '',
                'code' => '',
                'loginTime' => time()
            ]);

        }
        $msg['userId'] = $userId;
        $msg ['status'] = true;
        $msg ['info'] = '注册成功!';
        return $msg;
    }



    //修改密码
    public static function changePW($request) {
        $msg = [];
        
        if(in_array('',[$request->phone,$request->password,$request->passwordComfirm])) {
            $msg ['status'] = false;
            $msg ['info'] = '数据不能为空!';
            return $msg;
        }

        $isExist = self::isPhoneExist($request->phone);
        if(!$isExist) {
            $msg ['status'] = false;
            $msg ['info'] = '没有这个手机号码!';
            return $msg;
        }

        if($request->password != $request->passwordComfirm) {
            $msg['status'] = false;
            $msg['info'] = '密码不一致';
            return $msg;
        }

        if(strlen($request->password) < 8) {
            $msg ['status'] = false;
            $msg ['info'] = '密码不能少于8!';
            return $msg;
        }

        DB::table('users')->where('phone','=',$request->phone)->update([
            'passwd' => md5($request->password)
        ]);
        $msg['status'] = true;
        $msg['info'] = '修改成功';
        return $msg;
    }


    //修改账号名
    public static function updateUserName($request) {
        $msg = [];
        if($request->username == '') {
            $msg['status'] = false;
            $msg['info'] = '名字不可以为空!';
        } 

        // dump($userId,$username);
        $isCuccess = DB::table('users')->where('userId','=',$request->userId)->update([
            'username' => $request->username
            ]);
        
        return checkSqlIsSuccess($isCuccess,$msg,'修改失败','修改成功');     
    }

    //修改店名
    public static function updateStoreName($request) {
        $msg = [];
        if($request->storeName == '') {
            $msg['status'] = false;
            $msg['info'] = '名字不可以为空!';
        } 

        // dump($userId,$username);
        $isCuccess = DB::table('users')->where('userId','=',$request->userId)->update([
            'storeName' => $request->storeName
            ]);
        // dump($isCuccess);die;
        return checkSqlIsSuccess($isCuccess,$msg,'修改失败','修改成功');  
    }

    //手机号码是否存在
    public static function isPhoneExist($phone) {
        return DB::table('users')->where('phone','=',$phone)->count();
    }
}
