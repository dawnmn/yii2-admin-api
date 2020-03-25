<?php

namespace backend\models;

class ModelExt extends \yii\db\ActiveRecord
{
    // 分页
    public $page = 1;
    public $page_size = 20;

    protected $extraAttributes = []; // 表外字段

    public static function table()
    {
        return preg_replace('/[\{\}\%]/', '', static::tableName());
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), $this->extraAttributes);
    }

    /**
     * 验证和过滤 ' " < > & ; \
     */
    public function filterString($attribute, $params){
        if(!($this->$attribute = preg_replace('/[\'\"<>&;\\\]/', '', trim($this->$attribute)))){
            $this->addError($attribute, $attribute . '不合法');
        }
    }

    /**
     * 验证国内电话号码
     */
    public function filterLocalPhone($attribute, $params){
        if(!preg_match('/^[\d]+$/', $this->$attribute = trim($this->$attribute))){
            $this->addError($attribute, $attribute . '不合法');
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}