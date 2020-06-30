<?php


namespace console\controllers;

use yii\console\Controller;
use yii\helpers\FileHelper;

class ClearDownloadController extends Controller
{
    /**
     * 每日任务 删除7天前的下载文件缓存
     */
    public function actionRun(){
        $path = \Yii::getAlias('@data') . '/excel/';
        $dirList = scandir($path);
        $time = strtotime(date('Y-m-d', strtotime('-7 day', strtotime(date('Y-m-d')))));
        foreach ($dirList as $dir){
            if(is_dir($path.$dir) && $dir!='.' && $dir != '..' && strtotime($dir) <= $time){
                FileHelper::removeDirectory($path.$dir);
            }
        }
    }
}