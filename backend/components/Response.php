<?php

namespace backend\components;

class Response extends \yii\web\Response
{
    private $_message;

    public function setInfo($code, $message){
        $this->setStatusCode($code);
        $this->_message = $message;
        return $this;
    }

    public function getMessage(){
        return $this->_message;
    }
}