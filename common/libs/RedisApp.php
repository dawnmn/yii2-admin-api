<?php


namespace common\libs;

use Yii;

/**
 * redis 工具类
 * Class RedisLock
 * @package common\libs
 */
class RedisApp
{
    /**
     * 获取锁
     * @param string $key
     * @param int $expire
     * @return bool
     */
    public static function lock(string $key, int $expire){
        $redis = Yii::$app->redis;
        return $redis->set($key, 1, 'NX', 'EX', $expire) ? true : false;
    }

    public static function unlock(string $key){
        $redis = Yii::$app->redis;
        $redis->del($key);
    }

    /**
     * 接口请求限制 根据IP限制
     * @return bool
     */
    public static function requestLimit(){
        $api = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
        if(!($requestLimit = (Yii::$app->params['request_limit_list'][$api] ?? []))){
            return true;
        }

        $ip = Helper::getClientIp(0, 1);
        $key = $api.$ip;
        $time = time();
        $expireTime = $time - $requestLimit['expire'];

        $redis = Yii::$app->redis;
        $list = $redis->lrange($key, 0, -1);
        $len = count($list);
        $redis->watch($key);
        $redis->multi();

        if($len >= $requestLimit['limit']){
            foreach ($list as $index=>$callTime){
                if($callTime < $expireTime){
                    $redis->lpop($key);
                    unset($list[$key]);
                }else{
                    break;
                }
            }
        }

        if(count($list) >= $requestLimit['limit']){
            $redis->expire($key, $requestLimit['expire']);
            $redis->exec();
            return false;
        }

        $redis->rpush($key, $time);
        $redis->expire($key, $requestLimit['expire']);

        return $redis->exec() ? true : false;
    }
}