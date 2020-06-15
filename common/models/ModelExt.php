<?php

namespace common\models;

use common\libs\Excel;
use yii\db\ActiveQuery;
use yii\db\Query;

class ModelExt extends \yii\db\ActiveRecord
{
    protected $extraAttributes = []; // 表外字段
    const EXCEL_QUERY_PAGE_SIZE = 10000; // excel查询数据库分页条数

    /**
     * 获取表名
     * @return string|null
     */
    public static function table()
    {
        return preg_replace('/[\{\}\%]/', '', static::tableName());
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['page', 'page_size'], $this->extraAttributes);
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
     * 验证电话号码
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

    /**
     * 构建excel写对象
     * @param string $title
     * @param array $header
     * @param ActiveQuery $query
     * @param callable $format
     * @param bool $isStruct
     * @return Excel
     */
    public function buildExcelWrite(string $title, array $header, Query $query, Callable $format, bool $isStruct = false): Excel{
        $excel = new Excel();
        $excel->initWriter($title, $header);
        if ($isStruct){
            return $excel;
        }

        $page = 0;
        $pageSize = self::EXCEL_QUERY_PAGE_SIZE;
        while ($list = $query->limit($pageSize)->offset($pageSize * $page++)->asArray()->all()) {
            $data = $format($list);
            $excel->addData($data);
        }
        return $excel;
    }
}