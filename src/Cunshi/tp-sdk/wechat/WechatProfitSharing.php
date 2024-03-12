<?php

namespace extend\wechat;

use extend\Func;
use extend\Http;
use extend\Random;
use HttpException;

class WechatProfitSharing
{
    private $_appId;
    private $_mchId;

    private $_mchCertPath;

    private $_mchKeyPath;


    public function __construct($_appId, $_mchId, $_mchCertPath, $_mchKeyPath)
    {
        $this->_appId = $_appId;
        $this->_mchId = $_mchId;
        $this->_mchCertPath = $_mchCertPath;
        $this->_mchKeyPath = $_mchKeyPath;
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

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingaddreceiver',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
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

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingmerchantratioquery',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
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

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingorderamountquery',
                Func::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
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

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
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
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }
}
