<?php

namespace common\libs;

class Response extends \yii\web\Response
{
    private $_message;

    public function setInfo($code, $message, $data=[]){
        $this->setStatusCode($code);
        $this->_message = $message;
        $this->data = $data;
        return $this;
    }

    public function getMessage(){
        return $this->_message;
    }
}