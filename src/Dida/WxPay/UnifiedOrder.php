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
    const VERSION = '20180505';

    const APIURL = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    static $fieldset = [
        'appid'        => '',
        'mch_id'       => '',
        'trade_type'   => '',
        'out_trade_no' => '',
        'total_fee'    => '',
        'body'         => '',
        'notify_url'   => '',

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


    public function apply(array $data)
    {
        $temp = [];

        foreach ($data as $key => $value) {
            if (array_key_exists($key, self::$fieldset)) {
                $temp[$key] = $value;
            }
        }

        if (!isset($temp['spbill_create_ip']) || !$temp['spbill_create_ip']) {
            $temp['spbill_create_ip'] = Common::clientIP();
        }
        if (!isset($temp['nonce_str']) || !$temp['nonce_str']) {
            $temp['nonce_str'] = Common::randomString(10);
        }

        list($code, $msg) = $this->check($temp);

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


    protected function check(array $data)
    {
        foreach (self::$fieldset as $name => $flag) {
            switch ($flag) {
                case '':
                case 'auto':
                    if (!Common::field_exists($name, $data)) {
                        return [1, "缺少必填参数 {$name}"];
                    }
            }
        }

        if ($data["trade_type"] === "JSAPI") {
            if (!Common::field_exists('openid', $data)) {
                return [2, "JSAPI类型交易必填 openid"];
            }
        }
        if ($data['trade_type'] === 'NATIVE') {
            if (!Common::field_exists('product_id', $data)) {
                return [2, "微信扫码支付必填 product_id"];
            }
        }

        return [0, null];
    }
}
