<?php


namespace common\libs;

use Yii;

/**
 * redis 工具类
 * Class RedisLock
 * @package common\libs
 */
class RedisHelper
{
    private $redis;

    public function __construct()
    {
        $this->redis = Yii::$app->redis;
    }

    /**
     * 获取锁
     * @param string $key
     * @param int $expire
     * @return bool|mixed
     */
    public function lock(string $key, int $expire){
        return $this->redis->set($key, 1, 'NX', 'EX', $expire) ? true : false;
    }

    /**
     * 接口请求限制 不严格
     * @return bool
     */
    public function requestLimit(){
        $api = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;

        if(!($requestLimit = (Yii::$app->params['request_limit_list'][$api] ?? []))){
            return true;
        }

        $ip = Helper::getClientIp(0, 1);
        $redisKey = $api.$ip;
        $number = $this->redis->get($redisKey) ?: 0;
        if($number > $requestLimit['limit']){
            return false;
        }

        $this->redis->incr($redisKey);
        $this->redis->expire($redisKey, $requestLimit['expire']);
        return true;
    }
}