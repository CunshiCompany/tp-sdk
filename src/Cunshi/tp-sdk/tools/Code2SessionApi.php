<?php

namespace Cunshi\TpSdk\tools;


class Code2SessionApi
{
    private $_code;
    private $_appId;

    private $_secret;

    public function __construct($data)
    {
        $initdata = require('../wx/conf/config.php');
        $this->_appId = $initdata["wechat"]["appid"];
        $this->_secret = $initdata["wechat"]["secret"];
        $this->_code = $data['code'];
    }

    public function getRes()
    {
        return json_decode(
            Http::get(
                'https://api.weixin.qq.com/sns/jscode2session',
                [
                    'appid' => $this->_appId,
                    'secret' => $this->_secret,
                    'js_code' => $this->_code,
                    'grant_type' => 'authorization_code'
                ]
            ),
            true
        );
    }
}