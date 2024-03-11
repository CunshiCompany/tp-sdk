<?php

namespace Cunshi\TpSdk\wx;

use Cunshi\TpSdk\tools\XMLUtils;
use HttpException;

class WechatRefundNotify
{
    private $key;
    private static $_instance = null;

    public function __construct()
    {
        $initdata = require('../wx/conf/config.php');
        $this->key = $initdata["wechat"]["key"];
        $this->key = md5($this->key); //加密
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /*
     * 退款结果通知
     *https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=9_16
     * */
    public function WechatRefundNotify($result)
    {
        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }
        $base64temp = base64_decode($result["req_info"]);
        $decode = openssl_decrypt($base64temp, 'AES-256-ECB', $this->key, OPENSSL_RAW_DATA);
        return XMLUtils::xml_to_array($decode);
    }
}