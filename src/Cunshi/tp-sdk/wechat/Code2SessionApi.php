<?php

namespace extend\wechat;

use extend\Http;

class Code2SessionApi
{
    private $_appId;
    private $_secret;
    private $_code;

    public function __construct($_appId, $_secret, $data)
    {
        $this->_appId = $_appId;
        $this->_secret = $_secret;
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