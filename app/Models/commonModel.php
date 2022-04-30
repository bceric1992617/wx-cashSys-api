<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class commonModel extends Model
{
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
            for($i = 1; $i <= 20; $i++) { 
                $tableArr[] = [
                    'userId' => $userId,
                    'tableNum' => $i,
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

    //主账号登录
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



    public static function getLoginInfo($userId) {
        return Session::get('userInfo')[$userId];
    }

    //添加菜单
    public static function addMenuType($userId,$menuTypeArr) { 
        $msg = [];
        $menuType = array_unique(array_filter(json_decode($menuTypeArr,true))); //去除里面的空值和相同的值
        $menuTypeList = [];
        
        $count = DB::table('menuType')->where('userId','=',$userId)->count(); 
        if(count($menuType) > (15 - $count)) { //限制15条
            $msg ['status'] = false;
            $msg ['info'] = '超过添加条数';
            return $msg;
        }

        foreach($menuType as $k => $v) {
            $menuTypeList[$k]['userId'] = $userId;
            $menuTypeList[$k]['menuType'] = $v;
        }

        $isSuccess = DB::table('menuType')->insert($menuTypeList);
        return checkSqlIsSuccess($isSuccess,$msg,'添加失败','添加成功');
    }


    //获取菜单
    public static function getMenuInfo($userId) {
        return DB::table('menu')->select('menuId','menuName','menuTypeId','price','inventory','tags')->where('userId','=',$userId)->get();
    }

    //获取菜单类型
    public static function getMenuTypeInfo($userId) {
        return DB::table('menuType')->select('menuTypeId','menuType')->where('userId','=',$userId)->get();
    }

    //添加菜单
    public static function addMenu($userId,$menuArr) {
        $msg = [];
        $menuArr = json_decode($menuArr);

        $count = DB::table('menu')->where('userId','=',$userId)->count(); 
        if(count($menuArr) > (50 - $count)) { //限制50条
            $msg ['status'] = false;
            $msg ['info'] = '超过添加条数';
            return $msg;
        }

        $menuList = [];
        $feildList = ['menuName','price','tags','userId','menuTypeId'];
        foreach($menuArr as $key => $value) {
            for($i = 0; $i < count($value); $i++) {
                $menuList[$key][$feildList[$i]] = $value[$i];
            }
        }
        $isSuccess = DB::table('menu')->insert($menuList);
        
        return checkSqlIsSuccess($isSuccess,$msg,'添加失败','添加成功');
    }

    //删除菜单
    public static function delMenu($request) {
        $msg = [];
        $isCuccess = DB::table('menu')->where('menuId','=',$request->menuId)->delete();
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');
    }

    //修改菜单库存
    public static function updateInventory($menuId) {
        $msg = [];
        $inventory = DB::table('menu')->select('inventory')->where('menuId','=',$menuId)->first();
        $isCuccess = DB::table('menu')->where('menuId','=',$menuId)->update([
            'inventory' => $inventory->inventory ? '0' : '1'
            ]);
        return checkSqlIsSuccess($isCuccess,$msg,'修改失败','修改成功');
    }

    //添加账单
    public static function updateBill($request) {
        $msg = [];
        if(strlen($request->billInfo) < 5) {
            $msg ['status'] = false;
            $msg ['info'] = '没有任何菜单!';
            return $msg;
        }

        $isCuccess = DB::table('diningTable')->where('tableId','=',$request->tableId)->update([
            'stats' => '1', //忙碌
            'bill' => str_replace('gray','red',$request->billInfo)
        ]);

        return checkSqlIsSuccess($isCuccess,$msg,'改单失败!','下单成功!'); 
    }

    //删除菜单类型
    public static function delMenuType($request) {
        $msg = [];      
        $menuType = DB::table('menuType')->select('menuType')->where('menuTypeId','=',$request->menuTypeId)->first();

        if($menuType->menuType == '其他') {
            $msg ['status'] = false;
            $msg ['info'] = '默认类型不可以删除';
            return $msg;
        }
        
        $isMenuEmpty = self::checkMenuNum($request->menuTypeId);
        if($isMenuEmpty) {
            $msg ['status'] = false;
            $msg ['info'] = '删除失败,类型下还有菜单';
            return $msg;
        }

        $isCuccess = DB::table('menuType')->where('menuTypeId','=',$request->menuTypeId)->delete();
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');
    }

    //获取盈利信息
    public static function getInterest($userId) {
        return DB::table('interest')->select('today as 今日','1m as 1月','2m as 2月','3m as 3月','4m as 4月','5m as 5月','6m as 6月','7m as 7月','8m as 8月','9m as 9月','10m as 10月','11m as 11月','12m as 12月')->where('userId','=',$userId)->get();
    }


    //检查菜单类型下是否还设有菜单
    public static function checkMenuNum($menuTypeId) {
        return DB::table('menu')->where('menuTypeId','=',$menuTypeId)->count();
    }

    //储存主账号信息到session
    public static function saveLoginInfoToSession($userId){
        if(empty(Session::get('userInfo')[$userId])) {
            $getLoginInfo = (array)DB::table('users')->select('userId','userName','phone','email','VIP','storeName')->where('userId','=',$userId)->first();
            foreach($getLoginInfo as $k => $v) {
                $info[$userId][$k] = $v;
            }

            $printerInfo = self::getPrinterInfo($userId);
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

    //获取账单信息
    public static function getBill($tableId) {
        $billInfo = DB::table('diningTable')->select('bill')->where('tableId','=',$tableId)->first();
        return json_decode($billInfo->bill);
        
    }

    //获取排行信息
    public static function getHot($userId,$hotType) {
        if($hotType == 1) { //1是日排，2是月排
            return DB::table('menu')->select('menuName','price','daySaleNum as saleNum')->where('userId','=',$userId)->orderBy('daySaleNum','desc')->get();
        } else {
            return DB::table('menu')->select('menuName','price','monthSaleNum as saleNum')->where('userId','=',$userId)->orderBy('monthSaleNum','desc')->get();
        }
    }
    
    //结账
    public static function checkout($request) {
        
        $msg = [];
        $billArr = json_decode($request->billInfo);
        $month = date('m');
        $saleNum = $interest = 0;
        foreach($billArr as $value) {
            $saleNum += $value[3];
            $interest += $value[1];
        }
        DB::statement("update interest set today=today+". $interest .", ". $month ."m=". $month ."m+". $interest ." where userId=". $request->userId); //盈利
        DB::statement("update menu set daySaleNum=daySaleNum+". $saleNum .", monthSaleNum=monthSaleNum+". $saleNum ." where menuName='". $value[0] ."' and userId =". $request->userId); //卖出多少
        $isCuccess = DB::table('diningTable')->where('tableId','=',$request->tableId)->update([ //结账
            'stats'=>'0',
            'bill'=> NULL
        ]);
        return checkSqlIsSuccess($isCuccess,$msg,'结账失败','结账成功');
        
    }

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

        $isExistCUser = self::isExistCUser($request->username);
        // dump($isExistCUser);die;
        if(empty($isExistCUser)) {
            $time = time();
            DB::table('CUsers')->insert([
                'CUserName' => $request->username,
                'userId' => $request->userId,
                'passwd' => md5($request->password),
                'createTime' => $time,
                'updateTime' => $time
            ]);
        }
        return checkSqlIsSuccess(empty($isExistCUser),$msg,'账号已经注册过','创建成功');
        
    }

    //删除子账号
    public static function delChild($request) {
        $msg = [];
        $isCuccess = DB::table('CUsers')->where('cUserId','=',$request->cUserId)->delete();
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');  
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

    //判断子账号自否存在
    public static function isExistCUser($username){
        return DB::table('CUsers')->where('cUserName','=',$username)->count();
    }

    //获取子账号信息
    public static function getManageChild($userId) {
        return DB::table('CUsers')->select('cUserId','cUserName')->where('userId','=',$userId)->get();
    }

    //获取信息
    public static function getTableInfo($userId) {
        return DB::table('diningTable')->select('tableId','tableNum','stats')->where('userId','=',$userId)->get();
    }

    //手机号码是否存在
    public static function isPhoneExist($phone) {
        return DB::table('users')->where('phone','=',$phone)->count();
    }

    //检查登录
    public static function checkLogin($userId) {
        // dump($userId);die;
        return DB::table('userLoginInfo')->select('code','userIp','deviceInfo')->where('userId','=',$userId)->take(1)->get();
        
    }

    //修改登录状态信息
    public static function updateLoginInfo($userId,$code) {
        return DB::table('userLoginInfo')->where('userId','=',$userId)->update([
            'code' => $code
        ]);
    }

    //上传图片的数据写到数据库
    public static function uploadCodeImg($userId,$path,$name,$saveDir) {
        $msg = [];

        $isCuccess = DB::table('receiveCode')->insert([
            'userId' => $userId,
            'imgName' => $name,
            'imgUrl' => $path,
            'saveDir' => $saveDir
        ]);

        return checkSqlIsSuccess($isCuccess,$msg,'修改失败','修改成功'); 
    }

    public static function codeImgCode($userId){
        return DB::table('receiveCode')->where('userId','=',$userId)->count();
    }


    public static function isCodeImgNameExist($userId,$name){
        return DB::table('receiveCode')->where('userId','=',$userId)->where('imgName','=',$name)->count();
    }
    
    //获取收款码图片
    public static function getImgMsg($userId) {
        return DB::table('receiveCode')->select('receiveCodeId','imgName','imgUrl','isMain')->where('userId','=',$userId)->get();
    }
    
    //删除收款码图片
    public static function delCodeImg($request) {
        $msg = [];

        $receiveCodeMsg = DB::table('receiveCode')->select('saveDir')->where('receiveCodeId','=',$request->receiveCodeId)->first();  
        if(isset($receiveCodeMsg->saveDir)) {
            unlink($receiveCodeMsg->saveDir); //删除文件
        }

        $isCuccess = DB::table('receiveCode')->where('receiveCodeId','=',$request->receiveCodeId)->delete(); //删除文件SQL数据
        if(!$isCuccess) {
            $msg['status'] = false;
            $msg['info'] = '删除失败!';
            return $msg;
        } 
        
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');  
        
    }
    
    //修改主收款码
    public static function updateSort($userId,$receiveCodeId) {
        $msg = [];
        DB::table('receiveCode')->where('userId','=',$userId)->update([
            'isMain' => '0'
            ]); 

        $isCuccess = DB::table('receiveCode')->where('receiveCodeId','=',$receiveCodeId)->update([
            'isMain' => '1'
        ]); 
        return checkSqlIsSuccess($isCuccess,$msg,'修改失败','修改成功'); 
    }

    //添加打单机
    public static function addPrinter($request) {
        
        $msg = [];
        if(in_array('', [$request->sn,$request->key])) {
            $msg['status'] = false;
            $msg['info'] = 'SN和KEY不许为空';
            return $msg;
        }

        $isExist = DB::table('printer')->where('printerSN', '=', $request->sn)->count();
        if($isExist) {
            $msg['status'] = false;
            $msg['info'] = '打单机已存在';
            return $msg;
        }


        $printerInfo = [];
        $printerContent = $request->sn . '#' . $request->key;
        if(!empty(strFilter($request->printerName))) {
            $printerContent = $printerContent . '#' . $request->printerName;
            $printerInfo['printerName'] = $request->printerName;
        }
        $printerInfo['printerSN'] = $request->sn;
        $printerInfo['printerKey'] = $request->key;
        $printerInfo['userId'] = $request->userId;
        $isCuccess = DB::table('printer')->insert($printerInfo); //储存打单机信息
        if(!$isCuccess) {
            $msg['status'] = false;
            $msg['info'] = '存储失败';
            return $msg;
        }


        $addPrinter = printerAddlist($printerContent); //添加打单机
        if(isset(json_decode($addPrinter)->data->no[0])) {
            $errorInfo = json_decode($addPrinter)->data->no[0];
            $addPrinterIsCuccess = false;
        } else {
            $errorInfo = '';
            $addPrinterIsCuccess = true;
        }

        return checkSqlIsSuccess($addPrinterIsCuccess,$msg,$errorInfo,'添加成功'); 
    }

    //获取打单机信息
    public static function getPrinterInfo($userId) {
        return DB::table('printer')->where('userId','=',$userId)->get();
    } 

    //删除打单机
    public static function delPrinter($request) {
        $msg = [];
        $isCuccess = DB::table('printer')->where('printerSN','=',$request->printerSN)->delete();
        if(!$isCuccess) {
            $msg['status'] = false;
            $msg['info'] = '删除失败';
            return $msg;
        }

        $delPrinter = printerDelList($request->printerSN); //删除打单机
        $delPrinter = json_decode($delPrinter);
        if(!empty($delPrinter->data->no)) {
            $delPrinterIsCuccess = false;
        } else {
            $delPrinterIsCuccess = true;
        }
        return checkSqlIsSuccess($delPrinterIsCuccess,$msg,$delPrinter->data->no,'删除成功'); 
    } 

    //调用打单机
    public static function runPrinter($request) {
        $msg = [];
        $printInfo = json_decode($request->printInfo);
        // return $request->printInfo;
        $printInfoArr = [];
        foreach($printInfo as $k => $v) {
            $printInfoArr[$k]['title'] = $v[0];
            $printInfoArr[$k]['price'] = $v[1];
            $printInfoArr[$k]['num'] = $v[3];
            
            if($v[2] != '') {
                $printInfoArr[$k]['title'] = $printInfoArr[$k]['title'] . '('. $v[2] .')';
            }         
        }
        $content = composing($printInfoArr,14,6,3,6,$request->storeName,$request->tableNum);
        $isCuccess = printMsg($request->printerSN,$content,1);
        $isCuccess = json_decode($isCuccess);
     
        if($isCuccess->ret) {
            $printIsCuccess = false;
        } else {
            $printIsCuccess = true;
        }
        return checkSqlIsSuccess($printIsCuccess,$msg,$isCuccess->msg,'打单成功'); 
        
    }
}



