<?php

namespace Cunshi\TpSdk\wechat;


use Cunshi\TpSdk\common\Func;
use Cunshi\TpSdk\common\Http;
use Cunshi\TpSdk\common\Random;
use Cunshi\TpSdk\common\Sign;
use Cunshi\TpSdk\exception\WechatException;

class WechatProfitSharing
{
    private $_appId;
    private $_mchId;

    private $_mchCertPath;

    private $_mchKeyPath;
    private $_mchKey;  //商户密钥


    public function __construct($app_id, $mch_id, $mch_certPath, $mch_keyPath, $mch_key)
    {
        $this->_appId = $app_id;
        $this->_mchId = $mch_id;
        $this->_mchCertPath = $mch_certPath;
        $this->_mchKeyPath = $mch_keyPath;
        $this->_mchKey = $mch_key;
    }

    /**
     * 添加分账接收方
     *
     * @return array
     */
    public function addReceiver($merchant_id, $receiver)
    {
        $params = [
            'appid' => $this->_appId,
            'mch_id' => $this->_mchId,
            // 分账出资商户号
            'sub_mch_id' => $merchant_id,
            'nonce_str' => Random::alnum(32),
            'receiver' => json_encode($receiver, JSON_UNESCAPED_UNICODE)
        ];

        $params['sign'] = Sign::getSign($this->_mchKey, $params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingaddreceiver',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }

    /**
     * 获取最大分账比例
     *
     * @return array
     */
    public function maxRatio($merchant_id)
    {
        $params = [
            'mch_id' => $this->_mchId,
            // 分账出资商户号
            'sub_mch_id' => $merchant_id,
            'nonce_str' => Random::alnum(32)
        ];

        $params['sign'] = Sign::getSign($this->_mchKey, $params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingmerchantratioquery',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }

    /**
     * 获取待分账金额
     *
     * @return array
     */
    public function orderAmount($transaction_id)
    {
        $params = [
            'mch_id' => $this->_mchId,
            'transaction_id' => $transaction_id,
            'nonce_str' => Random::alnum(32)
        ];

        $params['sign'] = Sign::getSign($this->_mchKey,$params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingorderamountquery',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }

    /**
     * 单次分账
     *
     * @return array
     */
    public function profitSharing($merchant_id, $transaction_id, $out_order_no, $receiver)
    {
        $params = [
            'mch_id' => $this->_mchId,
            'sub_mch_id' => $merchant_id,
            'appid' => $this->_appId,
            'nonce_str' => Random::alnum(32),
            'transaction_id' => $transaction_id,
            'out_order_no' => $out_order_no,
            'receivers' => json_encode(
                $receiver,
                JSON_UNESCAPED_UNICODE
            )
        ];

        $params['sign'] = Sign::getSign($this->_mchKey,$params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/profitsharing',
                Func::array_to_xml($params),
                [
                    CURLOPT_SSLKEY => $this->_mchKeyPath,
                    CURLOPT_SSLCERT => $this->_mchCertPath,
                ]
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw  new  WechatException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }
}
