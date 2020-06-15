<?php

namespace backend\controllers;

use backend\components\Controller;
use common\libs\Helper;
use common\models\DownloadJob;
use Yii;

class DownloadJobController extends Controller
{
    public function actionList(){
        $model = new DownloadJob();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'items'))){
            return Helper::response(400, $error);
        }
        // 分页数据
        $query = $model->items();
        $paginationData = Helper::pagination($model,$query, function($list){
            foreach ($list as &$item){
                if($item['end_time']){
                    $item['status'] = 3;
                }elseif($item['begin_time']){
                    $item['status'] = 2;
                }else{
                    $item['status'] = 1;
                }
                unset($item['path']);
            }
            return $list;
        });
        return $paginationData;
    }

    public function actionDownload(){
        $model = new DownloadJob();
        // 输入验证
        if(is_string($error = Helper::validateRequest($model,'download'))){
            return Helper::response(400, $error);
        }

        if($item = DownloadJob::find()->select('path')->where(['token'=>$model->token])->asArray()->one()){
            $filename = pathinfo($item['path'], PATHINFO_BASENAME);
            Yii::$app->response->sendFile(Yii::$app->params['download_job_file_root'] . $item['path']);
            Yii::$app->response->headers->removeAll();
            ob_end_clean();
            header('Cache-Control: max-age=0');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;');
            header("Content-Disposition:attachment;filename=$filename");
        }else{
            return $this->response(-1,'参数有误');
        }
    }
}