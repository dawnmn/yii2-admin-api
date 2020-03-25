<?php

namespace backend\models;

use yii\rbac\Item;

class AuthItem extends ModelExt
{
    public function getPrimaryKey($asArray = false)
    {
        return ['name'];
    }

    protected $extraAttributes = [
        'name_new',
        'menu_name',
        'keyword',
        'items',
    ];

    public function rules()
    {
        return [
            [['name','description'], 'required', 'on' => ['add','upd']],
            [['name','description'],'filterString'],
            ['name', 'unique', 'on' => ['add']],
            ['name', 'exist', 'on' => ['upd','del']],
            ['name_new', 'filterNameNew'],
            ['name_new', 'required', 'on' => ['upd']],
            [['name'], 'required', 'on' => ['del']],
            [['menu_id'], 'number'],
            [['items'], 'safe'],
            ['keyword','filterString'],
        ];
    }

    public function filterNameNew($attribute, $params){
        if(self::find()->where(['!=', 'name', $this->name])->andWhere(['name'=>$this->name_new])->exists()){
            $this->addError($attribute, $attribute . '已存在');
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
        $scenarios['items'] = ['page','page_size','keyword'];
        $scenarios['add'] = ['name','description','menu_id','items'];
        $scenarios['upd'] = ['name','description','menu_id', 'name_new','items'];
        $scenarios['del'] = ['name'];
        return $scenarios;
    }

    public function items($type){
        switch ($type){
            case Item::TYPE_ROLE:
                $query = self::find()->alias('ll')
                    ->select(['ll.name','ll.description'])
                    ->andFilterWhere([
                        'like','ll.name',$this->keyword
                    ])->orFilterWhere([
                        'like','ll.description',$this->keyword
                    ])->andWhere("type=".Item::TYPE_ROLE)
                    ->orderBy('name');
                break;
            case Item::TYPE_PERMISSION:
                $query = self::find()->alias('ll')
                    ->select(['ll.name','ll.description','menu_name'=>'am.name','menu_id'])
                    ->leftJoin(AuthMenu::table().' am', 'am.id=ll.menu_id')
                    ->andFilterWhere([
                        'like','ll.name',$this->keyword
                    ])->orFilterWhere([
                        'like','ll.description',$this->keyword
                    ])->andWhere("type=".Item::TYPE_PERMISSION)
                    ->orderBy('name');
                break;
        }

        return $query;
    }

    public static function simpleListRole($except=null){
        return self::find()->select('name')
            ->where(['type'=>Item::TYPE_ROLE])
            ->andFilterWhere(['!=', 'name', $except])
            ->column();
    }

    public static function simpleListItem(){
        return self::find()->select('name,menu_id,description')->where(['type'=>Item::TYPE_PERMISSION])->asArray()->all();
    }
}