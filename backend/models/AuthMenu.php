<?php

namespace backend\models;

class AuthMenu extends ModelExt
{
    protected $extraAttributes = [
        'parent_name',
    ];

    public function rules()
    {
        return [
            [['name','route'], 'required', 'on' => ['add','upd']],
            [['name','icon'],'filterString'],
            ['name', 'unique', 'on' => ['add']],
            ['name', 'filterName', 'on' => ['upd']],
            ['id', 'required', 'on' => ['upd','del']],
            ['id', 'exist', 'on' => ['upd','del']],
            [['parent','id'], 'number'],
        ];
    }

    public function filterName($attribute, $params){
        if(self::find()->where(['!=','id',$this->id])->andWhere(['name'=>$this->name])->exists()){
            $this->addError($attribute, $attribute . '不合法');
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
        $scenarios['items'] = ['page','page_size'];
        $scenarios['add'] = ['name','route','icon','parent'];
        $scenarios['upd'] = ['name','route','icon','parent','id'];
        $scenarios['del'] = ['id'];
        return $scenarios;
    }

    public function items(){
        $query = self::find()->alias('ll')
            ->select(['ll.name','ll.route','ll.icon','ll.parent','parent_name'=>'am2.name','ll.id'])
            ->leftJoin(self::table().' am2', 'am2.id=ll.parent')
            ->orderBy('ll.parent');
        return $query;
    }

    public function add(){
        $this->save(false);
    }

    public function upd(){
        $this->oldAttributes = self::findOne(['id'=>$this->id])->getOldAttributes();
        $this->save(false);
    }

    public function del(){
        self::findOne(['id'=>$this->id])->delete();
    }

    public static function simpleListParent(){
        return self::find()->select('id,name')->where("parent is null")->asArray()->all();
    }

    public static function simpleListChild(){
        return self::find()->select('id,name')->where("parent is not null")->asArray()->all();
    }

    public static function tree($where){
        $result = self::find()->select(['name','route','icon','parent','id'])
            ->where($where)->asArray()->all();

        $tree = [];
        foreach ($result as $item){
            $temp = $item;
            unset($temp['parent']);
            if(!$item['parent']){
                $tree[$item['id']] = $temp;
            }else{
                $tree[$item['parent']]['children'][] = $temp;
            }
        }
        return $tree;
    }
}