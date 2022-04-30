<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReceiveCode extends Model
{
    use HasFactory;
    public $table = 'receiveCode';
    public $timestamps = false;

    //上传图片的数据写到数据库
    public static function uploadCodeImg($userId,$path,$name,$saveDir) {
        $msg = [];

        $isCuccess = DB::table('receiveCode')->insert([
            'userId' => $userId,
            'imgName' => $name,
            'imgUrl' => $path,
            'saveDir' => $saveDir
        ]);

        return checkSqlIsSuccess($isCuccess,$msg,'上传失败','上传成功'); 
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
}
