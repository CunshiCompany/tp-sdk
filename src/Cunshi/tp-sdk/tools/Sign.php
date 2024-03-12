<?php

namespace Cunshi\TpSdk\tools;


use function Cunshi\TpSdk\format_params;

class Sign
{
    // todo 添加format_params方法
    public static function getSign($params, $sign_type = 'MD5')
    {
        $initdata = require('../conf/config.php');
        if (!$params) return '';

        $mch_key = $initdata["wechat"]["secret"];
        ksort($params);
        $params_str = (new Sign)->format_params($params) . '&key=' . $mch_key;
        $sign_str = '';

        if ($sign_type == 'MD5') {
            $sign_str = md5($params_str);
        } elseif ($sign_type == 'HMAC-SHA256') {
            $sign_str = hash_hmac('sha256', (new Sign)->format_params($params) . '&key=' . $mch_key, $mch_key);
        }
        return strtoupper($sign_str);
    }

    private function format_params($params): string
    {
        $arr = array_filter($params); // 数组去空值
        ksort($arr); // 按键字典序排序
        $string_a = http_build_query($params);
        return $string_a;
    }
}
