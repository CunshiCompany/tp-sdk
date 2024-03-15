<?php

namespace Cunshi\TpSdk\wechat;

use Cunshi\TpSdk\common\AES;
use Cunshi\TpSdk\common\Func;
use Cunshi\TpSdk\common\Http;
use Cunshi\TpSdk\common\Random;
use Cunshi\TpSdk\common\Sign;

class WechatRefund
{

    /**
     * 原因：系统超时
     * 方案：请不要更换商户退款单号，请使用相同参数再次调用API
     */
    public static $SYSTEM_ERROR = 'SYSTEM_ERROR';

    /**
     * 原因：并发情况下，业务被拒绝，商户重试即可解决
     * 方案：请不要更换商户退款单号，请使用相同参数再次调用API
     */
    public static $BIZERR_NEED_RETRY = 'BIZERR_NEED_RETRY';

    /**
     * 原因：订单已经超过可退款的最大期限(支付后一年内可退款)
     * 方案：请选择其他方式自行退款
     */
    public static $TRADE_OVERDUE = 'TRADE_OVERDUE';

    /**
     * 原因：申请退款业务发生错误
     * 方案：该错误都会返回具体的错误原因，请根据实际返回做相应处理
     */
    public static $ERROR = 'ERROR';

    /**
     * 原因：用户账号注销
     * 方案：此状态代表退款申请失败，商户可自行处理退款
     */
    public static $USER_ACCOUNT_ABNORMAL = 'USER_ACCOUNT_ABNORMAL';

    /**
     * 原因：连续错误请求数过多被系统短暂屏蔽
     * 方案：请检查业务是否正常，确认业务正常后请在1分钟后再来重试
     */
    public static $INVALID_REQ_TOO_MUCH = 'INVALID_REQ_TOO_MUCH';

    /**
     * 原因：商户可用退款余额不足
     * 方案：此状态代表退款申请失败，商户可根据具体的错误提示做相应的处理
     */
    public static $NOTENOUGH = 'NOTENOUGH';

    /**
     * 原因：请求参数未按指引进行填写
     * 方案：请求参数错误，检查原交易号是否存在或发起支付交易接口返回失败
     */
    public static $INVALID_TRANSACTIONID = 'INVALID_TRANSACTIONID';

    /**
     * 原因：请求参数未按指引进行填写
     * 方案：请求参数错误，请重新检查再调用退款申请
     */
    public static $PARAM_ERROR = 'PARAM_ERROR';

    /**
     * 原因：参数中缺少APPID
     * 方案：请检查APPID是否正确
     */
    public static $APPID_NOT_EXIST = 'APPID_NOT_EXIST';

    /**
     * 原因：参数中缺少MCHID
     * 方案：请检查MCHID是否正确
     */
    public static $MCHID_NOT_EXIST = 'MCHID_NOT_EXIST';

    /**
     * 原因：缺少有效的订单号
     * 方案：请检查你的订单号是否正确且是否已支付，未支付的订单不能发起退款
     */
    public static $ORDERNOTEXIST = 'ORDERNOTEXIST';

    /**
     * 原因：未使用post传递参数
     * 方案：请检查请求参数是否通过post方法提交
     */
    public static $REQUIRE_POST_METHOD = 'REQUIRE_POST_METHOD';

    /**
     * 原因：参数签名结果不正确
     * 方案：请检查签名参数和方法是否都符合签名算法要求
     */
    public static $SIGNERROR = 'SIGNERROR';

    /**
     * 原因：XML格式错误
     * 方案：请检查XML参数格式是否正确
     */
    public static $XML_FORMAT_ERROR = 'XML_FORMAT_ERROR';

    /**
     * 原因：1个月之前的订单申请退款有频率限制
     * 方案：该笔退款未受理，请降低频率后重试
     */
    public static $FREQUENCY_LIMITED = 'FREQUENCY_LIMITED';

    /**
     * 原因：请求ip异常
     * 方案：如果是动态ip，请登录商户平台后台关闭ip安全配置；如果是静态ip，请确认商户平台配置的请求ip 在不在配的ip列表里
     */
    public static $NOAUTH = 'NOAUTH';

    /**
     * 原因：请检查证书是否正确，证书是否过期或作废
     * 方案：请检查证书是否正确，证书是否过期或作废
     */
    public static $CERT_ERROR = 'CERT_ERROR';

    /**
     * 原因：订单金额或退款金额与之前请求不一致，请核实后再试
     * 方案：订单金额或退款金额与之前请求不一致，请核实后再试
     */
    public static $REFUND_FEE_MISMATCH = 'REFUND_FEE_MISMATCH';

    /**
     * 原因：此状态代表退款申请失败，商户可根据具体的错误提示做相应的处理
     * 方案：此状态代表退款申请失败，商户可根据具体的错误提示做相应的处理
     */
    public static $INVALID_REQUEST = 'INVALID_REQUEST';

    /**
     * 原因：订单处理中，暂时无法退款，请稍后再试
     * 方案：订单处理中，暂时无法退款，请稍后再试
     */
    public static $ORDER_NOT_READY = 'ORDER_NOT_READY';

    public static $FAIL = 'FAIL';

    private $_appId;      // 小程序 appid
    private $_mchId;      // 服务商商户号
    private $_subMchId;   // 子商户号
    private $_notifyUrl;  // 退款成功回调地址
    private $_mchKey;     // 商户号密钥

    private $_mchCertPath;  // 证书路径
    private $_mchKeyPath;   // 证书 key 路径

    public function __construct($app_Id, $mch_Id, $mch_Key, $mch_CertPath, $mch_KeyPath)
    {
        $this->_appId       = $app_Id;
        $this->_mchId       = $mch_Id;
        $this->_mchKey      = $mch_Key;
        $this->_mchCertPath = $mch_CertPath;
        $this->_mchKeyPath  = $mch_KeyPath;
    }

    public function setSubMchId($mch_id)
    {
        $this->_subMchId = $mch_id;
        return $this;
    }

    public function setNotifyUrl($url)
    {
        $this->_notifyUrl = $url;
        return $this;
    }

    /**
     * 申请退款
     *
     * @param  $out_trade_no  订单单号
     * @param  $out_refund_no 退款单号
     * @param  $total_fee     订单总金额
     * @param  $refund_fee    退款金额
     * @param  $refund_desc   退款描述
     * @return string
     */
    public function refundOrder($out_trade_no, $out_refund_no, $total_fee, $refund_fee, $refund_desc)
    {
        $params = [
            'appid'         => $this->_appId,
            'mch_id'        => $this->_mchId,
            'sub_mch_id'    => $this->_subMchId,
            'nonce_str'     => Random::alnum(32),
            'out_trade_no'  => $out_trade_no,       // 商户系统内部订单号
            'out_refund_no' => $out_refund_no,      // 商户系统内部退款单号
            'total_fee'     => $total_fee,          // 订单总金额，单位为分
            'refund_fee'    => $refund_fee,         // 退款总金额，单位为分
            'refund_desc'   => $refund_desc,        // 退款原因
            'notify_url'    => $this->_notifyUrl,   // 通知地址
        ];

        $params['sign'] = Sign::getSign($this->_mchKey, $params);
        $result         = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/secapi/pay/refund',
                Func::array_to_xml($params),
                [
                    CURLOPT_SSLKEY  => $this->_mchKeyPath,
                    CURLOPT_SSLCERT => $this->_mchCertPath,
                ]
            )
        );

        return $this->_assembleResult($result);
    }

    private function _assembleResult($result)
    {
        if (!$result) {
            return [
                'status'    => self::$FAIL,
                'fail_code' => self::$ERROR,
                'fail_msg'  => '微信退款请求失败'
            ];
        }

        if ($result['return_code'] == 'FAIL') {
            return [
                'status'    => self::$FAIL,
                'fail_code' => $result['return_code'],
                'fail_msg'  => $result['return_msg']
            ];
        }

        if ($result['result_code'] == 'FAIL') {
            if ($result['err_code'] == self::$NOTENOUGH) {
                return [
                    'status'    => self::$FAIL,
                    'fail_code' => $result['err_code'],
                    'fail_msg'  => '余额不足，将尽快处理'
                ];
            }

            if (in_array($result['err_code'], [
                self::$TRADE_OVERDUE,
                self::$USER_ACCOUNT_ABNORMAL,
                self::$INVALID_TRANSACTIONID,
                self::$APPID_NOT_EXIST,
                self::$MCHID_NOT_EXIST,
                self::$ORDERNOTEXIST,
                self::$FREQUENCY_LIMITED,
                self::$REFUND_FEE_MISMATCH,
                self::$INVALID_REQUEST
            ])) {
                return [
                    'status'    => self::$FAIL,
                    'fail_code' => $result['err_code'],
                    'fail_msg'  => $result['err_code_des']
                ];
            } else {
                return [
                    'status'    => self::$FAIL,
                    'fail_code' => $result['err_code'],
                    'fail_msg'  => $result['err_code_des']
                ];
            }
        }

        return [];
    }

    /**
     * 退款查询
     * @param $out_refund_no 系统退款单号
     * # todo: 没改
     */
    public function queryRefundOrder($out_refund_no)
    {
        $params = [
            'appid'         => $this->_appId,
            'mch_id'        => $this->_mchId,
            'sub_mch_id'    => $this->_subMchId,
            'nonce_str'     => Random::alnum(32),
            'out_refund_no' => $out_refund_no,      // 商户系统内部退款单号
        ];

        $params['sign'] = Sign::getSign($this->_mchKey, $params);
        $result         = Func::xml_to_array(
            Http::post(
                'https://api.mch.weixin.qq.com/pay/refundquery',
                Func::array_to_xml($params)
            )
        );
        if ($this->checkNotifySign($result)) {
            if ($result['return_code'] == 'FAIL') {
                $res = ['status' => false, 'msg' => $result['return_msg']];
            } else if ($result['result_code'] == 'FAIL') {
                $res = ['status' => false, 'msg' => $result['err_code_des']];
            } else if ($result['refund_status_0'] == 'CHANGE') {
                $res = ['status' => false, 'msg' => $result['err_code_des'] ?? '退款异常'];
            } else if ($result['refund_status_0'] == 'PROCESSING') {
                $res = ['status' => false, 'msg' => $result['err_code_des'] ?? '退款处理中'];
            } else if ($result['refund_status_0'] != 'SUCCESS') {
                $res = ['status' => false, 'msg' => $result['err_code_des'] ?? '退款失败'];
            } else {
                $res = [
                    'status' => true,
                    'data'   => [
                        'refund_id'           => $result['refund_id_0'],
                        'out_refund_no'       => $result['out_refund_no_0'],
                        'refund_fee'          => $result['refund_fee_0'],
                        'refund_status'       => $result['refund_status_0'],
                        'refund_success_time' => $result['refund_success_time_0'],
                    ]
                ];
            }
        } else {
            $res = [
                'status' => false,
                //                'msg' => config('error.not_found_order')
            ];
        }
        return $res;
    }

    /**
     * Decrypt message.
     *
     * @param string $key
     * @return string|null
     **/
    public function decryptMessage(string $message)
    {
        if (!$message) return null;

        return AES::decrypt(base64_decode($message, true), md5($this->_mchKey), '', OPENSSL_RAW_DATA, 'AES-256-ECB');
    }

    /**
     * 异步签名验证
     *
     * @param  $data
     * @return bool
     */
    public function checkNotifySign($data)
    {
        if (!$data) return false;

        $sign = $data['sign'];
        unset($data['sign']);

        return $sign == Sign::getSign($this->_mchKey, $data) ? true : false;
    }
}
