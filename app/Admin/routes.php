<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/wxsendmsg', 'WxMsgController@sendMsg');
    $router->get('/wxsendimg', 'WxImgController@sendImg');
    $router->resource('users',WxUserController::class);
    $router->resource('detail',WxGoodsController::class);
    $router->resource('wx/media/img', WxTypeImg::class);       // 图片素材管理

});
