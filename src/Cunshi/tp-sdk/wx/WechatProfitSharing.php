<?php

namespace Cunshi\TpSdk\wx;


use Cunshi\TpSdk\App;
use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use Cunshi\TpSdk\tools\Sign;
use http\Env;
use HttpException;
use function Cunshi\TpSdk\xml_to_array;


class WechatProfitSharing
{
    private $_appId;
    private $_mchId;

    public function __construct()
    {
        $this->_appId       = Env::get('wechat.appid');
        $this->_mchId       = Env::get('wechat.mch_id');
        $this->_mchCertPath = App::getRootPath() . Env::get('wechat.mch_cert_path');
        $this->_mchKeyPath  = App::getRootPath() . Env::get('wechat.mch_key_path');
    }

    /**
     * 添加分账接收方
     *
     * @return array
     */
    public function addReceiver($merchant_id, $receiver)
    {
        $params = [
            'appid'      => $this->_appId,
            'mch_id'     => $this->_mchId,
            // 分账出资商户号
            'sub_mch_id' => $merchant_id,
            'nonce_str'  => Random::alnum(32),
            'receiver'   => json_encode($receiver, JSON_UNESCAPED_UNICODE)
        ];
        
        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingaddreceiver',
                array_to_xml($params)
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
            'mch_id'     => $this->_mchId,
            // 分账出资商户号
            'sub_mch_id' => $merchant_id,
            'nonce_str'  => Random::alnum(32)
        ];
        
        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingmerchantratioquery',
                array_to_xml($params)
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
            'mch_id'         => $this->_mchId,
            'transaction_id' => $transaction_id,
            'nonce_str'      => Random::alnum(32)
        ];
        
        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/profitsharingorderamountquery',
                array_to_xml($params)
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
            'mch_id'         => $this->_mchId,
            'sub_mch_id'     => $merchant_id,
            'appid'          => $this->_appId,
            'nonce_str'      => Random::alnum(32),
            'transaction_id' => $transaction_id,
            'out_order_no'   => $out_order_no,
            'receivers'      => json_encode(
                $receiver,
                JSON_UNESCAPED_UNICODE
            )
        ];

        $params['sign'] = Sign::getSign($params, 'HMAC-SHA256');
        $result = xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/profitsharing',
                array_to_xml($params),
                [
                    CURLOPT_SSLKEY  => $this->_mchKeyPath,
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
