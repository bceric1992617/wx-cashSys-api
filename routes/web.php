<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/vxAPI/sign', "vxAPI\\vxAPIController@sign");

Route::post('/vxAPI/login', "vxAPI\\vxAPIController@login");

Route::get('/vxAPI/getIndexInfo/{userId}', "vxAPI\\vxAPIController@getIndexInfo");
Route::post('/vxAPI/changePW', "vxAPI\\vxAPIController@changePW");

Route::post('/vxAPI/signCUser', "vxAPI\\vxAPIController@signCUser");

Route::get('/vxAPI/getUserInfo/{userId}', "vxAPI\\vxAPIController@getUserInfo");
Route::get('/vxAPI/getManageChild/{userId}', "vxAPI\\vxAPIController@getManageChild");
Route::delete('/vxAPI/delChild', "vxAPI\\vxAPIController@delChild");
Route::post('/vxAPI/updateUserName', "vxAPI\\vxAPIController@updateUserName");
Route::post('/vxAPI/updateStoreName', "vxAPI\\vxAPIController@updateStoreName");
Route::get('/vxAPI/getMenuAndMenuType/{userId}', "vxAPI\\vxAPIController@getMenuAndMenuType");
Route::get('/vxAPI/addMenuType/{userId}/{MenuTypeArr}', "vxAPI\\vxAPIController@addMenuType");
Route::get('/vxAPI/addMenu/{userId}/{menuArr}', "vxAPI\\vxAPIController@addMenu");
Route::post('/vxAPI/uploadMenuImg', "vxAPI\\vxAPIController@uploadMenuImg");
Route::get('/vxAPI/getMenuType/{userId}', "vxAPI\\vxAPIController@getMenuType");
Route::delete('/vxAPI/delMenu', "vxAPI\\vxAPIController@delMenu");
Route::delete('/vxAPI/delMenuType', "vxAPI\\vxAPIController@delMenuType");
Route::get('/vxAPI/updateInventory/{menuId}', "vxAPI\\vxAPIController@updateInventory");
Route::post('/vxAPI/updateBill', "vxAPI\\vxAPIController@updateBill");
Route::get('/vxAPI/getBill/{tableId}', "vxAPI\\vxAPIController@getBill");
Route::post('/vxAPI/checkout', "vxAPI\\vxAPIController@checkout");
Route::get('/vxAPI/getInterest/{userId}', "vxAPI\\vxAPIController@getInterest");
Route::get('/vxAPI/getHot/{userId}/{hotType}', "vxAPI\\vxAPIController@getHot");
Route::get('/vxAPI/checkLogin/{uchserId}', "vxAPI\\vxAPIController@checkLogin");
Route::get('/vxAPI/updateLoginInfo/{userId}/{code}', "vxAPI\\vxAPIController@updateLoginInfo");
Route::get('/vxAPI/sendCode/{phone}', "vxAPI\\vxAPIController@sendCode");
Route::get('/vxAPI/getImgMsg/{userId}', "vxAPI\\vxAPIController@getImgMsg");
Route::delete('/vxAPI/delCodeImg', "vxAPI\\vxAPIController@delCodeImg");
Route::get('/vxAPI/updateSort/{userId}/{receiveCodeId}', "vxAPI\\vxAPIController@updateSort");
Route::post('/vxAPI/addPrinter', "vxAPI\\vxAPIController@addPrinter");

Route::get('/vxAPI/getPrinterInfo/{userId}', "vxAPI\\vxAPIController@getPrinterInfo");
Route::delete('/vxAPI/delPrinter', "vxAPI\\vxAPIController@delPrinter");
Route::post('/vxAPI/runPrinter', "vxAPI\\vxAPIController@runPrinter");

Route::post('/vxAPI/uploadCodeImg', "vxAPI\\vxAPIController@uploadCodeImg");
Route::get('/vxAPI/addTable/{userId}/{tableNum}', "vxAPI\\vxAPIController@addTable");
Route::delete('/vxAPI/delTable', "vxAPI\\vxAPIController@delTable");

Route::get('/vxAPI/getOrderCode/{userId}/{storeName}/{page?}', "vxAPI\\vxAPIController@getOrderCode");

Route::post('/vxAPI/addReviewInfo', "vxAPI\\vxAPIController@addReviewInfo");
Route::post('/vxAPI/passReview', "vxAPI\\vxAPIController@passReview");
Route::delete('/vxAPI/delReivew', "vxAPI\\vxAPIController@delReivew");
Route::get('/vxAPI/getReviewInfo/{userId}', "vxAPI\\vxAPIController@getReviewInfo");

Route::get('/test', function() {
    echo 'cashSys';
});





