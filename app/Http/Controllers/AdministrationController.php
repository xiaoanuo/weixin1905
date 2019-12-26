<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
class AdministrationController extends Controller
{
    public function index()
    {
//        echo '<pre>';print_r($_GET);echo '</pre>';die;
        $code = $_GET['code'];
        $data = $this->getAccessToken($code);
        //获取用户信息
        $user_info = $this->getUserInfo($data['access_token'], $data['openid']);
        //保存用户信息
        $userinfo_key = 'h:u:'.$data['openid'];
        Redis::hMset($userinfo_key,$user_info);
    }


    /**
     * 根据code获取access-token
     * @param $code
     * @return mixed
     */
    public function getAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . env('WX_APPID') . '&secret=' . env('WX_SECRET') . '&code=' . $code . '&grant_type=authorization_code';
        $json_data = file_get_contents($url);
        return json_decode($json_data, true);
    }


    /**
     * 获取用户基本信息
     * @param $access_token
     * @param $openid
     * @return mixed
     */
    public function getUserInfo($access_token, $openid)
    {
        $user_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
        $json_data = file_get_contents($user_url);
        $data = json_decode($json_data, true);
//        echo '<pre>';print_r($data);echo '</pre>';die;
        if (isset($data['errcode'])) {
            // 错误处理
            die('出错啦 40001');     //40001 标识：获取用户信息失败
        }
        return $data;   //返回用户信息

    }


}
