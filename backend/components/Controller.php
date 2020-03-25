<?php

namespace backend\components;

use backend\models\Admin;
use backend\service\AuthService;
use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;

class Controller extends \yii\web\Controller
{
    public $admin;

    public function behaviors()
    {
        // API白名单
        if(AuthService::isWhiteApi()){
            return [];
        }

        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if(parent::beforeAction($action)){
            // API白名单
            if(AuthService::isWhiteApi()){
                return true;
            }

            // 超级管理员绿灯
            $adminId = Admin::getCurrentId();
            $role = AuthService::getRole();
            if(AuthService::SUPER_ADMIN == $role){
                return true;
            }
            // 普通角色权限验证
            $api = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
            if(!Yii::$app->authManager->checkAccess($adminId, $api)){
                throw new HttpException(405, '权限不足');
            }
            return true;
        }
        return false;
    }
}