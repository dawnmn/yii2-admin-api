<?php


namespace common\libs;

use PHPGangsta_GoogleAuthenticator;

class GoogleAuth
{
    /**
     * 生成密钥
     */
    public static function createSecret(string $name){
        $auth = new PHPGangsta_GoogleAuthenticator();
        $secret = $auth->createSecret();
        $qrcodeUrl = $auth->getCode($secret, $name);
        return [
            'secret'=>$secret,
            'qrcode_url'=>$qrcodeUrl
        ];
    }

    /**
     * 验证
     */
    public static function verify(string $secret, string $oneCode){
        $auth = new PHPGangsta_GoogleAuthenticator();
        return $auth->verifyCode($secret, $oneCode);
    }
}