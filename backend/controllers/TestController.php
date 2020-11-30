<?php

namespace backend\controllers;

use backend\models\AuthItem;
use backend\service\AuthService;
use common\libs\Aes;
use common\libs\Api;
use common\libs\Helper;
use yii\db\Exception;
use yii\rbac\Item;
use yii\web\Controller;

class TestController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex(){

    }

    /**
     * Api测试
     */
    public function actionTestApi(){
        if($result = (new Api(Api::KEY_SECRET_LIST[0]['key'],Api::KEY_SECRET_LIST[0]['secret']))
            ->get('http://yii2.admin.com/test/test-api-echo',[
            'user_id'=>1003,
            'name'=>'123abc',
                'tags'=>[
                    'good',
                    'handsome'
                ],
                'history'=>[
                    'young'=>'hhh',
                    'old'=>232
                ],
//                'file'=>new \CURLFile('/home/wwwroot/yii2_admin/data/image.jpg'),
//                'file2'=>new \CURLFile('/home/wwwroot/yii2_admin/data/image.jpg'),
        ])){
            return $result;
        }
        return Helper::response(510, '请求异常');
    }

    /**
     * Api响应测试
     */
    public function actionTestApiEcho(){
        $data = \Yii::$app->request->get();
        $header = \Yii::$app->request->headers;
        if(is_string($result = (new Api('',''))->verify($data, $header))){
            return Helper::response(511, $result, ['a'=>100]);
        }
        return $data;
    }

    /**
     * 邮件测试
     */
    public function actionTestEmail(){
        \Yii::$app->mailer->compose()
            ->setFrom('yourname1@126.com')
            ->setTo('yourname2@126.com')
            ->setSubject('邮件测试')
            ->setHtmlBody('<h1>邮件测试成功</h1>')
            ->send();
    }

    /**
     * Aes测试
     */
    public function actionAes(){
        $keyIv = Aes::getKeyIv();
        $aes = new Aes($keyIv['key'], $keyIv['iv']);
        echo $token = $aes->encode('13099998888');
        echo $aes->decode($token);
    }

    /**
     * 刷新权限列表
     */
    public function actionAuthItemRefresh(){
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            $authServer = new AuthService();
            $itemListOld = AuthItem::find()->select('name')->where(['type'=>Item::TYPE_PERMISSION])->column();
            $model = new \stdClass();

            $itemList = $itemListAdd = [];
            $controllerList = scandir(__DIR__);
            foreach ($controllerList as $controller){
                if(strpos($controller, 'Controller') === false){
                    continue;
                }
                $controller = str_replace('.php', '', $controller);
                $class = "backend\\controllers\\".$controller;
                $methodList = (new \ReflectionClass($class))->getMethods();
                foreach ($methodList as $action){
                    if($action->class != $class){
                        continue;
                    }
                    $action = $action->getName();
                    if(strpos($action, 'action') === false){
                        continue;
                    }
                    $item = str_replace('_', '-',
                        Helper::toUnderScore(str_replace('Controller', '', $controller))
                        . '/'. Helper::toUnderScore(str_replace('action', '', $action)));
                    $itemList[] = $item;
                    if(!in_array($item, $itemListOld)){
                        $model->name = $item;
                        $model->description = '';
                        $model->menu_id = null;
                        $authServer->addItem($model);
                        $itemListAdd[] = $item;
                    }
                }
            }

            $itemListDel = array_diff($itemListOld, $itemList);

            $model = new \stdClass();
            foreach ($itemListDel as $item){
                $model->name = $item;
                $authServer->delItem($model);
            }

            $transaction->commit();
        }catch (\Throwable $exception){
            $transaction->rollBack();
            throw $exception;
        }

        return $itemListAdd;
    }
}