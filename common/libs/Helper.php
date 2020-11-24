<?php


namespace common\libs;

use Curl\Curl;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\Query;

class Helper
{
    const UNDERSCORE_SEPARATOR = '_';

    // ---------------------- 请求响应 ----------------------
    /**
     * API接口返回函数
     */
    public static function response(string $code, string $message, array $data=[]){
        return \Yii::$app->response->setInfo($code, $message, $data);
    }

    /**
     * 验证请求参数，并将参数放入模型中
     */
    public static function validateRequest(Model $model, string $scenario, array $requestData = []){
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
     * 分页数据处理
     */
    public static function pagination(Model $model, Query $query, callable $format = null){
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
        echo json_encode(\Yii::$app->request->post());
        exit;
    }

    // ---------------------- 业务函数 ----------------------

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

    /**
     * 获取客户端IP地址 $type 0 返回IP地址 1 返回IPV4地址数字 $adv 处理代理情况
     */
    public static function getClientIp(int $type = 0, bool $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_ORIGINAL_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_ORIGINAL_FORWARDED_FOR'])) {
                $ip = trim($_SERVER['HTTP_X_ORIGINAL_FORWARDED_FOR']);
            } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * curl post 请求
     * 传文件：$data = ['file'=>new \CURLFile($filename)];
     */
    public static function post($url, $data, $options = []){
        $curl = new Curl();
        $curl->setDefaultDecoder($assoc = true);
        $curl->setTimeout($options['timeout'] ?? 30);
        $curl->setOpt(CURLOPT_HTTPHEADER, $options['headers']);
        $curl->setOpt(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $data = $data ?: [];
        $result = $curl->post($url, $data);

        return $result;
    }

    // ---------------------- 字符串函数 ----------------------

    /**
     * 转成驼峰命名
     */
    public static function toCamelCase(string $underScore)
    {
        $underScore = self::UNDERSCORE_SEPARATOR. str_replace(self::UNDERSCORE_SEPARATOR, " ", strtolower($underScore));
        return ltrim(str_replace(" ", "", ucwords($underScore)), self::UNDERSCORE_SEPARATOR );
    }

    /**
     * 转成下划线命名
     */
    public static function toUnderScore(string $cameCase)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . self::UNDERSCORE_SEPARATOR . "$2", $cameCase));
    }

    /**
     * 数组键名转成下划线命名 递归
     */
    public static function arrayKeyToCamelCase($array){
        foreach ($array as $k=>$v){
            if(is_array($v)){
                $v = self::arrayKeyToCamelCase($v);
            }
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
     * 过滤不可见字符
     */
    public static function filterInvisibleString($string){
        $pattern = "/[\x{007f}-\x{009f}]|\x{00ad}|[\x{0483}-\x{0489}]|[\x{0559}-\x{055a}]|\x{058a}|[\x{0591}-\x{05bd}]|\x{05bf}|[\x{05c1}-\x{05c2}]|[\x{05c4}-\x{05c7}]|[\x{0606}-\x{060a}]|[\x{063b}-\x{063f}]|\x{0674}|[\x{06e5}-\x{06e6}]|\x{070f}|[\x{076e}-\x{077f}]|\x{0a51}|\x{0a75}|\x{0b44}|[\x{0b62}-\x{0b63}]|[\x{0c62}-\x{0c63}]|[\x{0ce2}-\x{0ce3}]|[\x{0d62}-\x{0d63}]|\x{135f}|[\x{200b}-\x{200f}]|[\x{2028}-\x{202e}]|\x{2044}|\x{2071}|[\x{f701}-\x{f70e}]|[\x{f710}-\x{f71a}]|\x{fb1e}|[\x{fc5e}-\x{fc62}]|\x{feff}|\x{fffc}/u";
        return preg_replace($pattern, "", $string);
    }

    // ---------------------- 时间函数 ----------------------

    /**
     * 获取当前时间戳（微秒）
     */
    public static function currentTimeMillis(){
        return (int)bcmul(microtime(true), 1000, 0);
    }

    /**
     * 获取时间段内的日
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

    // ---------------------- 数组函数 ----------------------

    /**
     * 批量bc计算
     */
    public static function bcMulti($bc, $list, $scale){
        $number = 0;
        foreach ($list as $value){
            $number = $bc($number, $value, $scale);
        }
        return $number;
    }

    /**
     * 批量格式化小数位数 递归
     */
    public static function numberFormatMulti(&$list, $scale){
        foreach ($list as &$value){
            if(is_array($value)){
                self::numberFormatMulti($value, $scale);
            }else{
                if(is_numeric($value) && strpos($value, '.')){
                    $value = bcadd($value, 0, $scale);
                }
            }
        }
    }

    /**
     * 数组相同键值求和 递归
     */
    public static function arraySum($list, $listSum){
        foreach ($list as $k=>$v){
            if(is_array($v)){
                $listSum[$k] = self::arraySum($list[$k], $listSum[$k]);
            }else if(is_numeric($v)){
                $listSum[$k] = bcadd($listSum[$k], $v, Helper::getFloatLength($listSum[$k]) ?:Helper::getFloatLength($v));
            }
        }
        return $listSum;
    }
}