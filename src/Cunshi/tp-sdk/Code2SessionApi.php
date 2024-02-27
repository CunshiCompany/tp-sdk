<?php

namespace extend\wechat;

use extend\Http;
use think\facade\Env;

class Code2SessionApi
{
    private $_code;

    public function __construct($data)
    {
        $this->_appId  = Env::get('wechat.appid');
        $this->_secret = Env::get('wechat.secret');
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