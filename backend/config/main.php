<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Chongqing',
    'language'=>'zh-CN',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => 'rrm_backend_cookie',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => 'backend\components\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $data = [
                    'code' => $response->getStatusCode(),
                    'message' => $response->getMessage(),
                    'data'=>[]
                ];
                $response->data && $data['data'] = $response->data;

                $response->setStatusCode(200);
                $response->data = $data;

                // 写入日志
                if($data['code'] == 200){
                    (new \backend\models\AdminLog())->add();
                }
            },
        ],
        'user' => [
            'identityClass' => 'backend\models\Admin',
            'loginUrl'=>null
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'yii2-admin-backend',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'itemTable'=>'{{%auth_item}}',
            'itemChildTable'=>'{{%auth_item_child}}',
            'assignmentTable'=>'{{%auth_assignment}}',
            'ruleTable'=>'{{%auth_rule}}',
            // uncomment if you want to cache RBAC items hierarchy
            // 'cache' => 'cache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'formatter'=>[
            'defaultTimeZone'=>'Asia/Shanghai',
            'dateFormat'=>'php:Y-m-d',
            'timeFormat'=>'php:H:i:s',
            'datetimeFormat'=>'php:Y-m-d H:i:s'
        ],
    ],
    'params' => $params,
];
