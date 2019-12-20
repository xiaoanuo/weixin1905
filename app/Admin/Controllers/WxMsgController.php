<?php

namespace App\Admin\Controllers;

use App\Model\WxGoodsModel;
use App\Model\WxUserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
class WxMsgController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微信';

    public function sendMsg()
    {
        $openid_arr = WxUserModel::select('openid')->get()->toArray();

        $openid = array_column($openid_arr,'openid');
//        echo '<pre>';print_r($openid);echo '</pre>';

        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->access_token.'';
        $msg = date('Y-m-d H:i:s') . '收到請回復';

        $data = [
            'touser'    =>  $openid,
            'msgtype'   => 'text',
            'text'       => ['content'=>$msg]
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
