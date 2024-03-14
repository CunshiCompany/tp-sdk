<?php

namespace Cunshi\TpSdk\wechat;

use Cunshi\TpSdk\common\Func;
use Cunshi\TpSdk\common\Http;
use Cunshi\TpSdk\common\Random;
use Cunshi\TpSdk\common\Sign;

use Cunshi\TpSdk\exception\WechatException;

class WechatPay
{
    private $_appId;                // 小程序 appid
    private $_mchId;                // 服务商商户号
    private $_subMchId;             // 子商户号
    private $_orderName;            // 订单名称
    private $_notifyUrl;            // 支付成功回调地址
    private $_profitSharing = 'N';  // 是否需要分账

    public function __construct($app_id, $mch_id)
    {
        $this->_appId = $app_id;
        $this->_mchId = $mch_id;
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
        $prepay_id = $this->JsapiUnifiedOrder($openid, $out_trade_no, $total_fee, $ip);

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

    public function JsapiUnifiedOrder($openid, $out_trade_no, $total_fee, $ip)
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
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/unifiedorder',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
        }

        return $result['prepay_id'];
    }

    public function NativeUnifiedOrder($openid, $out_trade_no, $total_fee, $ip)
    {
        $params = [
            'appid' => $this->_appId,
            'mch_id' => $this->_mchId,
            'sub_mch_id' => $this->_subMchId,
            'nonce_str' => Random::alnum(32),
            'body' => $this->_orderName,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'spbill_create_ip' => $ip,
            'notify_url' => $this->_notifyUrl,
            'trade_type' => 'NATIVE',
            'openid' => $openid,
            'profit_sharing' => $this->_profitSharing
        ];

        $params['sign'] = Sign::getSign($params);
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/unifiedorder',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
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
}
