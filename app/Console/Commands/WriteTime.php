<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\WxUserModel;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
class WriteTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'write:time-stamp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $access_token;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->access_token = $this->getAccessToken();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 在命令行打印信息
        $this->sendMsg('开始执行...');
        file_put_contents('./write_time.txt', time() . "\n", FILE_APPEND);
        $this->sendMsg('执行结束...');
    }


    public function sendMsg()
    {
        //请求第三方接口
//        $weather_api = "https://free-api.heweather.net/s6/weather/now?location=beijing&key=9d7786053ece4c4aaf31afcab838007f";
//        $weather_info = file_get_contents($weather_api);
//        $weather_info_arr = json_decode($weather_info,true);
//              echo '<pre>';print_r($weather_info_arr);echo '</pre>';die;
//        $cond_txt = $weather_info_arr['HeWeather6'][0]['now']['cond_txt'];
//        $tmp = $weather_info_arr['HeWeather6'][0]['now']['tmp'];
//        $wind_dir = $weather_info_arr['HeWeather6'][0]['now']['wind_dir'];
//
//        $msg = '天况：'.$cond_txt.'--'  . '温度：'.$tmp .'--'.'风向：' .$wind_dir;
//        echo $msg;echo "\n";

        $openid_arr = WxUserModel::select('openid')->get()->toArray();

        $openid = array_column($openid_arr,'openid');
//        echo '<pre>';print_r($openid);echo '</pre>';

        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->access_token.'';
        $msg = date('Y-m-d H:i:s') . '是否接受';

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





    public function test()
    {
        echo $this->access_token;
    }

}
