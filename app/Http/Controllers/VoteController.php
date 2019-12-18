<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class VoteController extends Controller
{
    public function index()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
        $code = $_GET['code'];
        $data = $this->getAccessToken($code);
        //获取用户信息
        $user_info = $this->getUserInfo($data['access_token'],$data['openid']);

        //处理业务逻辑
        //判断是否已经投过  使用redis  集合 或有序集合

        $openid = $user_info['openid'];
        $key = 's:vote:zhangsan';
        Redis::Sadd($key,$openid);

        $menbers = Redis::Smembers($key);       //获取所有投票人的openid
        $total = Redis::Scard($key);            //统计投票人数
        echo "投票总人数：".$total;
        echo '<hr>';
        echo '<pre>';print_r($menbers);echo '</pre>';

        $redis_key = 'vote';
        $number = Redis::incr($redis_key);    //incr:增加
        echo "投票成功、当前票数：".$number;
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
        $data =  json_decode($json_data,true);
//        echo '<pre>';print_r($data);echo '</pre>';die;
        if(isset($data['errcode'])){
            // 错误处理
            die('出错啦 40001');     //40001 标识：获取用户信息失败
        }
        return $data;   //返回用户信息

    }

}
