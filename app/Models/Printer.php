<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Printer extends Model
{
    use HasFactory;
    public $table = 'printer';
    public $timestamps = false;

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
        $addPrinter = printerAddlist($printerContent); //添加打单机
        if(isset(json_decode($addPrinter)->data->no[0])) {
            $errorInfo = json_decode($addPrinter)->data->no[0];
            $addPrinterIsCuccess = false;
        } else {

            $isCuccess = DB::table('printer')->insert($printerInfo); //储存打单机信息
            if(!$isCuccess) {
                $msg['status'] = false;
                $msg['info'] = '存储失败';
                return $msg;
            }
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

        $delPrinter = printerDelList($request->printerSN); //删除打单机
        $delPrinter = json_decode($delPrinter);
        if(!empty($delPrinter->data->no)) {
            $delPrinterIsCuccess = false;
        } else {
            $isCuccess = DB::table('printer')->where('printerSN','=',$request->printerSN)->delete();
            if(!$isCuccess) {
                $msg['status'] = false;
                $msg['info'] = '删除失败';
                return $msg;
            }
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
