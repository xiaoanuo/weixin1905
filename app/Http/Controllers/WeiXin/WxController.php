<?php

namespace App\Http\Controllers\WeiXin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
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
     * 刷新token
     */
    public function flushAccessToken()
    {
        $key="wexin_access_token";
        Redis::del($key);
        echo $this->getAccessToken();
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
        $openid = $xml_obj->FromUserName;          //获取用户的openid
        if($event == 'subscribe'){

            $p = WxUserModel::where(['openid'=>$openid])->first();
            if($p){
                $msg ='欢迎'.$p['nickname'].'回家';
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
        }elseif($event=='CLICK'){           //菜单点击事件
            //如果是获取天气
            if($xml_obj->EventKey=='weather'){
                //请求第三方接口
                $weather_api = "https://free-api.heweather.net/s6/weather/now?location=beijing&key=9d7786053ece4c4aaf31afcab838007f";
                $weather_info = file_get_contents($weather_api);
                $weather_info_arr = json_decode($weather_info,true);
//                echo '<pre>';print_r($weather_info_arr);echo '</pre>';die;
                $cond_txt = $weather_info_arr['HeWeather6'][0]['now']['cond_txt'];
                $tmp = $weather_info_arr['HeWeather6'][0]['now']['tmp'];
                $wind_dir = $weather_info_arr['HeWeather6'][0]['now']['wind_dir'];

                $msg = '天况：'.$cond_txt.'--'  . '温度：'.$tmp .'--'.'风向：' .$wind_dir;
                $response_xml = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
<FromUserName><![CDATA['.$xml_obj->fromUser.']]></FromUserName>
<CreateTime>'.time().'</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA['.date('Y-m-d H:i:s'). $msg .']]></Content>
</xml>';
                echo $response_xml;
            }elseif($xml_obj->EventKey=='curriculum'){

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
            // TODO 下载图片
            $this->getMedia2($media_id,$msg_type);
            // TODO 回复图片
            $response = '<xml>
                          <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                          <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                          <CreateTime>'.time().'</CreateTime>
                          <MsgType><![CDATA[image]]></MsgType>
                          <Image>
                            <MediaId><![CDATA['.$media_id.']]></MediaId>
                          </Image>
                        </xml>';
            echo $response;

        }elseif($msg_type == 'voice'){     //语音消息
            // 下载语音
            $this->getMedia2($media_id,$msg_type);
            // TODO 回复语音
            $response = '<xml>
                          <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                          <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                          <CreateTime>'.time().'</CreateTime>
                          <MsgType><![CDATA[voice]]></MsgType>
                          <Voice>
                            <MediaId><![CDATA['.$media_id.']]></MediaId>
                          </Voice>
                        </xml>';
            echo $response;
        }elseif ($msg_type=='video'){
            // 下载小视频
            $this->getMedia2($media_id,$msg_type);
            // 回复
            $response = '<xml>
                          <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                          <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                          <CreateTime>'.time().'</CreateTime>
                          <MsgType><![CDATA[video]]></MsgType>
                          <Video>
                            <MediaId><![CDATA['.$media_id.']]></MediaId>
                            <Title><![CDATA[测试]]></Title>
                            <Description><![CDATA[不可描述]]></Description>
                          </Video>
                        </xml>';
            echo $response;
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
        $client = new Client();
        $response = $client->request('GET',$url);
        //获取文件扩展名
        $f = $response->getHeader('Content-disposition')[0];
        $extension = substr(trim($f,'"'),strpos($f,'.'));
        //获取文件内容
        $file_content = $response->getBody();
        // 保存文件
        $save_path = 'wx_media/';
        if($media_type=='image'){       //保存图片文件
            $file_name = date('YmdHis').mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'imgs/' . $file_name;
        }elseif($media_type=='voice'){  //保存语音文件
            $file_name = date('YmdHis').mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'voice/' . $file_name;
        }elseif($media_type=='video')
        {
            $file_name = date('YmdHis').mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'video/' . $file_name;
        }
        file_put_contents($save_path,$file_content);
    }

    /**
     * 创建自定义菜单
     */
    public function createMenu()
    {
        $url = 'http://1905wx.xiaoanuo.com/vote';
        $url2 = 'http://1905wx.xiaoanuo.com/';
        $url4 = 'http://1905wx.xiaoanuo.com/Administration';
        $redirect_url = urlencode($url);       //授权后跳转专业面
        $redirect_urls = urlencode($url2);       //授权后跳商城页面
        $redirect_url4 = urlencode($url4);

        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->access_token;
        $menu = [
            'button' =>[
                [
                    'type' => 'click',
                    'name' => '查看课程',
                    'key' => 'curriculum'
                ],
                [
                    'type' => 'view',
                    'name' => '课程管理',
                    'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx7b138a4006e174c7&redirect_uri='.$redirect_url4.'&response_type=code&scope=snsapi_userinfo&state=wx1905#wechat_redirect'
                ],
            ]
        ];
        $menu_json = json_encode($menu,JSON_UNESCAPED_UNICODE);
        $client = new Client();
        $response = $client->request('POST',$url,[
            'body' => $menu_json
        ]);

        echo '<pre>';print_r($menu);echo '</pre>';
        echo $response->getBody();
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
