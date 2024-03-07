<?php

namespace Cunshi\TpSdk\wx;

use app\WechatConst\WxPayConfig;
use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use Cunshi\TpSdk\tools\XMLUtils;
use HttpException;


class WechatOrderQuery
{

    private static $_instance = null;
    private $appid;
    private $mch_id;
    private $sub_mch_id;
    private $sign;
    private $key;

    /*
     *  查询订单
     * 文档 https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=9_2
     * */
    private function __construct()
    {
        $initdata = require('../wx/conf/config.php');
        $this->appid = $initdata["wechat"]["appid"];
        $this->mch_id = $initdata["wechat"]["mch_id"];
        $this->sub_mch_id = $initdata["wechat"]["sub_mch_id"];
        $this->sign = $initdata["wechat"]["sign"];
        $this->key = $initdata["wechat"]["key"];
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return mixed
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * @param mixed $appid
     */
    public function setAppid($appid): void
    {
        $this->appid = $appid;
    }

    /**
     * @return mixed
     */
    public function getMchId()
    {
        return $this->mch_id;
    }

    /**
     * @param mixed $mch_id
     */
    public function setMchId($mch_id): void
    {
        $this->mch_id = $mch_id;
    }

    /**
     * @return mixed
     */
    public function getSubMchId()
    {
        return $this->sub_mch_id;
    }

    /**
     * @param mixed $sub_mch_id
     */
    public function setSubMchId($sub_mch_id): void
    {
        $this->sub_mch_id = $sub_mch_id;
    }

    /**
     * @return mixed
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param mixed $sign
     */
    public function setSign($sign): void
    {
        $this->sign = $sign;
    }

    public function OrderQuery($transaction_id)
    {

        $Obj = [
            "appid" => $this->appid,
            "mch_id" => $this->mch_id,
            "sub_mch_id" => $this->sub_mch_id,
            "transaction_id" => $transaction_id,//微信订单号
            "nonce_str" => Random::alnum(32),//随机字符串
        ];
        $Obj["sign"] = $this->makeSign($Obj); //签名

        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/orderquery',
                XMLUtils::array_to_xml($Obj)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }
        if ($result['return_code'] == 'SUCCESS' && $result['return_msg'] == 'SUCCESS' && $result['trade_state'] == 'SUCCESS') {
            $r = ["msg" => "交易成功", "data" => $result];
            return json_encode($r);
        } else {
            $r = ["msg" => "交易失败", "data" => $result];
            return json_encode($r);
        }
    }

    public function makeSign($arr)
    {
        $arr = array_filter($arr); // 数组去空值
        ksort($arr); // 按键字典序排序
        $string_a = http_build_query($arr); // 将数组转换为参数格式字符串
        $string_a .= "&key={$this->key}"; // 将key拼接到末尾
        $string_a = urldecode($string_a); // 将url中的特殊字符转换回来
        $temp = md5($string_a); // md5加密
        return strtoupper($temp); // 将结果转为纯大写
    }
}