<?php


namespace common\libs;

use Curl\Curl;
use yii\web\HeaderCollection;

/**
 * 对外调用接口类
 * 支持 GET POST 上传文件
 */
class Api
{
    // key - secret 列表
    const KEY_SECRET_LIST = [
        [
            'key'=>'jLFpqzfuE9',
            'secret'=>'CnVKt9o3rya',
        ],
    ];

    private $key;
    private $secret;

    public function __construct(string $key, string $secret){
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * 生成签名
     */
    private function sign(array $data, int $timestamp){
        $token = "application:{$this->key}\ntimestamp:$timestamp\n";

        self::arrayToString($data, $token);
        $token = hash_hmac('sha1',$token,$this->secret,true);
        $token = base64_encode($token);
        return $token;
    }

    /**
     * 生成键值字符串
     */
    private function arrayToString(&$data, &$token){
        if($data){
            ksort($data);
            foreach ($data as $k=>$v){
                if(is_array($v)){
                    $token.="$k:[]\n";
                    self::arrayToString($v, $token);
                }else{
                    $token.="$k:$v\n";
                }
            }
        }
    }

    /**
     * 生成Curl对象
     */
    private function curl(array $data, bool $json = true){
        $timestamp = Helper::currentTimeMillis();
        $date = date('D, d M Y H:i:s').' GMT';
        $signature = $this->sign($data, $timestamp);

        $curl = new Curl();
        $curl->setDefaultDecoder($assoc = true);

        $header = [
            'application: '.$this->key,
            'timestamp: '.$timestamp,
            'signature: '.$signature,
            'Date: '.$date,
        ];
        $json && $header[] = 'Content-Type: application/json; charset=UTF-8';
        $curl->setOpt(CURLOPT_HTTPHEADER, $header);

        return $curl;
    }

    /**
     * get请求
     */
    public function get(string $url, array $data){
        return $this->curl($data)->get($url, $data);
    }

    /**
     * post请求
     */
    public function post(string $url, array $data){
        return $this->curl($data)->post($url, json_encode($data, JSON_FORCE_OBJECT));
    }

    /**
     * 上传文件
     */
    public function upload(string $url, array $data){
        return $this->curl([], false)->post($url, $data);
    }

    /**
     * 请求验证
     */
    public function verify(array $data, HeaderCollection $headers){
        $this->key = $headers->get('application');
        $timestamp = $headers->get('timestamp');
        $signature = $headers->get('signature');

        $timestampLocal = Helper::currentTimeMillis();
        if(abs($timestampLocal - $timestamp) > 300000){
            return '请求超时';
        }
        foreach (self::KEY_SECRET_LIST as $account){
            if($account['key'] == $this->key){
                $this->secret = $account['secret'];
                break;
            }
        }
        if(!$this->secret){
            return 'application 错误';
        }
        if($signature != $this->sign($data, $timestamp)){
            return 'signature 验证不通过';
        }
        return true;
    }
}