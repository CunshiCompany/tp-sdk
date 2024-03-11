<?php

return [
//todo 完善读写文件
    "wechat" => [
        "appid" => "", //微信公众账号或开放平台APP的唯一标识
        "mch_id" => "", //微信支付商户号
        "key" => "", //API密钥
        "secret" => "", //APPID对应的接口密码
        "sub_appid" => "",//子商户公众账号ID
        "sub_mch_id" => "",//子商户号
        "notify_url" => "",//通知地址
        "profitn_notify_url" => "",//分账通知地址
        "jsapi_sign_type" => "MD5",//jsapi签名方式
        "profitsharing_sign_type" => "HMAC-SHA256",
        "mch_cert_path" => "",//证书路径
        "mch_key_path" => "",//证书 key 路径
        "interface_url" => "https://api.mch.weixin.qq.com/pay/unifiedorder",//接口url
    ]
];