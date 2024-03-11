<?php

namespace Cunshi\TpSdk\wx\ProfitSharing;


use Cunshi\TpSdk\App;
use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use Cunshi\TpSdk\tools\Sign;
use Cunshi\TpSdk\tools\XMLUtils;
use http\Env;
use HttpException;
use function Cunshi\TpSdk\xml_to_array;


class WechatProfitSharing
{
    private $_appId;
    private $_mchId;

    public function __construct()
    {
        $initdata = require('../conf/config.php');

        $this->_appId = $initdata['wechat']['appid'];
        $this->_mchId = $initdata['wechat']['mch_id'];
        $this->_mchCertPath = $initdata['wechat']['mch_cert_path'];
        $this->_mchKeyPath = $initdata['wechat']['mch_key_path'];
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
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingaddreceiver',
                XMLUtils::array_to_xml($params)
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
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingmerchantratioquery',
                XMLUtils::array_to_xml($params)
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
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingorderamountquery',
                XMLUtils::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }

    /**
     * 单次分账
     * 文档https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_1&index=1
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
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/profitsharing',
                XMLUtils::array_to_xml($params),
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

    /**
     * 请求多次分账
     * 文档https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_6&index=2
     */
    public function requestMultipleSharing($merchant_id, $transaction_id, $out_order_no, $receivers)
    {
        // todo 完善params
        $params = [
            "mch_id" => $this->_mchId,
            "sub_mch_id" => $merchant_id,
            "appid" => $this->_appId,
            "nonce_str" => Random::alnum(32),
            "transaction_id" => $transaction_id,
            "out_order_no" => $out_order_no,
            "receivers" => json_encode(
                $receivers,
                JSON_UNESCAPED_UNICODE
            ), //装receiver的数组
        ];

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');

        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/multiprofitsharing',
                XMLUtils::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

//        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'FAIL') {
//            return ($result);
//        }
        // todo 可能需要解析receivers
//  $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'都成功返回

        return $result;
    }
}