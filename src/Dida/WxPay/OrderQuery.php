<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\WxPay;

class OrderQuery
{
    const VERSION = '20180611';

    const APIURL = "https://api.mch.weixin.qq.com/pay/orderquery";

    static $valid_fields = [
        'appid'  => 'required',
        'mch_id' => 'required',

        'nonce_str' => 'auto',

        'transaction_id' => '',
        'out_trade_no'   => '',

        'sign_type' => 'calc',
        'sign'      => 'calc',
    ];


    public function query(array $biz, array $conf)
    {
        $data = [
            "appid"     => $conf["app_id"],
            "mch_id"    => $conf["mch_id"],
            'sign_type' => 'MD5',
            "nonce_str" => Common::randomString(10),
        ];

        $data = array_merge_recursive($biz, $data);

        $data["sign"] = Common::sign($data, $conf["sign_key"]);

        $xml = Common::arrayToXml($data);

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
        $verify = Common::verify($rcv, $conf['sign_key']);
        if ($verify === false) {
            return [1, "应答的签名校验失败", null];
        }

        return [0, null, $rcv];
    }


    public function checkFields(array $fields)
    {
        foreach ($fields as $name => $value) {
            if (!array_key_exists($name, self::$valid_fields)) {
                return [1, "发现无效字段 $name", null];
            }
        }

        foreach (self::$valid_fields as $name => $flag) {
            switch ($flag) {
                case 'required':
                case 'auto':
                    if (!Common::field_exists($name, $fields)) {
                        return [1, "必填参数 {$name} 未设置", null];
                    }
            }
        }

        return [0, null, null];
    }
}
