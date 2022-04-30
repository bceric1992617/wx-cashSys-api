<?php

namespace App\Http\Controllers\vxAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\commonModel;
use App\Models\Users;
use App\Models\CUsers;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\MenuType;
use App\Models\Interest;
use App\Models\UserLoginInfo;
use App\Models\ReceiveCode;
use App\Models\Printer;
use App\Models\ReviewOrderInfo;
use Illuminate\Support\Facades\DB;



class vxAPIController extends Controller
{

    public function login(Request $request) {
        if($request->userLevel == 1) { //1是主账号 2是员工账号
            $userInfo = Users::login($request); 
            if($userInfo['status']) {
                $userInfo['userInfo'] = Users::saveLoginInfoToSession($userInfo['userId']); //保存主账号用户信息到session
            }
            return $userInfo;
        } else {
            $cUserInfo = CUsers::cUserlogin($request);  
            if($cUserInfo['status']) {
                $cUserInfo['userInfo'] = Users::saveLoginInfoToSession($cUserInfo['userId']); //保存主账号用户信息到session
                $cUserInfo['cUserInfo'] = CUsers::savecUserLoginInfoToSession($cUserInfo['cUserId']); //保存员工账号用户信息到session
            }
            return $cUserInfo;
        }
    }
    
    public function sign(Request $request) {
        $userInfo = Users::sign($request);
        if(isset($userInfo['userId'])) {
            $userInfo['userInfo'] = Users::saveLoginInfoToSession($userInfo['userId']); //保存主账号用户信息到session
        }
        return $userInfo;
    }

    public function changePW(Request $request) {
        return Users::changePW($request);
    }

    //获取首页信息
    public function getIndexInfo($userId) {
        return DiningTable::getTableInfo($userId);
        
    }

    //子账号登录
    public function signCUser(Request $request) {
        return CUsers::signCUser($request);
    }


    //获取主账号信息
    public function getUserInfo($userId){
        return Users::saveLoginInfoToSession($userId);
    }
    
    //获取子账号信息
    public function getManageChild($userId) {
        return CUsers::getManageChild($userId);
        
    }
    
    //删除子账号
    public function delChild(Request $request) {
        return CUsers::delChild($request);

    }

    //修改用户名
    public function updateUserName(Request $request) {
        return Users::updateUserName($request);
    }

    //修改店名
    public function updateStoreName(Request $request) {
        return Users::updateStoreName($request);
    }
    
    //获取菜单和菜单类型
    public function getMenuAndMenuType($userId) {
        $menuInfo = [];
        $menuInfo['menuList'] = Menu::getMenuInfo($userId);
        $menuInfo['menuTypeList'] = MenuType::getMenuTypeInfo($userId);
        return $menuInfo;
    }
    
    //添加菜单类型
    public function addMenuType($userId,$menuTypeArr) {
        return MenuType::addMenuType($userId,$menuTypeArr);
    }

    //获取菜单类型
    public function getMenuType($userId) {
        return MenuType::getMenuTypeInfo($userId);
    }
    
    //添加菜单
    public function addMenu($userId,$menuArr) {
        return Menu::addMenu($userId,$menuArr);
    }
    
    //删除菜单
    public function delMenu(Request $request) {
        $userCodeImgDir = public_path() . '/uploads/codeImg/' . $request->input('userId') . '/menuImgs';
        $checkMenuImgExists = Menu::checkMenuImgExists($request->input('menuId'));
        // dump($checkMenuImgExists->imgAddr);die;
        if(!empty($checkMenuImgExists->imgAddr)) { #再次上传删除原来的图片
            unlink(public_path() . $checkMenuImgExists->imgAddr);
        }
        return Menu::delMenu($request);
    }

    //删除菜单类型
    public function delMenuType(Request $request) {
        return MenuType::delMenuType($request);
    }

    //修改菜单库存
    public function updateInventory($menuId) {
        return Menu::updateInventory($menuId);
    }

    //添加账单
    public function updateBill(Request $request) {
        return DiningTable::updateBill($request);
    }

    //获取账单
    public function getBill($tableId) {
        return DiningTable::getBill($tableId);
    }

    //结账
    public function checkout(Request $request) {
        return DiningTable::checkout($request);
    }

    //获取盈利信息
    public function getInterest($userId) {
        return Interest::getInterest($userId);
    }

    //获取排行信息
    public function getHot($userId,$hotType) {
        return Menu::getHot($userId,$hotType);
    }
    
    //检查登录code
    public function checkLogin($userId) {
        return UserLoginInfo::checkLogin($userId);
    }

    //修改登录状态信息
    public function updateLoginInfo($userId,$code) {
        return UserLoginInfo::updateLoginInfo($userId,$code);
    }

    //发送短信验证
    public function sendCode($phone) {
        sendCode($phone);
    }

    //获取收款码图片
    public function getImgMsg($userId) {
        return ReceiveCode::getImgMsg($userId);
    }

    //添加打单机信息
    public function addPrinter(Request $request) {
        return Printer::addPrinter($request);
    }

    //获取打单机信息
    public function getPrinterInfo($userId) {
        return Printer::getPrinterInfo($userId);
    }

    //删除打单机
    public function delPrinter(Request $request) {
        return Printer::delPrinter($request);
    }

    //调用打单机
    public function runPrinter(Request $request) {
        return Printer::runPrinter($request);
    }

    //删除二维码图片
    public function delCodeImg(Request $request) {
        return ReceiveCode::delCodeImg($request);
    }

    //修改图二维码
    public function updateSort($userId,$receiveCodeId) {
        return ReceiveCode::updateSort($userId,$receiveCodeId);
    }

    //上传二维码图片
    public function uploadCodeImg(Request $request) {
        $msg = [];
        
        $file = $_FILES['file'];
        $suffix = explode('/',$file['type'])[1];
        $name = time() . '.' . $suffix;

        $userCodeImgDir = public_path() . '/uploads/codeImg/' . $request->input('userId') . '/';
        if(!is_dir($userCodeImgDir)) {
            mkdir($userCodeImgDir,0777,true);
        }
        
        $count = ReceiveCode::codeImgCode($request->input('userId'));
        if($count >= 2) {
            $msg['status'] = false;
            $msg['info'] = '收款码只允许2张!';
            return $msg;
        }

        $isCodeImgNameExist = ReceiveCode::isCodeImgNameExist($request->input('userId'),$name);
        if($isCodeImgNameExist) {
            $msg['status'] = false;
            $msg['info'] = '图片名字不要一样';
            return $msg;
        }

        if(!checkImgType($suffix)){
            $msg['status'] = false;
            $msg['info'] = '只允许png,jpg,jpeg,gif格式';
            return $msg;
        }
        $imgUrl = asset('/uploads/codeImg/') . '/' . $request->input('userId') . '/' . $name;
        $saveUrl = $userCodeImgDir . $name;
        if (move_uploaded_file($file['tmp_name'], $saveUrl)) {
            return ReceiveCode::uploadCodeImg($request->input('userId'), $imgUrl, $name,$saveUrl);
        } else {
            $msg['status'] = false;
            $msg['info'] = '上传异常';
            return $msg;
        }
    }


    //删除桌台
    public function delTable(Request $request) {
        return DiningTable::delTable($request);
    }

    //添加桌台
    public function addTable($userId,$tableNum) {
        return DiningTable::addTable($userId,$tableNum);
    }

    //点餐二维码
    public function getOrderCode($userId,$storeName,$page) {
        $infoNum = 9;
        $tableObj = DiningTable::where('userId','=',$userId);
        $pageNum = ceil($tableObj->count() / $infoNum);
        $tableInfo = $tableObj->offset(($page - 1) * 9)->limit($infoNum)->get();
        return view("getOrderCode")->with([
            'tableInfo' => $tableInfo,
            'storeName' => $storeName,
            'pageNum' => $pageNum
        ]);
    }

    //上传菜单图片
    public function uploadMenuImg(Request $request) {
        $msg = [];
        $file = $_FILES['file'];
        $name = strFilter($request->input('name'));
        if(!checkImgType(explode('/',$file['type'])[1])){
            $msg['status'] = false;
            $msg['info'] = '只允许png,jpg,jpeg,gif格式';
            return $msg;
        }

        $userCodeImgDir = public_path() . '/uploads/codeImg/' . $request->input('userId') . '/menuImgs';
        if(!is_dir($userCodeImgDir)) { #创建目录
            mkdir($userCodeImgDir,0777,true);
        }
        
        $checkMenuImgExists = Menu::checkMenuImgExists($request->input('menuId'));
        if(!empty($checkMenuImgExists->imgAddr)) { #再次上传删除原来的图片
            unlink(public_path() . $imgAddr);
        }

        $fileName =  time() . '.png';
        $saveUrl = $userCodeImgDir . '/' . $fileName;
        if (move_uploaded_file($file['tmp_name'], $saveUrl)) { #上传图片
            $isCuccess = Menu::where('menuId','=',$request->input('menuId'))->update([
                'imgAddr' => '/uploads/codeImg/' . $request->input('userId') . '/menuImgs/' . $fileName
            ]);
            return checkSqlIsSuccess($isCuccess,$msg,'上传失败','上传成功'); 
        } else {
            $msg['status'] = false;
            $msg['info'] = '上传异常';
            return $msg;
        }

    }

    //添加信息到审核表
    public function addReviewInfo(Request $request) {
        $msg = [];
        if(strlen($request->billInfo) < 5) {
            $msg ['status'] = false;
            $msg ['info'] = '没有任何菜单!';
            return $msg;
        }

        $isCuccess = ReviewOrderInfo::insert([
            'userId' => $request->userId,
            'tableNum' => $request->tableNum,
            'bill' => $request->billInfo
        ]);

        return checkSqlIsSuccess($isCuccess,$msg,'下单失败!','下单成功!'); 
    }

    //获取审核信息
    public function getReviewInfo($userId) {
        $reviewInfo = ReviewOrderInfo::select('userId','tableNum','bill')->where('userId','=',$userId)->orderBy('userId','desc')->get()->toArray();
        $billInfo = array_column($reviewInfo,'bill');
        $reviewMsg = [];
        foreach($reviewInfo as $value) {
            if(empty($reviewMsg[$value['tableNum']]['billInfo'])) {
                $reviewMsg[$value['tableNum']]['billInfo'] = '';
            }
            $reviewMsg[$value['tableNum']]['billInfo'] .= removeFirstAndLast($value['bill']) . ',';
        }
        foreach($reviewMsg as &$value) {
            $value = '[' . trim($value['billInfo'],',') . ']';
        }
        return $reviewMsg;
    }

    //通过审核
    public function passReview(Request $request) {
        $msg = [];
        $getBillInfo = DiningTable::select('tableId','bill','stats')->where('userId','=',$request->userId)->where('tableNum','=',$request->tableNum)->first();
        $getReviewInfo = ReviewOrderInfo::select('bill')->where('userId','=',$request->userId)->where('tableNum','=',$request->tableNum)->get();

        //拼接菜单
        $reviewInfo = '';
        foreach($getReviewInfo as $value) {
            $reviewInfo .= removeFirstAndLast($value->bill) . ',';
        }
        $reviewInfo = trim($reviewInfo,',');
        // dump(123,$getBillInfo,$request->userId, $request->tableNum);die;
        if($getBillInfo->stats == 1) { //1表示桌台的激活的 
            $isCuccess = DiningTable::where('tableId','=',$getBillInfo->tableId)->update([
                'bill' => '[' . removeFirstAndLast($getBillInfo->bill) . ',' . $reviewInfo . ']'
            ]); //追加的菜单
        } else {
            $isCuccess = DiningTable::where('tableId','=',$getBillInfo->tableId)->update([
                'stats' => '1',
                'bill' => '[' . $reviewInfo . ']'
            ]); //新加的菜单
        }
        
        $request->merge(['printInfo' => '[' . $reviewInfo . ']']);
        self::delReivew($request); //删除审核信息
        if($request->printerSN == 'undefined') {
            $msg ['status'] = true;
            $msg ['info'] = '你还没有绑定打单机';
            return $msg;
        }
        return Printer::runPrinter($request); //打印
    }

    //删除审核信息
    public function delReivew(Request $request) {
        $msg = [];
        $isCuccess = ReviewOrderInfo::delReviewInfo($request);
        return checkSqlIsSuccess($isCuccess,$msg,'失败!','成功!'); 
    }
}



