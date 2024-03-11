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
     * https://api.mch.weixin.qq.com/pay/profitsharingaddreceiver
     * 添加分账接收方
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
     * 删除分账接收方
     * https://api.mch.weixin.qq.com/pay/profitsharingremovereceiver
     * @return void
     */
    public function deleteReceiver($merchant_id, $receiver)
    {
        $params = [
            'appid' => $this->_appId,
            'mch_id' => $this->_mchId,
            'sub_mch_id' => $merchant_id,
            'nonce_str' => Random::alnum(32),
            'receiver' => json_encode($receiver, JSON_UNESCAPED_UNICODE)
        ];

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingremovereceiver',
                XMLUtils::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

        return $result;
    }

    /**
     * 查询最大分账比例API
     * https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_11&index=8
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
     * 查询订单待分账金额
     * https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_10&index=7
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
     * 请求单次分账
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
            ),
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

    /**
     * 查询分账结果
     * 文档 https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_2&index=3
     * @return void
     */
    public function querySharingResult($merchant_id, $transaction_id, $out_order_no)
    {
        $params = [
            "mch_id" => $this->_mchId,
            "sub_mch_id" => $merchant_id,
            "nonce_str" => Random::alnum(32),
            "transaction_id" => $transaction_id,
            "out_order_no" => $out_order_no,
        ];

        $params["sign"] = Sign::getSign($params, 'HMAC-SHA256');

        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingquery',
                XMLUtils::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }
        return $result;
    }

    /**
     * 完结分账
     * https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_5&index=6
     * @return void
     *
     */
    public function profitsharingFinish($merchant_id, $transaction_id, $out_order_no, $description)
    {
        $params = [
            'mch_id' => $this->_mchId,
            'sub_mch_id' => $merchant_id,
            'appid' => $this->_appId,
            'nonce_str' => Random::alnum(32),
            'transaction_id' => $transaction_id,
            'out_order_no' => $out_order_no,
            'description' => $description
        ];

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/profitsharingfinish',
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

    /*
     * 分账回退
     *https://pay.weixin.qq.com/wiki/doc/api/allocation_sl.php?chapter=25_7&index=9
     */
    public function profitSharingReturn($merchant_id, $transaction_id, $out_order_no, $out_return_no, $return_amount, $description)
    {
        $params = [
            'mch_id' => $this->_mchId,
            'sub_mch_id' => $merchant_id,
            'appid' => $this->_appId,
            'nonce_str' => Random::alnum(32),
            'transaction_id' => $transaction_id,
            'out_order_no' => $out_order_no,
            'out_return_no' => $out_return_no,
            'return_account_type' => $merchant_id,
            'return_account' => $merchant_id,
            'return_amount' => $return_amount,
            'description' => $description
        ];

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/profitsharingreturn',
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
}
