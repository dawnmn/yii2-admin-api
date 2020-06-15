<?php

namespace common\models;

use backend\models\Admin;

class DownloadJob extends ModelExt
{
    protected $extraAttributes = [
    ];

    public function rules()
    {
        return [
            ['token', 'required', 'on'=>['download']],
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
        $scenarios['items'] = ['page','page_size'];
        $scenarios['download'] = ['token'];
        return $scenarios;
    }

    public function items(){
        $query = self::find()
            ->where("admin_id=".Admin::getCurrentId())
            ->orderBy('create_time desc');
        return $query;
    }
}