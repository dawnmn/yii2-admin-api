<?php


namespace common\libs;

use Curl\Curl;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\Query;

class Helper
{
    const UNDERSCORE_SEPARATOR = '_';

    /**
     * 转成驼峰命名
     * @param string $underScore
     * @return string
     */
    public static function toCamelCase(string $underScore)
    {
        $underScore = self::UNDERSCORE_SEPARATOR. str_replace(self::UNDERSCORE_SEPARATOR, " ", strtolower($underScore));
        return ltrim(str_replace(" ", "", ucwords($underScore)), self::UNDERSCORE_SEPARATOR );
    }

    /**
     * 转成下划线命名
     * @param string $cameCase
     * @return string
     */
    public static function toUnderScore(string $cameCase)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . self::UNDERSCORE_SEPARATOR . "$2", $cameCase));
    }

    /**
     * API 接口返回函数
     * @param string $code
     * @param string $message
     * @param array $data
     * @return mixed
     */
    public static function response(string $code, string $message, array $data=[]){
        return \Yii::$app->response->setInfo($code, $message, $data);
    }

    /**
     * 验证请求参数，并将参数放入模型中
     * @param Model $model
     * @param string $scenario
     * @param array $requestData
     * @return bool|string
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
     * @param Model $model
     * @param Query $query
     * @param callable|null $format
     * @return array
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
     * @param $data
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

    /**
     * 生成订单号
     * @param int|string $userId
     * @return string
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
     * @param int $type
     * @param bool $adv
     * @return mixed
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
     */
    public static function post($url, $data, $options = []){
        $curl = new Curl();
        $curl->setDefaultDecoder($assoc = true);
        $curl->setTimeout($options['timeout'] ?? 30);
        $curl->setOpt(CURLOPT_HTTPHEADER, $options['headers'] ?? ['Content-Type: application/json;charset=utf-8']);
        $curl->setOpt(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $data = $data ?: [];
        $result = $curl->post($url, $data);

        return $result;
    }

    /**
     * 过滤不可见字符
     * @param string|string[] $string
     * @return string|string[]|null
     */
    public static function filterInvisibleString($string){
        $pattern = "/[\x{007f}-\x{009f}]|\x{00ad}|[\x{0483}-\x{0489}]|[\x{0559}-\x{055a}]|\x{058a}|[\x{0591}-\x{05bd}]|\x{05bf}|[\x{05c1}-\x{05c2}]|[\x{05c4}-\x{05c7}]|[\x{0606}-\x{060a}]|[\x{063b}-\x{063f}]|\x{0674}|[\x{06e5}-\x{06e6}]|\x{070f}|[\x{076e}-\x{077f}]|\x{0a51}|\x{0a75}|\x{0b44}|[\x{0b62}-\x{0b63}]|[\x{0c62}-\x{0c63}]|[\x{0ce2}-\x{0ce3}]|[\x{0d62}-\x{0d63}]|\x{135f}|[\x{200b}-\x{200f}]|[\x{2028}-\x{202e}]|\x{2044}|\x{2071}|[\x{f701}-\x{f70e}]|[\x{f710}-\x{f71a}]|\x{fb1e}|[\x{fc5e}-\x{fc62}]|\x{feff}|\x{fffc}/u";
        return preg_replace($pattern, "", $string);
    }

    /**
     * 获取当前时间戳（微秒）
     * @return int
     */
    public static function currentTimeMillis(){
        return (int)bcmul(microtime(true), 1000, 0);
    }
}