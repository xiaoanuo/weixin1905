<?php

namespace App\Http\Controllers\WeiXin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
use GuzzleHttp\Client;
class WxQRController extends Controller
{
    /**
     * 获取ticked
     */
    public function ticked()
    {
        $scene_id = $_GET['scene'];    //二维码参数
        $access_token = WxUserModel::getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;

        $data = [
            "expire_seconds"    => 604800,
            "action_name"       => 'QR_SCENE',
            "action_info"       => [
                'scene'    =>[
                    'scene_id'   => $scene_id
                ]
            ]
        ];
        $client = new Client();
        $response = $client->request('POST',$url,[
            'body' =>json_encode($data)
        ]);
        $json =  $response->getBody();
        $ticked = json_decode($json,true)['ticket'];
        //获取带参数的二维码

        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticked;

        return redirect($url);
    }
}
