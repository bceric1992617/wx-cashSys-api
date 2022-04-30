<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DiningTable extends Model
{
    use HasFactory;
    public $table = 'diningTable';
    public $timestamps = false;

    //获取信息
    public static function getTableInfo($userId) {
        return DB::table('diningTable')->select('tableId','tableNum','stats','orderCodeAddr')->where('userId','=',$userId)->orderBy('tableId','desc')->get();
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


        if($isCuccess) {
            $msg ['status'] = false;
            $msg ['info'] = '下单成功';
            return $msg;
        } else {
            $msg ['status'] = false;
            $msg ['info'] = '没有改动';
            return $msg;
        }
        // return checkSqlIsSuccess($isCuccess,$msg,'菜单没变!','下单成功!'); 
    }

    //获取账单信息
    public static function getBill($tableId) {
        $billInfo = DB::table('diningTable')->select('bill')->where('tableId','=',$tableId)->first();
        return json_decode($billInfo->bill);
        
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
    
    //删除桌台
    public static function delTable($request) {
        $msg = [];
        if(self::tableStatus($request)) {
            $msg ['status'] = false;
            $msg ['info'] = '请先结算这张台';
            return $msg;
        }
        
        $userCodeImgDir = public_path() . '/uploads/codeImg/' . $request->userId . '/orderCode/' . $request->tableNum . '.png';
        // dump($userCodeImgDir,123);die;
        if(file_exists($userCodeImgDir)) {
            unlink($userCodeImgDir); #删除对应二维码图片
        }
        
        $isCuccess = DB::table('diningTable')->where('tableNum','=',$request->tableNum)->where('userId','=',$request->userId)->delete();
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');
    }

    //添加桌台
    public static function addTable($userId,$tableNum) {
        $tableNum += 1;
        $msg = [];
        if(self::checkTableNum($userId) > 20) {
            $msg ['status'] = false;
            $msg ['info'] = '不能超过20桌';
            return $msg;
        }

        $userCodeImgDir = public_path() . '/uploads/codeImg/' . $userId . '/orderCode';
        $imgPath = $userCodeImgDir . '/' . $tableNum . '.png';
        $qcode = getOrderCodeSetting();
        createOrderCode($qcode, $imgPath, $tableNum, $userId); //生成对应的点餐二维码
        $isCuccess = DB::table('diningTable')->insert([
            'userId' => $userId,
            'tableNum' => $tableNum,
            'orderCodeAddr' => '/uploads/codeImg/' . $userId . '/orderCode/' . $tableNum . '.png'
        ]);
        return checkSqlIsSuccess($isCuccess,$msg,'添加失败','添加成功');
    }

    //添加点餐二维码
    public static function addCodeAddr($userId) {
        DB::table('diningTable')->where('userId','=',$userId)->update();
    }



    
    //检查台桌状态
    public static function tableStatus($request) {
        return DB::table('diningTable')->select('stats')->where('tableNum','=',$request->tableNum)->where('userId','=',$request->userId)->first()->stats;
    }

    //检查台桌数量
    public static function checkTableNum($userId) {
        return DB::table('diningTable')->where('userId','=',$userId)->count();
    }
}


