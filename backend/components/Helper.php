<?php

namespace backend\components;

use yii\data\Pagination;

class Helper
{
    const INPUT_PARAMS_ERROR = '参数有误';

    /**
     * 只获取类名
     */
    public static function classBasename($object){
        return basename(str_replace('\\', '/', get_class($object)));
    }

    /**
     * 验证请求参数
     */
    public static function validateRequest($model, $scenario, $requestData = []){
        $requestData = $requestData ?: \Yii::$app->request->post();
        $model->scenario = $scenario;
        // 处理$requestData为空数组load方法返回false
        $requestData['__for_load__'] = '';
        if($model->load($requestData, '') && $model->validate()){
            return true;
        }else{
            $errors = '';
            $validateErrors = $model->getErrors();
            array_walk($validateErrors, function ($value,$key) use (&$errors){
                $errors .= "[$key:{$value[0]}]";
            });
            return self::INPUT_PARAMS_ERROR.':'.$errors;
        }
    }

    public static function simplifyRequestError($error){
        $error = str_replace(self::INPUT_PARAMS_ERROR, '', $error);
        $error = str_replace(']', ',', $error);
        $error = preg_replace('/[\:\[a-zA-Z。]*/', '', $error);
        return trim($error, ',');
    }

    public static function response($code, $message){
        return \Yii::$app->response->setInfo($code, $message);
    }

    /**
     * 分页数据集中处理
     */
    public static function pagination($model, $query, $format = null){
        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => is_numeric($model->page_size) ? $model->page_size : 20, // 在这里验证page_size
            'page' => is_numeric($model->page) ? $model->page - 1 : 0 // 在这里验证page
        ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)->all();
        foreach ($list as &$item){
            if(is_object($item)){
                $item = $item->toArray();
            }
        }

        $data = [
            'list'=>$format ? call_user_func($format, $list) : $list,
            'pagination'=>[
                'total_count'=>(int)$pagination->totalCount,
            ]
        ];
        return $data;
    }

    /**
     * 返回JSON
     */
    public static function responseJson($data){
        ob_end_clean();
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * 显示请求JSON
     */
    public static function requestJson(){
        ob_end_clean();
        echo json_encode(\Yii::$app->request->post());die;
    }
}