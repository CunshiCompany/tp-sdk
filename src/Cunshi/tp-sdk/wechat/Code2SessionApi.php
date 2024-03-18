<?php

namespace Cunshi\TpSdk\wechat;

use Cunshi\TpSdk\common\Http;

class Code2SessionApi
{
    private $_appId;
    private $_secret;
    private $_code;

    public function __construct($app_id, $secret, $data)
    {
        $this->_appId  = $app_id;
        $this->_secret = $secret;
        $this->_code   = $data['code'];
    }

    public function getRes()
    {
        return json_decode(
            Http::get(
                'https://api.weixin.qq.com/sns/jscode2session',
                [
                    'appid'      => $this->_appId,
                    'secret'     => $this->_secret,
                    'js_code'    => $this->_code,
                    'grant_type' => 'authorization_code'
                ]
            ),
            true
        );
    }
}
