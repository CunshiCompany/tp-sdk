<?php

namespace Cunshi\TpSdk\wx;

use Cunshi\TpSdk\tools\Http;
use Cunshi\TpSdk\tools\Random;
use Cunshi\TpSdk\tools\XMLUtils;
use HttpException;

class WechatPayTilReport
{
    private $appid;
    private $mch_id;
    private $sub_mch_id;
    private $interface_url;
    private $key;
    private static $_instance = null;

    public function __construct()
    {
        $initdata = require('../wx/conf/config.php');
        $this->appid = $initdata["wechat"]["appid"];
        $this->mch_id = $initdata["wechat"]["mch_id"];
        $this->sub_mch_id = $initdata["wechat"]["sub_mch_id"];
        $this->interface_url = $initdata["wechat"]["interface_url"];
        $this->key = $initdata["wechat"]["key"];
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function PayTilReport($execute_time_, $return_msg, $result_code)
    {
        $params = [
            "appid" => $this->appid,
            "mch_id" => $this->mch_id,
            "sub_mch_id" => $this->sub_mch_id,
            "nonce_str" => Random::alnum(32),
            "interface_url" => $this->interface_url, //接口URL
            "execute_time_" => $execute_time_, //接口耗时
            "return_msg" => $return_msg,//返回信息
            "result_code" => $result_code,
            "user_ip" => $_SERVER['REMOTE_ADDR'], //访问接口IP
        ];

        $params["sign"] = $this->makeSign($params); //获得签名
        $result = XMLUtils::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/payitil/report',
                XMLUtils::array_to_xml($params)
            )
        );
        if ($result['return_code'] == 'FAIL') {
            throw new HttpException('communicate_failed', $result['return_msg']);
        }
        return $result;
    }

    private function makeSign($arr)
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