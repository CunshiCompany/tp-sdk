<?php

namespace Cunshi\TpSdk\wx;

use Cunshi\TpSdk\tools\XMLUtils;
use HttpException;

class WechatPayResultNotify
{
    private static $_instance = null;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=9_7
     * 支付结果通用通知
     * @throws HttpException
     */
    public function WechatPayResultNotify($result)
    {
        $result = XMLUtils::xml_to_array($result);
        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

        if ($result['result_code'] == 'SUCCESS') {
            $response = ['return_code' => 'SUCCESS'];
            return XMLUtils::array_to_xml($response);
        }
        $response = ['return_code' => 'FAIL'];
        return XMLUtils::array_to_xml($response);
    }
}