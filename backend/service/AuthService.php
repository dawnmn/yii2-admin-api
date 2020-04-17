<?php

namespace backend\service;

use backend\models\Admin;
use backend\models\AuthItem;
use backend\models\AuthMenu;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;

class AuthService
{
    const SUPER_ADMIN = '超级管理员'; // 超级管理员

    public function addItem($model){
        $authManager = Yii::$app->authManager;
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 使用yii2的方式添加权限条目
            $authItem = $authManager->createPermission($model->name);
            $authItem->description = $model->description;
            $authManager->add($authItem);

            // 补充菜单字段值
            $authItem = AuthItem::findOne(['name'=>$model->name]);
            $authItem->menu_id = $model->menu_id;
            $authItem->save(false);

            $trans->commit();
        } catch (\Exception $exception) {
            $trans->rollBack();
            throw $exception;
        }
    }

    public function updItem($model){
        $authManager = Yii::$app->authManager;
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 使用yii2的方式修改权限条目
            $authItem = $authManager->getPermission($model->name);
            $authItem->name = $model->name_new;
            $authItem->description = $model->description;
            $authManager->update($model->name, $authItem);

            // 补充菜单字段值
            $authItem = AuthItem::findOne(['name'=>$model->name_new]);
            $authItem->menu_id = $model->menu_id;
            $authItem->save(false);

            $trans->commit();
        } catch (\Exception $exception) {
            $trans->rollBack();
            throw $exception;
        }
    }

    public function delItem($model){
        // 使用yii2的方式删除权限条目
        $authManager = Yii::$app->authManager;
        $authItem = $authManager->getPermission($model->name);
        $authManager->remove($authItem);
    }

    public function addRole($model){
        $authManager = Yii::$app->authManager;
        $role = $authManager->createRole($model->name);
        $role->description = $model->description;
        $authManager->add($role);
    }

    public function updRole($model){
        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole($model->name);
        $role->name = $model->name_new;
        $role->description = $model->description;
        $authManager->update($model->name, $role);
    }

    public function delRole($model){
        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole($model->name);
        $authManager->remove($role);
    }

    /**
     * 更新角色权限
     */
    public function updRoleItem($roleName, $items){
        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole($roleName);
        $authManager->removeChildren($role);
        foreach ($items as $item){
            $item = $authManager->getPermission($item);
            $authManager->addChild($role, $item);
        }
    }

    /**
     * 获取角色
     */
    public static function getRole($adminId=0){
        $adminId = $adminId ?: Admin::getCurrentId();
        if($roles = \Yii::$app->authManager->getRolesByUser($adminId)){
            return array_key_first($roles);
        }
        return '';
    }

    /**
     * 获取角色的所有权限
     */
    public static function getRoleItems($roleName){
        if(self::SUPER_ADMIN == $roleName){
            return AuthItem::simpleListItem();
        }else{
            $result = Yii::$app->authManager->getPermissionsByRole($roleName);
            $nameList = array_keys($result);

            return AuthItem::find()->select('name,menu_id,description')
                ->where(['type'=>Item::TYPE_PERMISSION])->andWhere(['in', 'name', $nameList])->asArray()->all();
        }
    }

    /**
     * 获取账号的所有菜单
     */
    public static function getMenuList($adminId=0){
        $adminId = $adminId ?$adminId: Admin::getCurrentId();
        $authManager = Yii::$app->authManager;

        $menuIdList = [];
        if(self::SUPER_ADMIN != self::getRole($adminId)){
            $authList = $authManager->getPermissionsByUser($adminId);
            $itemNameList = [];
            foreach ($authList as $item){
                $itemNameList[] = $item->name;
            }
            $menuIdList = AuthItem::find()->select('menu_id')
                ->where(['in', 'name', $itemNameList])
                ->andWhere("menu_id is not null")
                ->column();
        }
        $menuIdList = array_unique($menuIdList);

        $tree = [];
        $result = AuthMenu::tree(implode(',',$menuIdList));
        foreach ($result as $node){
            if(isset($node['children'])){
                $tree[] = $node;
            }
        }
        return $tree;
    }

    /**
     * 获取角色的权限树
     */
    public static function getAuthTree($roleName){
        $menuTree = AuthMenu::tree();

        $authItemAll = ArrayHelper::index(AuthItem::simpleListItem(), 'name');
        $authItem = ArrayHelper::index(self::getRoleItems($roleName), 'name');
        $authItemMenuYes = [];
        $authItemMenuNo = [];

        // 格式化权限
        foreach ($authItemAll as $key=>$value){
            $value['allow'] = isset($authItem[$key]) ? 1 : 0;
            $temp = $value['name'];
            $value['name'] = $value['description'];
            $value['description'] = $temp;
            if($value['menu_id']){
                $authItemMenuYes[] = $value;
            }else{
                unset($value['menu_id']);
                $authItemMenuNo[] = $value;
            }
        }

        // 将权限注入菜单树
        foreach ($menuTree as &$menuParent){
            unset($menuParent['route']);
            unset($menuParent['icon']);
            unset($menuParent['id']);
            foreach ($menuParent['children'] as &$menuChild){
                foreach ($authItemMenuYes as $authItem){
                    if($authItem['menu_id'] == $menuChild['id']){
                        unset($authItem['menu_id']);
                        $menuChild['children'][] = $authItem;
                    }
                }
                unset($menuChild['route']);
                unset($menuChild['icon']);
                unset($menuChild['id']);
            }
        }

        $menuTree[] = [
            'name'=>'其它',
            'children'=>[
                [
                    'name'=>'其它',
                    'children'=>$authItemMenuNo
                ]
            ]
        ];

        return array_values($menuTree);
    }

    /**
     * API白名单
     */
    public static function isWhiteApi(){
        $api = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
        if(in_array($api, Yii::$app->params['api_white_list'])){
            return true;
        }
        return false;
    }
}