<?php


namespace backend\controllers;


use yii\web\Controller;

class IndexController extends Controller
{
    public function actionIndex(){
        $this->redirect(['html/']);
    }
}