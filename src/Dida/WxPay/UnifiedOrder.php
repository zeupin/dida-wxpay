<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\WxPay;

class UnifiedOrder
{
    const VERSION = '20180612';

    const APIURL = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    static $valid_fields = [
        'appid'        => 'required',
        'mch_id'       => 'required',
        'trade_type'   => 'required',
        'out_trade_no' => 'required',
        'total_fee'    => 'required',
        'body'         => 'required',
        'notify_url'   => 'required',

        'nonce_str'        => 'auto',
        'spbill_create_ip' => 'auto',

        'product_id' => 'cond',
        'openid'     => 'cond',

        'device_info' => 'optional',
        'detail'      => 'optional',
        'attach'      => 'optional',
        'fee_type'    => 'optional',
        'time_start'  => 'optional',
        'time_expire' => 'optional',
        'goods_tag'   => 'optional',
        'limit_pay'   => 'optional',

        'sign_type' => 'calc',
        'sign'      => 'calc',
    ];


    public function prepay(array $data)
    {
        $temp = [];

        foreach ($data as $key => $value) {
            if (array_key_exists($key, self::$valid_fields)) {
                $temp[$key] = $value;
            }
        }

        if (!isset($temp['spbill_create_ip']) || !$temp['spbill_create_ip']) {
            $temp['spbill_create_ip'] = Common::clientIP();
        }
        if (!isset($temp['nonce_str']) || !$temp['nonce_str']) {
            $temp['nonce_str'] = Common::randomString(10);
        }

        list($code, $msg) = $this->checkFields($temp);

        if ($code !== 0) {
            return [1, $msg, null];
        }

        $temp["sign_type"] = "MD5";

        ksort($temp);
        $temp["sign"] = Common::sign($temp, $data['sign_key']);

        $xml = Common::arrayToXml($temp);
        \Dida\Log\Log::write("request=$xml");

        $curl = new \Dida\CURL\CURL();
        $result = $curl->request([
            'url'    => self::APIURL,
            'method' => 'POST',
            'data'   => $xml,
        ]);
        \Dida\Log\Log::write($result);

        list($code, $msg, $xml) = $result;

        if ($code !== 0) {
            return [$code, $msg, null];
        }

        $rcv = Common::xmlToArray($xml);
        $verify = Common::verify($rcv, $data['sign_key']);
        if ($verify === false) {
            return [1, "应答的签名校验失败", null];
        }

        if ($rcv["result_code"] == "FAIL") {
            return [$rcv["err_code"], $rcv["err_code_des"], null];
        }

        $appId = $data['appid'];
        $timeStamp = time();
        $nonceStr = Common::randomString(10);
        $package = "prepay_id={$rcv["prepay_id"]}";
        $pay = [
            'appId'     => $appId,
            'timeStamp' => "$timeStamp",
            'nonceStr'  => $nonceStr,
            'package'   => $package,
            'signType'  => 'MD5',
        ];
        ksort($pay);
        $paySign = Common::sign($pay, $data['sign_key']);
        $pay['paySign'] = $paySign;

        return [0, null, $pay];
    }


    protected function checkFields(array $data)
    {
        foreach (self::$valid_fields as $name => $flag) {
            switch ($flag) {
                case 'required':
                case 'auto':
                    if (!Common::field_exists($name, $data)) {
                        return [1, "必填参数 {$name} 未设置", null];
                    }
            }
        }

        if ($data["trade_type"] === "JSAPI") {
            if (!Common::field_exists('openid', $data)) {
                return [2, "JSAPI类型交易必填 openid", null];
            }
        }
        if ($data['trade_type'] === 'NATIVE') {
            if (!Common::field_exists('product_id', $data)) {
                return [2, "微信扫码支付必填 product_id", null];
            }
        }

        return [0, null, null];
    }
}
