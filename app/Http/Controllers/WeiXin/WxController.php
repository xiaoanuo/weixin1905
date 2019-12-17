<?php

namespace App\Http\Controllers\WeiXin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
use Illuminate\Support\Facades\Redis;
class WxController extends Controller
{
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
//        dd($event);
        if($event == 'subscribe'){
            $openid = $xml_obj->FromUserName;          //获取用户的openid
            $p = WxUserModel::where(['openid'=>$openid])->first();
            if($p){
                $msg ="欢迎回家";
                $xml = '<xml>
                          <ToUserName><![CDATA['.$openid.']]></ToUserName>
                          <FromUserName><![CDATA['.$xml_obj->fromUser.']]></FromUserName>
                          <CreateTime>'.time().'</CreateTime>
                          <MsgType><![CDATA[text]]></MsgType>
                          <Content><![CDATA['.$msg.']]></Content>
                        </xml>';
                echo $xml;
            }else{
                //获取用户信息
                $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
                $user_info = file_get_contents($url);
                $data = json_decode($user_info,true);
                $nickname = $data['nickname'];

                $user_data = [
                    'openid' => $openid,
                    'sub_time' => $xml_obj->CreateTime,
                    'nickname' => $data['nickname'],
                    'sex' => $data['sex'],
                    'headimgurl' => $data['headimgurl']
                ];
                //openid  入库
                $uid = WxUserModel::insertGetId($user_data);
                $msg ='欢迎'.$nickname.'关注成功';
                $xml = '<xml>
                      <ToUserName><![CDATA['.$openid.']]></ToUserName>
                      <FromUserName><![CDATA['.$xml_obj->ToUserName.']]></FromUserName>
                      <CreateTime>'.time().'</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA['.$msg.']]></Content>
                    </xml>';
                echo $xml;
            }
        }




        //判断消息类型
        $msg_type = $xml_obj->MsgType;
        $toUser = $xml_obj->FromUserName;    //接受消息的用户openid
        $fromUser = $xml_obj->ToUserName;    //开发者ID
        $time = time();                           //time时间戳

        $media_id = $xml_obj->MediaId;       //

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

            //消息入库
        }elseif($msg_type =='image'){  //图片消息
                //下载图片
            $this->getMedia2($media_id,$msg_type);
                //回复图片

        }elseif($msg_type == 'voice'){     //语音消息
                //下载语音
                $this->getMedia2($media_id,$msg_type);
                //回复语音

        }

    }



    public function getMedia()
    {
        $MediaId = "vX6VRjqrTRnwCyPI699jmWZN_0gRb2uNWzDz9OQ6A01ZYlVVOKp7wl-YWgcNn8Iq";
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$MediaId;
        //获取素材内容
        $data = file_get_contents($url);
        //保存文件

        $file_name = date('YmdHis').mt_rand(11111,99999).'.amr';
        file_put_contents($file_name,$data);
        echo "下载素材成功";echo '</br>';
        echo "文件名：" .$file_name;
    }


    protected function getMedia2($media_id,$media_type)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$media_id;
        //获取素材内容
        $data = file_get_contents($url);
        //获取文件扩展名
//        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_info = fifo_file($data);    //返回一个文件信息
//        var_dump($file_info);die;
        $extension = '.' . pathinfo($data)['extension'];
        //保存文件
        $save_path = 'wx_media/';
        if($media_type=='image'){    //保存圖片
            $file_name = date('YmdHis').mt_rand(11111,99999).$extension;
            $save_path = $save_path . 'imgs/' . $file_name;
        }elseif ($media_type=='voice'){
            $file_name = date('YmdHis').mt_rand(11111,99999).'.amr';
            $save_path = $save_path . 'voice/' .$file_name;
        }

        file_put_contents($file_name,$data);
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


}
