<?php


namespace common\libs;

use backend\models\Admin;
use backend\service\AuthService;
use Yii;

/**
 * redis 工具类
 */
class RedisApp
{
    const KEY_PREFIX_API_LIMIT = 'API_LIMIT_';

    /**
     * 上锁
     */
    public static function lock(string $key, int $expire){
        $redis = Yii::$app->redis;
        return $redis->set($key, 1, 'NX', 'EX', $expire) ? true : false;
    }

    /**
     * 解锁
     */
    public static function unlock(string $key){
        $redis = Yii::$app->redis;
        $redis->del($key);
    }

    /**
     * 接口请求根据 IP/用户ID 限制
     */
    public static function apiLimit(){
        $api = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
        if(!($apiLimit = (Yii::$app->params['api_limit_list'][$api] ?? []))){
            return true;
        }

        $suffix = AuthService::isWhiteApi() ? Helper::getClientIp(0, 1) : Admin::getCurrentId();
        $key = self::KEY_PREFIX_API_LIMIT.$api.$suffix;

        return self::limit($key, $apiLimit['limit'], $apiLimit['expire']);
    }

    /**
     * 计数器限制
     */
    public static function limit(string $key,int $limit,int $expire, callable $lock=null){
        if($lock && !$lock()){
            return false;
        }

        $time = time();
        $expireTime = $time - $expire;

        $redis = Yii::$app->redis;
        $list = $redis->lrange($key, 0, -1);
        $len = count($list);
        $redis->watch($key);
        $redis->multi();

        if($len >= $limit){
            foreach ($list as $index=>$callTime){
                if($callTime < $expireTime){
                    $redis->lpop($key);
                    unset($list[$index]);
                }else{
                    break;
                }
            }
        }

        if(count($list) >= $limit){
            $redis->expire($key, $expire);
            $redis->exec();
            $lock();
            return false;
        }

        $redis->rpush($key, $time);
        $redis->expire($key, $expire);

        return $redis->exec() ? true : false;
    }
}