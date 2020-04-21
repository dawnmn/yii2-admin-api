<?php

namespace backend\controllers;

use backend\components\Controller;
use common\libs\Helper;
use backend\models\AuthItem;
use backend\models\AuthMenu;
use backend\service\AuthService;
use yii\rbac\Item;

class AuthItemController extends Controller
{
    /**
     * API 列表
     */
    public function actionList(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'items'))){
            return Helper::response(400, $error);
        }
        // 分页数据
        $query = $model->items(Item::TYPE_PERMISSION);
        $paginationData = Helper::pagination($model,$query);
        return $paginationData;
    }

    /**
     * API 新增
     */
    public function actionAdd(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'add'))){
            return Helper::response(400, $error);
        }

        (new AuthService())->addItem($model);
    }

    /**
     * API 修改
     */
    public function actionUpd(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'upd'))){
            return Helper::response(400, $error);
        }
        (new AuthService())->updItem($model);
    }

    /**
     * API 删除
     */
    public function actionDel(){
        $model = new AuthItem();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'del'))){
            return Helper::response(400, $error);
        }
        (new AuthService())->delItem($model);
    }
}