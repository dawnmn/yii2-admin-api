<?php


namespace common\models;


use yii\base\Model;
use yii\web\UploadedFile;

class FileUpload extends Model
{
    public $__for_load__;

    const DEFAULT_CATEGORY = 'default';
    const FILE = 'file';

    public $category;

    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false],
            ['category', 'match', 'pattern' => '/^\w+$/i'],
            ['category', 'default', 'value'=>self::DEFAULT_CATEGORY],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['file'] = ['file', 'category'];
        return $scenarios;
    }

    public function saveFile($category){
        $dir = \Yii::getAlias('@data') . '/' . "uploads/$category/" . date("Ymd") . '/';
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        $relativePath = "uploads/$category/" . date("Ymd") . '/' . \Yii::$app->security->generateRandomString(32).'.'.$this->file->extension;
        $path  = \Yii::getAlias('@data') . '/' . $relativePath;
        $this->file->saveAs($path);

        return [
            'url'=>\Yii::$app->params['upload_static_url'].$relativePath,
            'path'=>$relativePath
        ];
    }
}