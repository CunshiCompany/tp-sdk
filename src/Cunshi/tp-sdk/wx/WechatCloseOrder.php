<?php

namespace Cunshi\TpSdk\wx;


use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use Cunshi\TpSdk\tools\XMLUtils;
use HttpException;

class WechatCloseOrder
{
    private static $_instance = null;
    private $appid;
    private $mch_id;
    private $sub_mch_id;
//    private $sign;
    private $key;

    private function __construct()
    {
        $initdata = require('../wx/conf/config.php');
        $this->appid = $initdata["wechat"]["appid"];
        $this->mch_id = $initdata["wechat"]["mch_id"];
        $this->sub_mch_id = $initdata["wechat"]["sub_mch_id"];
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
     *关闭订单 https://api.mch.weixin.qq.com/pay/closeorder
     *文档 https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=9_3
     */

    public function CloseOrder($out_trade_no)
    {
        $Obj = [
            "appid" => $this->appid,
            "mch_id" => $this->mch_id,
            "sub_mch_id" => $this->sub_mch_id,
            "out_trade_no" => $out_trade_no,//微信订单号
            "nonce_str" => Random::alnum(32),//随机字符串
        ];
        $Obj["sign"] = $this->makeSign($Obj); //签名

        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/closeorder',
                XMLUtils::array_to_xml($Obj)
            )
        );

        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }

        if ($result['result_code'] == 'SUCCESS') {
            $r = ["msg" => "关闭订单成功", "data" => $result];
            return json_encode($r);
        } elseif ($result['result_code'] == 'FAIL') {
            $r = ["msg" => "关闭订单失败", "data" => $result];
//            return json_encode([$result['err_code'], $result['err_code_des']]);
            return $r;
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