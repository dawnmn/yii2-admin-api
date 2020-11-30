<?php

namespace backend\service;

use common\libs\Helper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * echart数据
 */
class ChartService
{
    const TIME_FROM = '2017-08-01 00:00:00';
    const DATA_TEMPLATE = [
        'x_axis'=>[],
        'y_axis'=>[] // 这是个二维数组
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
        switch ($this->xType){
            case self::X_TYPE_MONTH:
                $this->dateFormat = '%Y-%m';
                $this->data['x_axis'] = Helper::getPeriodMonths($this->timeFrom, $this->timeTo);
                break;
            case self::X_TYPE_DAY:
                $this->dateFormat = '%Y-%m-%d';
                $this->data['x_axis'] = Helper::getPeriodDays($this->timeFrom, $this->timeTo);
                break;
            case self::X_TYPE_HOUR:
                $this->dateFormat = '%H';
                $this->data['x_axis'] = range(0, 23);
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
        $result = ArrayHelper::index($result, 'x');

        $yList = [];
        foreach ($this->data['x_axis'] as $x){
            $yList[] = empty($result[$x]) ? 0 : $result[$x]['y'];
        }

        return $yList;
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
        $result = ArrayHelper::index($result, 'x');

        $yList = [];
        foreach ($this->data['x_axis'] as $x){
            $yList[] = empty($result[$x]) ? 0 : $result[$x]['y'];
        }

        return $yList;
    }
}