<?php

namespace common\libs;

use Yii;
use yii\db\Exception;

/**
 * aes对称加密，可用于加密数据库敏感数据，如手机号
 */

class Aes{
    const CIPHER = 'AES-256-CBC';
    const FILENAME_KEY = 'key.txt';
    const FILENAME_IV = 'iv.txt';

    private $key;
    private $iv;

    public function __construct(string $key, string $iv){
        $this->key = $key;
        $this->iv = $iv;
    }

    /**
     * 获取 key iv
     */
    public static function getKeyIv(){
        $path = Yii::$app->params['aes_file_path'];
        $filenameKey = $path.self::FILENAME_KEY;
        $filenameIv = $path.self::FILENAME_IV;

        if(!is_file($filenameKey) || !is_file($filenameIv)){
            throw new Exception('aes 密钥文件不存在，请创建');
        }

        $f = fopen($filenameKey, 'r');
        $key = fread($f, filesize($filenameKey));
        fclose($f);

        $f = fopen($filenameIv, 'r');
        $iv = fread($f, filesize($filenameIv));

        return ['key'=>$key, 'iv'=>$iv];
    }

    /**
     * 刷新 key iv
     * 请自行保存旧的key iv，要使用新的key iv ,你需要创建一个新的对象
     */
    public function refreshKeyIv() {
        $key = openssl_random_pseudo_bytes(16);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));

        $path = Yii::$app->params['aes_file_path'];
        if(!is_dir($path)){
            mkdir($path, 0755);
        }

        $filenameKey = $path.self::FILENAME_KEY;
        $filenameIv = $path.self::FILENAME_IV;

        $f = fopen($filenameKey, 'w');
        fwrite($f, $key);
        fclose($f);

        $f = fopen($filenameIv, 'w');
        fwrite($f, $iv);
        fclose($f);

        return ['key'=>$key, 'iv'=>$iv];
    }

    /**
     * 加密
     */
    public function encode(string $message){
        return openssl_encrypt($message, self::CIPHER, $this->key, $options=0, $this->iv);
    }

    /**
     * 解密
     */
    public function decode(string $token){
        return openssl_decrypt($token, self::CIPHER, $this->key, $options=0, $this->iv);
    }
}