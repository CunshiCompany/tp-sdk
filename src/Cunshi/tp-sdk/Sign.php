<?php

namespace extend\wechat;

use think\facade\Env;

class Sign
{
    public static function getSign($params, $sign_type = 'MD5')
    {
        if (!$params) return '';

        $mch_key = Env::get('wechat.mch_key');
        ksort($params);
        $params_str = format_params($params, false) . '&key=' . $mch_key;
        $sign_str = '';
        
        if ($sign_type == 'MD5') {
            $sign_str = md5($params_str);
        } elseif ($sign_type == 'HMAC-SHA256') {
            $sign_str = hash_hmac('sha256', format_params($params, false) . '&key=' . $mch_key, $mch_key);
        }

        return strtoupper($sign_str);
    }
}
