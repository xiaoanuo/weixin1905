<?php

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

Route::get('/info', function () {
    phpinfo();
});
//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/','Index\\IndexController@index');



Route::any('user/addUser','User\\LoginController@addUser');
Route::get('cs/ddd','WeiXin\\WxController@ddd');
Route::get('test/xml','WeiXin\\WxController@xmlTest');

Route::get('cs/redis','WeiXin\\WxController@test');
Route::get('dev/redis/del','VoteController@delkey');

//微信开发
Route::get('wx','WeiXin\\WxController@wechat');
Route::post('wx','WeiXin\\WxController@receiv');   //接受微信的推送事件
Route::get('wx/media','WeiXin\\WxController@getMedia');    //获取图片
Route::get('wx/menu','WeiXin\\WxController@createMenu');    //创建菜单
Route::get('/wx/flush/access_token','WeiXin\WxController@flushAccessToken');  // 刷新access_token

//微信公众号
Route::get('/vote','VoteController@index');    //微信投票
Route::get('/wx/tasks','PlanningController@sendMsg');    //计划任务


//微商城
Route::get('/goods/detail','Goods\\IndexController@detail');    //商品详情


Route::get('/curriculum','CurriculumController@index');    //课程
Route::get('/index','AdministrationController@index');    //管理
Route::get('/add','AdministrationController@add');    //管理








