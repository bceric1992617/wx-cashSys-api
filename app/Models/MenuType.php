<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;

class MenuType extends Model
{
    use HasFactory;
    public $table = 'menuType';
    public $timestamps = false;

    //获取菜单类型
    public static function getMenuTypeInfo($userId) {
        return DB::table('menuType')->select('menuTypeId','menuType')->where('userId','=',$userId)->get();
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

    //删除菜单类型
    public static function delMenuType($request) {
        $msg = [];      
        $menuType = DB::table('menuType')->select('menuType')->where('menuTypeId','=',$request->menuTypeId)->first();

        if($menuType->menuType == '其他') {
            $msg ['status'] = false;
            $msg ['info'] = '默认类型不可以删除';
            return $msg;
        }
        
        $isMenuEmpty = Menu::checkMenuNum($request->menuTypeId);
        if($isMenuEmpty) {
            $msg ['status'] = false;
            $msg ['info'] = '删除失败,类型下还有菜单';
            return $msg;
        }

        $isCuccess = DB::table('menuType')->where('menuTypeId','=',$request->menuTypeId)->delete();
        return checkSqlIsSuccess($isCuccess,$msg,'删除失败','删除成功');
    }
}
