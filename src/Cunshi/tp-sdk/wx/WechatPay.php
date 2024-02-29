<?php

namespace Cunshi\TpSdk\wx;


use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use http\Env;
use HttpException;


class WechatPay
{
    private static $_instance = null;

    private $_appId;                // 小程序 appid
    private $_mchId;                // 服务商商户号
    private $_subMchId;             // 子商户号
    private $_orderName;            // 订单名称
    private $_notifyUrl;            // 支付成功回调地址
    private $_profitSharing = 'N';  // 是否需要分账

    public function __construct()
    {
        $this->_appId = Env::get('wechat.appid');
        $this->_mchId = Env::get('wechat.mch_id');
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function setSubMchId($mch_id)
    {
        $this->_subMchId = $mch_id;
        return $this;
    }

    public function setNotifyUrl($url)
    {
        $this->_notifyUrl = $url;
        return $this;
    }

    public function setOrderName($name)
    {
        $this->_orderName = $name;
        return $this;
    }

    public function setProfitSharing($profit_sharing)
    {
        if (!unserialize($profit_sharing)) {
            $this->_profitSharing = 'N';
        } else {
            $this->_profitSharing = 'Y';
        }

        return $this;
    }

    public function paySign($openid, $out_trade_no, $total_fee, $ip)
    {
        $prepay_id = $this->_unifiedorder($openid, $out_trade_no, $total_fee, $ip);

        $params = [
            'appId' => $this->_appId,
            'timeStamp' => time(),
            'nonceStr' => Random::alnum(32),
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'MD5'
        ];

        $params['paySign'] = Sign::getSign($params);
        return $params;
    }

    private function _unifiedOrder($openid, $out_trade_no, $total_fee, $ip)
    {
        $params = [
            'appid' => $this->_appId,
            'mch_id' => $this->_mchId,
            'sub_mch_id' => $this->_subMchId,
            'nonce_str' => Random::alnum(32),
            'body' => $this->_orderName,     // 商品简单描述
            'out_trade_no' => $out_trade_no,         // 商户系统内部订单号
            'total_fee' => $total_fee,            // 订单总金额，单位为分
            'spbill_create_ip' => $ip,                   // 用户端ips
            'notify_url' => $this->_notifyUrl,     // 通知地址
            'trade_type' => 'JSAPI',               // 交易类型
            'openid' => $openid,               // 用户标识
            'profit_sharing' => $this->_profitSharing  // 是否需要分账
        ];

        $params['sign'] = Sign::getSign($params);
        $result = $this->xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/unifiedorder',
                $this->array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

        return $result['prepay_id'];
    }

    /**
     * 异步签名验证
     *
     * @param  $data
     * @return bool
     */
    public function checkNotifySign($data)
    {
        if (!$data) return false;

        $sign = $data['sign'];
        unset($data['sign']);

        return $sign == Sign::getSign($data) ? true : false;
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
     * xml to array
     *
     * @param $xml
     * @return mixed
     */
    public function xml_to_array($xml)
    {
        libxml_disable_entity_loader(true); // 禁止引用外部xml实体
        $xml_string = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xml_string), true);
    }

    /**
     * array to xml
     *
     * @param $arr
     * @return string
     */
    public function array_to_xml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . self::array_to_xml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}
