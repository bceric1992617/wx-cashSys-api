<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Menu extends Model
{
    use HasFactory;
    public $table = 'menu';
    public $timestamps = false;

    //获取菜单
    public static function getMenuInfo($userId) {
        return DB::table('menu')->select('menuId','menuName','menuTypeId','price','inventory','tags','imgAddr')->where('userId','=',$userId)->get();
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
            if($value[0] != '') {
                for($i = 0; $i < count($value); $i++) {
                    $menuList[$key][$feildList[$i]] = $value[$i];
                }
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

    //获取排行信息
    public static function getHot($userId,$hotType) {
        if($hotType == 1) { //1是日排，2是月排
            return DB::table('menu')->select('menuName','price','daySaleNum as saleNum')->where('userId','=',$userId)->orderBy('daySaleNum','desc')->get();
        } else {
            return DB::table('menu')->select('menuName','price','monthSaleNum as saleNum')->where('userId','=',$userId)->orderBy('monthSaleNum','desc')->get();
        }
    }


    //检查菜单类型下是否还设有菜单
    public static function checkMenuNum($menuTypeId) {
        return DB::table('menu')->where('menuTypeId','=',$menuTypeId)->count();
    }


    public static function checkMenuImgExists($menuId) {
        return Menu::select('imgAddr')->where('menuId','=',$menuId)->first();
    }


}
