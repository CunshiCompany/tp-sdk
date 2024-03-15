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

    private $_mchKey;  //商户密钥

    public function __construct($app_id, $mch_id, $mch_key)
    {
        $this->_appId  = $app_id;
        $this->_mchId  = $mch_id;
        $this->_mchKey = $mch_key;
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

    public function paySign($openid, $out_trade_no, $total_fee, $ip, $trade_type)
    {
        $Objs = [
            'appid'            => $this->_appId,
            'mch_id'           => $this->_mchId,
            'sub_mch_id'       => $this->_subMchId,
            'nonce_str'        => Random::alnum(32),
            'body'             => $this->_orderName,
            'out_trade_no'     => $out_trade_no,
            'total_fee'        => $total_fee,
            'spbill_create_ip' => $ip,
            'notify_url'       => $this->_notifyUrl,
            'trade_type'       => $trade_type,           //JSAPI为Jsapi支付、NATIVE为Native支付
            'openid'           => $openid,
            'profit_sharing'   => $this->_profitSharing
        ];

        $Objs['sign'] = Sign::getSign($this->_mchKey, $Objs);
        $result       = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/unifiedorder',
                Func::array_to_xml($Objs)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
        }

        $prepay_id = $result['prepay_id'];

        $params = [
            'appId'     => $this->_appId,
            'timeStamp' => time(),
            'nonceStr'  => Random::alnum(32),
            'package'   => 'prepay_id=' . $prepay_id,
            'signType'  => 'MD5'
        ];

        $params['paySign'] = Sign::getSign($this->_mchKey, $params);
        return $params;
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

        return $sign == Sign::getSign($this->_mchKey,$data) ? true : false;
    }
}
