<?php

namespace backend\controllers;

use backend\components\Controller;
use common\libs\Helper;
use backend\models\AuthItem;
use backend\service\AuthService;
use yii\rbac\Item;

class AuthRoleController extends Controller
{
    /**
     * 列表
     */
    public function actionList(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'items'))){
            return Helper::response(400, $error);
        }
        // 分页数据
        $query = $model->items(Item::TYPE_ROLE);
        $paginationData = Helper::pagination($model,$query);
        return $paginationData;
    }

    /**
     * 新增
     */
    public function actionAdd(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'add'))){
            return Helper::response(400, $error);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            (new AuthService())->addRole($model);
            (new AuthService())->updRoleItem($model->name, $model->items);
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * 修改
     */
    public function actionUpd(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'upd'))){
            return Helper::response(400, $error);
        }
        if($model->name == AuthService::SUPER_ADMIN){
            return Helper::response(400, '不能修改'.AuthService::SUPER_ADMIN);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            (new AuthService())->updRole($model);
            (new AuthService())->updRoleItem($model->name_new, $model->items);
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * 删除
     */
    public function actionDel(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'del'))){
            return Helper::response(400, $error);
        }
        if($model->name == AuthService::SUPER_ADMIN){
            return Helper::response(400, '不能删除'.AuthService::SUPER_ADMIN);
        }
        (new AuthService())->delRole($model);
    }

    /**
     * 角色权限树
     */
    public function actionAuthTree(){
        $roleName = \Yii::$app->request->post('role_name');

        $data = [
            'auth_tree'=>AuthService::getAuthTree($roleName),
        ];
        return $data;
    }

    /**
     * 角色名称集合
     */
    public function actionSimpleList(){
        return [
            'role_list'=>AuthItem::simpleListRole()
        ];
    }
}