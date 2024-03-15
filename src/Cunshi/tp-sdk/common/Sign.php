<?php

namespace Cunshi\TpSdk\common;


class Sign
{
    public static function getSign($mch_key, $params, $sign_type = 'MD5')
    {
        if (!$params) return '';
        ksort($params);
        $params_str = Func::format_params($params, false) . '&key=' . $mch_key;
        $sign_str   = '';

        if ($sign_type == 'MD5') {
            $sign_str = md5($params_str);
        } elseif ($sign_type == 'HMAC-SHA256') {
            $sign_str = hash_hmac('sha256', Func::format_params($params, false) . '&key=' . $mch_key, $mch_key);
        }

        return strtoupper($sign_str);
    }
}
