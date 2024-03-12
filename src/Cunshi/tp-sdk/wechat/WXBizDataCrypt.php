<?php

namespace extend\wechat;

use think\facade\Env;

/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 *
 *  error code 说明.
 *  -41001: encodingAesKey 非法
 *  -41003: aes 解密失败
 *  -41004: 解密后得到的buffer非法
 *  -41005: base64加密失败
 *  -41016: base64解密失败
 */
class WXBizDataCrypt
{
    private $_appid;
    private $_sessionKey;

    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;

    /**
     * 构造函数
     *
     * @param string $sessionKey 用户在小程序登录后获取的会话密钥
     */
    public function __construct($_appid, $session_key)
    {
        $this->_appid = $_appid;
        $this->_sessionKey = $session_key;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文
     *
     * @param string $encrypted_data 加密的用户数据
     * @param string $iv 与用户数据一同返回的初始向量
     * @param string $data 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($encrypted_data, $iv, &$data)
    {
        if (strlen($this->_sessionKey) != 24) {
            return WXBizDataCryptErrorCode::$IllegalAesKey;
        }

        if (strlen($iv) != 24) {
            return WXBizDataCryptErrorCode::$IllegalIv;
        }

        $aes_key = base64_decode($this->_sessionKey);
        $aes_iv = base64_decode($iv);
        $aes_cipher = base64_decode($encrypted_data);
        $result = openssl_decrypt($aes_cipher, 'AES-128-CBC', $aes_key, 1, $aes_iv);
        $obj = json_decode($result);

        if ($obj == null || $obj->watermark->appid != $this->_appid) {
            return WXBizDataCryptErrorCode::$IllegalBuffer;
        }

        $data = $result;
        return WXBizDataCryptErrorCode::$OK;
    }
}
