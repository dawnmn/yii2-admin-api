<?php

namespace backend\service;

use common\libs\Helper;
use Yii;

/**
 * echart数据
 */
class ChartService
{
    const TIME_FROM = '2017-08-01 00:00:00';
    const DATA_TEMPLATE = [
        'x_axis'=>[],
        'y_axis'=>[]
    ];
    const X_TYPE_MONTH = 'month';
    const X_TYPE_DAY = 'day';
    const X_TYPE_HOUR = 'hour';

    protected $xType;
    protected $timeFrom;
    protected $timeTo;
    protected $where;
    protected $data;
    protected $dateFormat;

    public function __construct($options = [])
    {
        $this->timeFrom = $options['time_from'] ?? self::TIME_FROM;
        $this->timeTo = $options['time_to'] ?? date('Y-m-d H:i:s');
        $this->xType = $options['x_type'] ?? self::X_TYPE_MONTH;
    }

    protected function buildData(){
        $this->data['x_axis'] = Helper::getDays($this->timeFrom, $this->timeTo);
        switch ($this->xType){
            case self::X_TYPE_MONTH:
                $this->dateFormat = '%Y-%m';
                break;
            case self::X_TYPE_DAY:
                $this->dateFormat = '%Y-%m-%d';
                break;
            case self::X_TYPE_HOUR:
                $this->dateFormat = '%H';
                break;
        }
    }

    /**
     * 示例
     */
    public function example(){
        $this->buildData();
        $this->where = '';
        $this->data['y_axis'][] = $this->auxExampleOne();
        $this->data['y_axis'][] = $this->auxExampleTwo();

        return $this->data;
    }

    /**
     * 示例
     */
    protected function auxExampleOne(){
        $sql = "
select date_format(operate_time, '{$this->dateFormat}') x,count(uid) y
from example mo
where {$this->where}
group by x
        ";
        $result = Yii::$app->db_core->createCommand($sql)->queryAll();

        $list = [];
        foreach ($this->data['x_axis'] as $x){
            $y = '0';
            foreach ($result as $item){
                if($x == $item['x']){
                    $y = $item['y'];
                    break;
                }
            }
            $list[] = $y;
        }

        return $list;
    }

    /**
     * 示例
     */
    protected function auxExampleTwo(){
        $sql = "
select date_format(operate_time, '{$this->dateFormat}') x,count(uid) y
from example mo
where {$this->where}
group by x
        ";
        $result = Yii::$app->db_core->createCommand($sql)->queryAll();

        $list = [];
        foreach ($this->data['x_axis'] as $x){
            $y = '0';
            foreach ($result as $item){
                if($x == $item['x']){
                    $y = $item['y'];
                    break;
                }
            }
            $list[] = $y;
        }

        return $list;
    }
}