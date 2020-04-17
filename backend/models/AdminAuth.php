<?php

namespace backend\models;

use yii\web\IdentityInterface;

class AdminAuth extends ModelExt implements IdentityInterface
{
    const LOGIN_EXPIRE_TIME = 604800; // 登录有效期7天
    const DEFAULT_PASSWORD = '123456'; // 默认密码

    public static function table()
    {
        return 'admin';
    }

    public static function tableName()
    {
        return '{{%admin}}';
    }

    protected $extraAttributes = [
    ];

    public function rules()
    {
        return [
            [['username','password'], 'required', 'on' => ['login']],
            [['username'], 'filterString'],
            ['password', 'filterLogin', 'on' => ['login']]
        ];
    }

    /**
     * 登录验证
     */
    public function filterLogin($attribute, $params){
        $item = self::find()->select('password, status,phone')
            ->where(['username'=>$this->username])->asArray()->one();

        if(!$item){
            $this->addError($attribute, '账号不存在或密码错误');
        }elseif($item['status'] == 0){
            $this->addError($attribute, '账号被停用');
        }elseif (!\Yii::$app->getSecurity()->validatePassword($this->$attribute, $item['password'])){
            $this->addError($attribute, '账号不存在或密码错误');
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['login'] = ['username','password'];
        return $scenarios;
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id'=>$id]);
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return [];
    }

    public function getId()
    {
        return $this->primaryKey;
    }

    public function getAuthKey()
    {
        return '';
    }

    public function validateAuthKey($authKey)
    {
        return '';
    }
}