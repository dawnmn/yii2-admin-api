<?php


namespace common\libs;

use Curl\Curl;
use yii\web\HeaderCollection;

class Api
{
    // key - secret 列表
    const KEY_SECRET_LIST = [
        [
            'key'=>'jLFpqzfuE9',
            'secret'=>'CnVKt9o3rya',
        ],
    ];

    /**
     * 生成签名
     * @param array $data
     * @param int $timestamp
     * @param string $key
     * @param string $secret
     * @return string
     */
    private function sign(array $data, int $timestamp, string $key, string $secret){
        $token = "application:$key\ntimestamp:$timestamp\n";

        if($data){
            ksort($data);
            foreach ($data as $k=>$v){
                $token.="$k:$v\n";
            }
        }
        $token = hash_hmac('sha1',$token,$secret,true);
        $token = base64_encode($token);
        return $token;
    }

    /**
     * 上行请求 组建header，生成Curl对象
     * @param string $url
     * @param array $data
     * @return Curl
     * @throws \ErrorException
     */
    private function curl(string $url, array $data){
        $timestamp = Helper::currentTimeMillis();
        $date = date('D, d M Y H:i:s').' GMT';
        $key = self::KEY_SECRET_LIST[0]['key'];
        $secret = self::KEY_SECRET_LIST[0]['secret'];
        $signature = $this->sign($data, $timestamp, $key, $secret);

        $curl = new Curl();
        $curl->setDefaultDecoder($assoc = true);
        $curl->setOpt(CURLOPT_HTTPHEADER, [
            'application: '.$key,
            'timestamp: '.$timestamp,
            'signature: '.$signature,
            'Content-Type: application/json; charset=UTF-8',
            'Date: '.$date,
        ]);
        return $curl;
    }

    /**
     * 上行请求 get方式
     * @param string $url
     * @param array $data
     * @return mixed
     * @throws \ErrorException
     */
    public function get(string $url, array $data){
        return $this->curl($url, $data)->get($url, $data);
    }

    /**
     * 上行请求 post方式
     * @param string $url
     * @param array $data
     * @return mixed
     * @throws \ErrorException
     */
    public function post(string $url, array $data){
        return $this->curl($url, $data)->post($url, $data);
    }

    /**
     * 上行响应 请求验证
     * @param array $data
     * @param HeaderCollection $headers
     * @return bool|string
     */
    public function verify(array $data, HeaderCollection $headers){
        $key = $headers->get('application');
        $timestamp = $headers->get('timestamp');
        $signature = $headers->get('signature');
        $secret = '';

        $timestampLocal = Helper::currentTimeMillis();
        if(abs($timestampLocal - $timestamp) > 300000){
            return '请求超时';
        }
        foreach (self::KEY_SECRET_LIST as $keySecret){
            if($keySecret['key'] == $key){
                $secret = $keySecret['secret'];
                break;
            }
        }
        if(!$secret){
            return 'application 错误';
        }
        if($signature != $this->sign($data, $timestamp, $key, $secret)){
            return 'signature 验证不通过';
        }
        return true;
    }
}