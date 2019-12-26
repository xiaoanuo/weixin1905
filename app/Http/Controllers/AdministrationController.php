<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
class AdministrationController extends Controller
{
    public function index()
    {
                echo '<pre>';print_r($_GET);echo '</pre>';die;
        $code = $_GET['code'];
        $data = $this->getAccessToken($code);
        //获取用户信息
        $user_info = $this->getUserInfo($data['access_token'], $data['openid']);
        //保存用户信息
        $userinfo_key = 'h:u:'.$data['openid'];
        dd($userinfo_key);
        Redis::hMset($userinfo_key,$user_info);
    }
}
