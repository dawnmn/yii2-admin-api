<?php


namespace common\libs;

use yii\data\Pagination;

class Helper
{
    const UNDERSCORE_SEPARATOR = '_';

    /**
     * 转成驼峰命名
     */
    public static function toCamelCase($underScore)
    {
        $underScore = self::UNDERSCORE_SEPARATOR. str_replace(self::UNDERSCORE_SEPARATOR, " ", strtolower($underScore));
        return ltrim(str_replace(" ", "", ucwords($underScore)), self::UNDERSCORE_SEPARATOR );
    }

    /**
     * 转成下划线命名
     */
    public static function toUnderScore($cameCase)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . self::UNDERSCORE_SEPARATOR . "$2", $cameCase));
    }

    /**
     * 数组键名转成下划线命名
     */
    public static function arrayKeyToCamelCase($array){
        foreach ($array as $k=>$v){
            if(($kNew = self::toCamelCase($k)) != $k){
                $array[$kNew] = $v;
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * 只获取类名
     */
    public static function classBasename($object){
        return basename(str_replace('\\', '/', get_class($object)));
    }

    /**
     * 验证请求参数
     */
    public static function validateRequest($model, $scenario, $requestData = []){
        $requestData = $requestData ?: \Yii::$app->request->post();
        $model->scenario = $scenario;
        // 处理$requestData为空数组load方法返回false
        $requestData['__for_load__'] = '';
        if($model->load($requestData, '') && $model->validate()){
            return true;
        }else{
            $errors = '';
            $validateErrors = $model->getErrors();
            if(YII_DEBUG){
                array_walk($validateErrors, function ($value,$key) use (&$errors){
                    $errors .= "[$key:{$value[0]}]";
                });
            }else{
                $errors = array_shift($validateErrors)[0];
            }

            return $errors;
        }
    }

    /**
     * 分页数据集中处理
     */
    public static function pagination($model, $query, $format = null){
        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => is_numeric($model->page_size) ? $model->page_size : 20, // 在这里验证page_size
            'page' => is_numeric($model->page) ? $model->page - 1 : 0 // 在这里验证page
        ]);
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)->all();
        foreach ($list as &$item){
            if(is_object($item)){
                $item = $item->toArray();
            }
        }

        $data = [
            'list'=>$format ? call_user_func($format, $list) : $list,
            'pagination'=>[
                'total_count'=>(int)$pagination->totalCount,
            ]
        ];
        return $data;
    }

    /**
     * 返回JSON
     */
    public static function responseJson($data){
        ob_end_clean();
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * 显示请求JSON
     */
    public static function requestJson(){
        ob_end_clean();
        echo json_encode(\Yii::$app->request->post());die;
    }

    /**
     * 把数组的空字段转换成0
     */
    public static function arrayEmptyToZero($array){
        foreach ($array as &$item){
            $item = $item ?: 0;
        }
        return $array;
    }

    /**
     * 获取时间段内的月
     */
    public static function getMonths($timeFrom, $timeTo){
        $timeFrom = strtotime($timeFrom);
        $timeTo = strtotime($timeTo);
        $months = [];
        for(; $timeFrom <= $timeTo;  $timeFrom = strtotime('+1 month', $timeFrom)){
            $months[] = date('Y-m',$timeFrom);
        }
        return $months;
    }

    /**
     * 获取时间段内的日
     */
    public static function getDays($timeFrom, $timeTo){
        $timeFrom = strtotime($timeFrom);
        $timeTo = strtotime($timeTo);
        $days = [];
        for(; $timeFrom <= $timeTo;  $timeFrom = strtotime('+1 day', $timeFrom)){
            $days[] = date('Y-m-d',$timeFrom);
        }
        return $days;
    }

    /**
     * 通过值删除数组元素
     */
    public static function arrayUnsetByValue(&$array, $value){
        if(!is_array($array)){
            return;
        }

        $keys = array_keys($array, $value);
        if(!empty($keys)){
            foreach ($keys as $key) {
                unset($array[$key]);
            }
        }
    }

    /**
     * 生成订单号
     */
    public static function orderNo($userId)
    {
        $userId = substr($userId, -3);
        if (strlen($userId) < 3) {
            $userId = str_pad($userId, 3, '0', STR_PAD_LEFT);
        }

        $years = [20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32];
        return (array_search(date('y'), $years) + 1) . $userId . substr(time(), -9) . substr(microtime(), 2, 5);
    }
}