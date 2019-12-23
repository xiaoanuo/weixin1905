<?php

namespace App\Admin\Controllers;

use App\Model\WxGoodsModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
class WxImgController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微信';

    public function sendImg()
    {
        $openid_arr = WxGoodsModel::select('img')->get();
        echo '<pre>';print_r($openid_arr);echo '</pre>';
        $MediaId = "vX6VRjqrTRnwCyPI699jmWZN_0gRb2uNWzDz9OQ6A01ZYlVVOKp7wl-YWgcNn8Iq";
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->access_token.'';
        $msg = date('Y-m-d H:i:s') . '李剑收到回答';

        $data = [
            'touser'    =>  $openid_arr,
            'msgtype'   => 'mpnews',
            'media_id'       => $MediaId,
            "send_ignore_reprint" => 0
        ];
        $client = new Client();
        $response = $client->request('POST',$url,[
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

        echo $response->getBody();

    }



    protected $access_token;

    public function __construct()
    {
        //获取access_token
        $this->access_token = $this->getAccessToken();
    }

    public function test()
    {
        echo $this->access_token;
    }

    protected function getAccessToken()
    {
        $key = 'wx_access_token';
        $access_token = Redis::get($key);
        if($access_token){
            return $access_token;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET');
        $data_json = file_get_contents($url);
        $arr = json_decode($data_json,true);

        Redis::set($key,$arr['access_token']);
        Redis::expire($key,3600);
        return $arr['access_token'];
    }

    /**
     * 刷新access_token
     */
    public function flushAccessToken()
    {
        $key="wexin_access_token";
        Redis::del($key);
        echo $this->getAccessToken();
    }

}
