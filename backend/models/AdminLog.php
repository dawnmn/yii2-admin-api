<?php

namespace backend\models;


use common\libs\Helper;
use common\models\ModelExt;

class AdminLog extends ModelExt
{
    protected $extraAttributes = [
        'time_from',
        'time_to',
        'admin',
    ];

    public function rules()
    {
        return [
            ['ip', 'ip'],
            ['admin', 'filterString'],
            [['time_from','time_to'],'date' ],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['items'] = ['ip', 'admin', 'time_from','time_to','page', 'page_size'];
        return $scenarios;
    }

    public function items(){
        $query = self::find()->alias('ll')
            ->select("ll.id,ll.content,ll.ip,ll.create_time,a.username admin")
            ->innerJoin('admin a', 'a.id=ll.admin_id')
            ->orderBy("ll.create_time desc");
        $query->andFilterWhere([
            'll.ip'=>$this->ip
        ])->andFilterWhere([
            'like', 'a.username', $this->admin
        ])->andFilterWhere([
            '>=','ll.create_time', $this->time_from
        ])->andFilterWhere([
            '<=','ll.create_time', $this->time_to
        ]);
        return $query;
    }


    public function add(){
        if($this->admin_id = Admin::getCurrentId()){
            $this->create_time = date('Y-m-d H:i:s');
            $this->api = \Yii::$app->controller->id.'/'.\Yii::$app->controller->action->id;
            $this->content = AuthItem::find()
                ->select("description")
                ->where(['name'=>$this->api])
                ->scalar();
            $this->ip = Helper::getClientIp(0,true);
            $this->save(false);
        }
    }
}