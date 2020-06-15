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
            'redis' => 'redis', // 连接组件或它的配置
            'channel' => 'queue', // Queue channel key
        ],
    ],
];
