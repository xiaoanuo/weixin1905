<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class VoteController extends Controller
{
    public function delkey()
    {
        $key = $_GET['k'];
        echo 'Delete key:'.$key;echo '</br>';
        Redis::del($key);
    }

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

        //处理业务逻辑
        //判断是否已经投过  使用redis  集合 或有序集合

        $openid = $user_info['openid'];
        $key = 'ss:vote:zhangsan';

        //判断是否已经投过票
        if (Redis::zrank($key, $user_info['openid'])) {
//            echo '您已经投过、宝贵的一票';
        } else {
            Redis::Zadd($key, time(), $openid);
        }

        $total = Redis::zCard($key);        // 获取总数
        echo '投票总人数： ' . $total;echo '</br>';
        $members = Redis::zRange($key, 0, -1, true);       // 获取所有投票人的openid
        //echo '<pre>',print_r($members);echo'</pre>';echo '<hr>';
        foreach ($members as $k => $v) {
            //echo "用户： " . $k . ' 投票时间: ' . date('Y-m-d H:i:s', $v);echo '</br>';
            $u_k = 'h:u:'.$k;
            $u = Redis::hgetAll($u_k);
            //$u = Redis::hMget($u_k,['openid','nickname','sex','headimgurl']);
            //echo '<pre>',print_r($u);echo'</pre>';echo '<hr>';die;
            echo '<img src="'.$u['headimgurl'].'">';
        }
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

        public function hashest()
        {
            $uid = 1000;
            $key = 'h:user_info:uid:'.$uid;
            $user_info = [
               'user_name' => 'ajian',
               'email' => 'ajian@qqcom',
                'age' => 20,
                'sex' => 1
            ];

            Redis::hMset($key,$user_info);die;
            echo '<hr>';
            $u = Redis::hGetAll($key);
            echo '<pre>';print_r($u);echo'</pre>';
        }

}
