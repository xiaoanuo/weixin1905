<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
        $code = $_GET['code'];
        $data = $this->getAccessToken($code);
        //获取用户信息
        $user_info = $this->getUserInfo($data['access_token'],$data['openid']);
    }

    public function  getAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $json_data = file_get_contents($url);
        return json_decode($json_data,true);
    }

    /**
     * 获取用户基本信息
     * @param $access_token
     * @param $openid
     * @return mixed
     */
    public function getUserInfo($access_token,$openid)
    {
        $user_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $json_data = file_get_contents($user_url);
        $user_info = json_decode($json_data,true);
        echo '<pre>';print_r($user_info);echo '</pre>';
    }

}
