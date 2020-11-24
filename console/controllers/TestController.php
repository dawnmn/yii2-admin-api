<?php


namespace console\controllers;


use yii\console\Controller;

class TestController extends Controller
{
    /**
     * 初始化系统业务数据
     */
    public function actionRun1(){
        $redis = \Yii::$app->redis;
        echo $redis->get("aa")."\n";
        echo $redis->get("bb")."\n";
        $redis->watch("aa");
        $redis->multi();
        $redis->set("aa", 123);
        sleep(10);
        $redis->set("bb", 123);
        var_dump($redis->exec());
        echo $redis->get("aa")."\n";
        echo $redis->get("bb")."\n";
    }

    public function actionRun2(){
        $redis = \Yii::$app->redis;
        echo $redis->get("aa")."\n";
        $redis->set("aa", 300);
        $redis->set("bb", "bb");
        echo $redis->get("aa")."\n";

        echo $redis->get("bb")."\n";
    }
}