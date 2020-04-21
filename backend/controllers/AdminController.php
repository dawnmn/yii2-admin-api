<?php

namespace backend\controllers;

use backend\components\Controller;
use backend\models\Admin;
use backend\service\AuthService;
use common\libs\Helper;
use Yii;
use yii\web\HttpException;

class AdminController extends Controller
{
    /**
     * API 新增
     */
    public function actionAdd(){
        $model = new Admin();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'add'))){
            return Helper::response(400, $error);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try{
            $role = new \stdClass();
            $role->name = $model->role_name;
            $data = $model->add();
            Yii::$app->authManager->assign($role, $model->id);
            $transaction->commit();
            return $data;
        }catch (\Throwable $exception){
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * API 修改
     */
    public function actionUpd(){
        $model = new Admin();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'upd'))){
            return Helper::response(400, $error);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try{
            $role = new \stdClass();
            $role->name = $model->role_name;
            $model->upd();
            Yii::$app->authManager->revokeAll($model->id);
            Yii::$app->authManager->assign($role, $model->id);
            $transaction->commit();
        }catch (\Throwable $exception){
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * API 列表
     */
    public function actionList(){
        $model = new Admin();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'items'))){
            return Helper::response(400, $error);
        }

        // 分页数据
        $query = $model->items();
        $paginationData = Helper::pagination($model,$query);
        return $paginationData;
    }

    /**
     * API 修改密码
     */
    public function actionUpdPassword(){
        $model = new Admin();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'upd_password'))){
            return Helper::response(400, $error);
        }
        $model->updPassword();
    }

    /**
     * API 登录
     */
    public function actionLogin(){
        // 处理已登录
        if (!\Yii::$app->user->isGuest) {
            return [];
        }
        $model = new Admin();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'login'))){
            $error = Helper::simplifyRequestError($error);
            return Helper::response(400, $error);
        }
        Yii::$app->user->login(Admin::findOne(['username'=>$model->username]), Admin::LOGIN_EXPIRE_TIME);
    }

    /**
     * API 登出
     */
    public function actionLogout(){
        Yii::$app->user->logout();
    }

    /**
     * API 重置密码
     */
    public function actionResetPassword(){
        if(AuthService::SUPER_ADMIN != AuthService::getRole()){
            throw new HttpException(405, '权限不足');
        }

        $model = new Admin();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'reset_password'))){
            return Helper::response(400, $error);
        }
        return $model->resetPassword();
    }
}