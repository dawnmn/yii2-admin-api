<?php


namespace common\libs;

use PhpOffice\PhpSpreadsheet\IOFactory;

/*
链式操作，导出示例
$data = [
    ['a'=>100,
        'b'=>'asdf']
];

$excel = new Excel();
$excel->initWriter('haha',[
    '用户电话号码'=>Excel::CELL_TYPE_INT,
    '双挖普通矿机'=>Excel::CELL_TYPE_STRING,
]);
$excel->addData($data);
$excel->addData($data);
$excel->save(); // 存储到本地
$excel->download(); // 直接下载
*/

class Excel
{
    // 数据格式
    const CELL_TYPE_STRING = 'string';
    const CELL_TYPE_INT = 'integer';
    const CELL_TYPE_FLOAT = 'float';

    protected $writer;
    protected $title;
    protected $header;
    protected $data;

    public function __construct()
    {
        $this->writer = new XLSXWriter();
        $this->data = [];
    }

    /**
     * 初始化写
     * @param string $title
     * @param array $header
     * @return $this
     */
    public function initWriter(string $title, array $header){
        $this->title = $title;
        $this->header = $header;
        $this->writer = new XLSXWriter();
        $this->writer->writeSheetHeader($this->title, $this->header);
        return $this;
    }

    /**
     * 写装载数据
     * @param array $data
     * @return $this
     */
    public function addData(array &$data){
        foreach ($data as $item){
            $this->writer->writeSheetRow($this->title,$item);
        }
        return $this;
    }

    /**
     * 直接下载
     */
    public function download(){
        $fileName = $this->title.'_'.date('YmdHis').'.xlsx';
        ob_end_clean();
        header('Cache-Control: max-age=0');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;');
        header("Content-Disposition:attachment;filename=$fileName");
        $this->writer->writeToStdOut();
        exit(0);
    }

    /**
     * 保存为文件
     * @return array
     * @throws \yii\base\Exception
     */
    public function save(){
        $dir = \Yii::getAlias('@data') . '/excel/' . date("Ymd") . '/';
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }

        $fileName = $this->title.'_'.date('YmdHis').'_'.\Yii::$app->security->generateRandomString(6).'.xlsx';
        $token = md5($fileName);
        $relativePath = 'excel/' . date("Ymd") . '/' . $fileName;
        $this->writer->writeToFile($dir.$fileName);

        return [
            'path'=>$relativePath,
            'token'=>$token
        ];
    }

    /**
     * 从文件中读取 建议异步 popen
     * @param $file
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function read($file){
        $spreadsheet = IOFactory::createReader("Xlsx")->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $location = $sheet->getHighestRowAndColumn();
        $row = $location['row'];
        $column = $location['column'];
        $column = ord($column) - ord('A') + 1;

        $data = [];
        for($i=2;$i<=$row;$i++){
            $item = [];
            for($j=1;$j<=$column;$j++){
                $item[$j-1] = trim(is_object($value = $sheet->getCellByColumnAndRow($j, $i)->getValue()) ? $value->__toString() : $value);
            }
            if(!implode('',$item)){
                continue;
            }
            $data[] = $item;
        }
        return $data;
    }

    /**
     * 获取标题
     * @return mixed
     */
    public function getTitle(){
        return $this->title;
    }
}