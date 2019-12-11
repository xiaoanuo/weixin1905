<?php

namespace App\Http\Controllers\WeiXin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
class WxController extends Controller
{
    protected $access_token;

    public function __construct()
    {
        //获取access_token
        $this->access_token = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET');
        $data_json = file_get_contents($url);
        $arr = json_decode($data_json,true);
        return $arr['access_token'];
    }

    /**
     * 处理接入
     */
    public function wechat()
    {
        $token = "2259b56f5898cd6192c";
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echostr = $_GET["echostr"];

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){      //验证通过
            echo $echostr;
        }else{
            die("not ok");
        }
    }

    /**
     * 接受微信的推送事件
     */
    public function receiv()
    {
        $log_file = "wx.log";     //public
        $xml_str = file_get_contents("php://input");
        //将接受的数据记录到日志文件
        $data = date('Y-m-d H:i:s') . $xml_str;
        file_put_contents($log_file,$data,FILE_APPEND);     //追加写入
        //处理xml数据
        $xml_obj = simplexml_load_string($xml_str);

        $event = $xml_obj->Event;   //获取事件类型
        if($event == 'subscribe'){
            $openid = $xml_obj->FromUserName;          //获取用户的openid
            //获取用户信息
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
            $user_info = file_get_contents($url);
            file_put_contents('wx_user.log',$user_info,FILE_APPEND);
            $data = json_decode($user_info);

            $nickname = $data->nickname;

//            var_dump($nickname);die;
            $p = WxUserModel::where(['openid'=>$openid])->first();
            if($p){
              echo "欢迎.$nickname.回来";die;
            }
            $user_data = [
                'openid' => $openid,
                'sub_time' => $xml_obj->CreateTime,
                'nickname' => $data->nickname,
                'sex' => $data->sex
            ];
            //openid  入库
            $uid = WxUserModel::insertGetId($user_data);
            echo "$nickname.关注成功";

        }

        //判断消息类型
        $msg_type = $xml_obj->MsgType;
        $toUser = $xml_obj->FromUserName;    //接受消息的用户openid
        $fromUser = $xml_obj->ToUserName;    //开发者ID
        $time = time();                           //time时间戳

        if($msg_type == 'text'){
            $content = date('Y-m-d H:i:s') . $xml_obj->Content;
            $respon_text = '<xml>
              <ToUserName><![CDATA['.$toUser.']]></ToUserName>
              <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
              <CreateTime>'.$time.'</CreateTime>
              <MsgType><![CDATA[text]]></MsgType>
              <Content><![CDATA['.$content.']]></Content> 
            </xml>';
                echo $respon_text;   //回复用户消息
        }

    }

    /**
     * 获取用户基本信息
     */
    public function getUserInfo($access_token,$openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        //发送网络请求
        $json_str = file_get_contents($url);
        $log_file = 'wx_user.log';
        file_put_contents($log_file,$json_str,FILE_APPEND);
    }


    public function  ddd()
    {
        echo "小阿剑de大保健纳？？？";
    }

}
