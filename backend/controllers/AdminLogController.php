<?php

namespace backend\controllers;

use backend\components\Controller;
use backend\components\Helper;
use backend\models\AdminLog;

class AdminLogController extends Controller
{
    public function actionList(){
        $model = new AdminLog();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'items'))){
            return Helper::response(400, $error);
        }
        // 分页数据
        $query = $model->items();
        $paginationData = Helper::pagination($model,$query);
        return $paginationData;
    }
}