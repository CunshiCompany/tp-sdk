<?php

namespace Cunshi\TpSdk\wx;

class WechatNotifyReturnStatus
{
    /**
     * 异步回调处理失败时返回内容
     *
     * @param $msg
     * @return string
     */
    public function notifyReturnFail($msg = 'FAIL')
    {
        return "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[{$msg}]]></return_msg></xml>";
    }

    /**
     * 异步回调处理成功时返回内容
     *
     * @param $msg
     * @return string
     */
    public function notifyReturnSuccess($msg = 'OK')
    {
        return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[{$msg}]]></return_msg></xml>";
    }

}