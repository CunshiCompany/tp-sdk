<?php

namespace Cunshi\TpSdk\wx\JSAPI;

use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use Cunshi\TpSdk\tools\XMLUtils;
use HttpException;

class WechatDownLoadBill
{
    private $appid;
    private $mch_id;
    private $key;
    private static $_instance = null;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $initdata = require('../conf/config.php');
        $this->appid = $initdata["wechat"]["appid"];
        $this->mch_id = $initdata["wechat"]["mch_id"];
        $this->key = $initdata["wechat"]["key"];
    }

    /*
     *下载交易账单
     *https://api.mch.weixin.qq.com/pay/downloadbill
     * */
    public function downloadBill($bill_date)
    {
        // todo 完善sign
        $params["appid"] = $this->appid;
        $params["mch_id	"] = $this->mch_id;
        $params["nonce_str"] = Random::alnum(32);
        $params["bill_date"] = $bill_date;
        $params["sign"] = $this->makeSign($params);

        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/downloadbill',
                XMLUtils::array_to_xml($params)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }
//        微信支付提供了3份不同类型的账单文件：
//        ALL，包含了当天支付成功的订单和发起成功的退款单
//        SUCCESS，仅包含支付成功的订单
//        REFUND，仅包含发起成功的退款单
//        return json_encode($result);
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