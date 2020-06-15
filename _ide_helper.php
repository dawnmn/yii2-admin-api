<?php
/** 组件提示,无任何实际功能
 * Class Yii
 */
class Yii
{
    /**
     * @var MyApplication
     */
    public static $app;

}

/** 自定义应用组件
 * Class MyApplication
 */
class MyApplication
{
    /** components redis */
    /** @var yii\redis\Connection */
    public $redis ;
}