<?php

namespace backend\models;

use common\libs\Excel;
use yii\helpers\ArrayHelper;

class Admin extends AdminAuth
{
    protected $extraAttributes = [
        'role_name',
        'keyword',
        'password_new',
        'password_again',
    ];

    public function rules()
    {
        $rules = [
            [['email','phone','role_name','status'], 'required', 'on' => ['add','upd']],
            [['username'], 'required', 'on' => ['add']],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9]{4,50}$/i', 'on' => ['add']],
            [['username', 'email'],'unique', 'on' => ['add']],
            ['keyword','filterString'],
            ['email','email'],
            ['email', 'filterEmail', 'on' => ['upd']],
            ['role_name','filterRoleName'],
            ['phone','filterLocalPhone'],
            ['id', 'required', 'on' => ['upd', 'reset_password']],
            ['id', 'exist', 'on' => ['upd', 'reset_password']],
            [['status','id'], 'number'],
            [['password', 'password_new', 'password_again'], 'trim'],
            [['password', 'password_new', 'password_again'], 'string', 'length' => [4, 50]],
            [['password', 'password_new', 'password_again'], 'required', 'on' => ['upd_password']],
            ['password_new', 'filterPasswordNew'],
        ];
        return array_merge($rules, parent::rules());
    }

    public function filterEmail($attribute, $params){
        if(self::find()->where(['!=', 'id', $this->id])->andWhere(['email'=>$this->$attribute])->exists()){
            $this->addError($attribute, $attribute . '已存在');
        }
    }

    public function filterRoleName($attribute, $params){
        $roleList = AuthItem::simpleListRole();
        if(!in_array($this->$attribute, $roleList)){
            $this->addError($attribute, $this->getAttributeLabel($attribute) . '不合法');
        }
    }

    public function filterPasswordNew($attribute, $params){
        $password = self::find()->select('password')
            ->where(['id'=>self::getCurrentId()])->scalar();
        if (!\Yii::$app->getSecurity()->validatePassword($this->password, $password)){
            $this->addError($attribute, '当前密码错误');
        }
        if($this->password == $this->password_new){
            $this->addError($attribute, '新旧密码相同');
        }
        if($this->password_new != $this->password_again){
            $this->addError($attribute, '两次输入的密码不一致');
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
        $scenarios['items'] = ['keyword','page','page_size'];
        $scenarios['add'] = ['username','email','role_name','status','phone'];
        $scenarios['upd'] = ['email','role_name','status','phone','id'];
        $scenarios['upd_password'] = ['password', 'password_new', 'password_again'];
        $scenarios['reset_password'] = ['id'];
        return $scenarios;
    }

    public static function getCurrentId(){
        return \Yii::$app->user->isGuest ? 0 : \Yii::$app->user->getIdentity()->getId();
    }

    public function items(){
        $query = self::find()->alias('ll')
            ->select(['ll.id','ll.username','ll.phone','ll.email','role_name'=>'aa.item_name','ll.status','ll.create_time'])
            ->leftJoin(AuthAssignment::table().' aa', 'aa.user_id=ll.id');
        $query->orFilterWhere([
            'like','ll.username',$this->keyword
        ])->orFilterWhere([
            'like','ll.phone',$this->keyword
        ])->orFilterWhere([
            'like','ll.email',$this->keyword
        ])->orderBy('ll.create_time desc');
        return $query;
    }

    public function excel($isStruct = false)
    {
        $title = '管理员列表';
        $header = [
            '编号' => Excel::CELL_TYPE_INT,
            '用户名' => Excel::CELL_TYPE_STRING,
            '手机号码' => Excel::CELL_TYPE_STRING,
            '电子邮箱' => Excel::CELL_TYPE_STRING,
            '状态' => Excel::CELL_TYPE_STRING,
            '创建时间' => Excel::CELL_TYPE_STRING,
        ];

        $buildData = function ($list){
            $data = [];
            foreach ($list as $item){
                switch ($item['status']){
                    case 1:
                        $item['status'] = '正常';
                        break;
                    default:
                        $item['status'] = '停用';
                        break;
                }
                $data[] = [
                    $item['id'],
                    $item['username'],
                    $item['phone'],
                    $item['email'],
                    $item['status'],
                    $item['create_time'],
                ];
            }
            return $data;
        };

        return parent::buildExcelWrite(
            $title,
            $header,
            $this->items(),
            $buildData,
            $isStruct
        );
    }

    public function add(){
        $datetime = date('Y-m-d H:i:s');
        $this->create_time = $datetime;
        $this->update_time = $datetime;
        $passwordDefault = \Yii::$app->security->generateRandomString(8);
        $this->password = \Yii::$app->security->generatePasswordHash($passwordDefault);

        foreach ($this->extraAttributes as $attribute){
            unset($this->$attribute);
        }

        $this->save(false);

        return [
            'password_default'=>$passwordDefault
        ];
    }

    public function upd(){
        $datetime = date('Y-m-d H:i:s');
        $this->update_time = $datetime;

        foreach ($this->extraAttributes as $attribute){
            unset($this->$attribute);
        }

        $this->oldAttributes = self::findOne(['id'=>$this->id])->getOldAttributes();
        $this->save(false);
    }

    public function updPassword(){
        $datetime = date('Y-m-d H:i:s');
        $this->update_time = $datetime;
        $this->password = \Yii::$app->security->generatePasswordHash($this->password_new);
        $this->id = self::getCurrentId();

        foreach ($this->extraAttributes as $attribute){
            unset($this->$attribute);
        }
        $this->oldAttributes = self::findOne(['id'=>$this->id])->getOldAttributes();
        $this->save(false);
    }

    public function resetPassword(){
        $datetime = date('Y-m-d H:i:s');
        $this->update_time = $datetime;

        $passwordDefault = \Yii::$app->security->generateRandomString(8);
        $this->password = \Yii::$app->security->generatePasswordHash($this->password_default);

        $this->oldAttributes = self::findOne(['id'=>$this->id])->getOldAttributes();
        $this->save(false);

        return [
            'password_default'=>$passwordDefault
        ];
    }
}
