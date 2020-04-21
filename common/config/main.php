<?php
return [
    'timeZone' => 'Asia/Chongqing',
    'language'=>'zh-CN',
    'bootstrap' => [
        'queue', // 把这个组件注册到控制台
    ],
    'components' => [
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // 驱动的其他选项
        ],
    ],

    /*
     * main-local 格式
     *
    'components' => [
        'db'=>[
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=test',
            'username' => 'root',
            'password' => 'password',
            'tablePrefix' => '',
            'charset' => 'utf8',
            'enableSchemaCache' => YII_ENV == 'dev' ? false : true, // 表结构缓存
            'schemaCacheDuration' => 86400,
        ],
        'log' => [
            'traceLevel' => 1,
            'targets' => [
                [
                    'class' => 'common\libs\LogFileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
    ],
    */
];
