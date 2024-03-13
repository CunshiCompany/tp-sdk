<?php

namespace Cunshi\TpSdk\exception;

use Exception;

class WechatException extends Exception
{
    // 构造函数，接收一个可选的异常信息
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    // 自定义的字符串表示形式
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}