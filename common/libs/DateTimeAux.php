<?php


namespace common\libs;


class DateTimeAux
{
    /**
     * 获取时间段内的日
     * @param string $timeFrom
     * @param string $timeTo
     * @return array
     */
    public static function getPeriodDays(string $timeFrom, string $timeTo){
        $timeFrom = strtotime($timeFrom);
        $timeTo = strtotime($timeTo);
        $days = [];
        for(; $timeFrom <= $timeTo; $timeFrom = strtotime('+1 day', $timeFrom)){
            $days[] = date('Y-m-d',$timeFrom);
        }
        return $days;
    }

    /**
     * 获取时间段内的月
     * @param string $timeFrom
     * @param string $timeTo
     * @return array
     */
    public static function getPeriodMonths(string $timeFrom, string $timeTo){
        $timeFrom = strtotime(substr($timeFrom, 0, 7));
        $timeTo = strtotime($timeTo);
        $months = [];
        for(; $timeFrom <= $timeTo; $timeFrom = strtotime('+1 month', $timeFrom)){
            $months[] = date('Y-m',$timeFrom);
        }
        return $months;
    }
}