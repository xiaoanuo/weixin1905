<?php

namespace App\Admin\Controllers;

use App\Model\WxGoodsModel;
use App\Model\WxUserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use GuzzleHttp\Client;

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

        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=28_p9B0hKZ1iC40Lqg7s81J6OS9M8pX8ADp7SFZu31aYCN4AcIiwSP2EH4rsKIHxwMwoSaTH19npi81UCHcDkBlbzpQgvuo7nQhhFVo37TBuQP7voRahgw4jOm8HhiVVKrPZ6pyyv0Ges82lQSeHOJgAHAYFA";
        $msg = date('Y-m-d H:i:s') . '斗神叶秋';

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
}
