<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
        $code = $_GET['code'];
        print_r($code);
        $this->getAccessToken($code);
    }

    public function  getAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);
        echo '<pre>';print_r($data);echo '</pre>';
    }

}
