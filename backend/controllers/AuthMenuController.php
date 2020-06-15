<?php

namespace backend\controllers;

use backend\components\Controller;
use common\libs\Helper;
use backend\models\AuthMenu;
use backend\service\AuthService;

class AuthMenuController extends Controller
{
    /**
     * 子菜单集合
     */
    public function actionSimpleListChild(){
        return [
            'menu_list'=>AuthMenu::simpleListChild()
        ];
    }

    /**
     * 父菜单集合
     */
    public function actionSimpleListParent(){
        return [
            'menu_list'=>AuthMenu::simpleListParent()
        ];
    }

    /**
     * 列表
     */
    public function actionList(){
        $model = new AuthMenu();
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
     * 详情
     */
    public function actionItem(){
        $model = new AuthMenu();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'item'))){
            return Helper::response(400, $error);
        }
        $item = $model->item();
        return $item;
    }

    /**
     * 新增
     */
    public function actionAdd(){
        $model = new AuthMenu();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'add'))){
            return Helper::response(400, $error);
        }
        $model->add();
    }

    /**
     * 修改
     */
    public function actionUpd(){
        $model = new AuthMenu();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'upd'))){
            return Helper::response(400, $error);
        }
        $model->upd();
    }

    /**
     * 删除
     */
    public function actionDel(){
        $model = new AuthMenu();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'del'))){
            return Helper::response(400, $error);
        }
        $model->del();
    }

    /**
     * 当前登录用户菜单
     */
    public function actionCurrent(){
        return [
            'menu'=>AuthService::getMenuList()
        ];
    }
}