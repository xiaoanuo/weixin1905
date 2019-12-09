<?php

namespace App\Http\Controllers\WeiXin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WxController extends Controller
{
    /**
     * 获取access_token
     */
    public static function access_tonken()
    {
        //获取access_token
        $access_token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
    }

    /**
     *获取用户基本信息
     */
    public function getUserInfo()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=28_u-3g0M-t6OdngJH5pi23DACzzTg-wwxVibCZJ5KT4DDR_2y7ZVEz4y_QqmyCQLzQxaWp2BfFEUxNHa5VVHChTgufaTuEOkVea_cpkalAEqK5HmaJpRTRI4C2zSIupu_JIUwNIOKKfr-LXXwqDUDaAJANEN&openid=oGVH1wM5F47tnb-oCC4d4qt96PjM&lang=zh_CN";
    }

    /**
     * 接受微信推送事件
     */
    public function receiv()
    {
        $log_file = "wx.log";  //public
        //将接受的数据记录到日志文件
        $xml = file_get_contents("php://input");
        $data = date('Y-m-d','H:i:s') . $xml;
        file_put_contents($log_file,$data,FILE_APPEND);    //追加写入
    }

    public function xmlText()
    {
        $xml ="<xml>
                  <ToUserName><![CDATA[toUser]]></ToUserName>
                  <FromUserName><![CDATA[fromUser]]></FromUserName>
                  <CreateTime>1348831860</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[this is a test]]></Content>
                  <MsgId>1234567890123456</MsgId>
                </xml>";
    }

}
