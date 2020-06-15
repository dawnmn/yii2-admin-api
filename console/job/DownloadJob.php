<?php


namespace console\job;


use backend\models\Admin;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use \common\models\DownloadJob as Download;

class DownloadJob extends BaseObject implements \yii\queue\JobInterface
{
    public $model;
    public $modelMethod;
    public $downloadId;

    public function execute($queue)
    {
        $download = Download::findOne(['id' => $this->downloadId]);
        $download->begin_time = date('Y-m-d H:i:s');
        $download->save(false);

        $modelClass = get_class($this->model);
        $modelMethod = $this->modelMethod;
        $result = (new $modelClass($this->model))->$modelMethod()->save();

        $download->end_time = date('Y-m-d H:i:s');
        $download->path = $result['path'];
        $download->token = $result['token'];
        $download->save(false);
    }

    public static function push(ActiveRecord $model, $method='excel'){
        $datetime = date('Y-m-d H:i:s');
        $download = new Download([
            'admin_id' => Admin::getCurrentId(),
            'create_time' => $datetime,
            'name'=>$model->$method(true)->getTitle()
        ]);
        $download->save(false);

        return \Yii::$app->queue->push(new self([
            'model'=>$model,
            'modelMethod'=>$method,
            'downloadId'=>$download->id,
        ]));
    }
}