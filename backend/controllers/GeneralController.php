<?php

namespace backend\controllers;

use backend\components\Controller;
use common\libs\Helper;
use common\models\FileUpload;
use yii\web\UploadedFile;

/**
 * 通用控制器
 */
class GeneralController extends Controller
{
    /**
     * API 文件上传
     */
    public function actionFileUpload(){
        // 表单验证
        $model = new FileUpload();
        $requestData[FileUpload::FILE] = UploadedFile::getInstanceByName(FileUpload::FILE); // 请求参数 注入文件
        $requestData['category'] = \Yii::$app->request->post('category'); // 请求参数 注入类型
        if (is_string($error = Helper::validateRequest($model, FileUpload::FILE, $requestData))) {
            // 兼容wangEditor5数据返回格式
            Helper::responseJson([
                'errno'=>0,
                'code'=>-1,
                'message'=>$error,
            ]);
        }

        $result = $model->saveFile($model->category);
        // 兼容wangEditor5数据返回格式
        Helper::responseJson([
            'errno'=>0,
            'code'=>200,
            'message'=>'',
            'data'=>$result,
        ]);
    }
}