<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\WxPay;

class MiniAppGateway
{
    protected $conf = [];


    public function __construct(array $conf = [])
    {
        $this->conf = $conf;
    }


    public function config(array $conf)
    {
        $this->conf = $conf;
    }


    public function prepay(array $data)
    {
        $preset = [
            "trade_type" => "JSAPI",
            "appid"      => $this->conf["app_id"],
            "mch_id"     => $this->conf["mch_id"],
            'notify_url' => $this->conf["notify_url"],
            'sign_type'  => 'MD5',
            'sign_key'   => $this->conf["mch_key"],
        ];

        $input = array_merge($data, $preset);

        $uniorder = new UnifiedOrder;

        $result = $uniorder->prepay($input);

        return $result;
    }


    public function query($data)
    {
        $conf = [
            "app_id"    => $this->conf["app_id"],
            "mch_id"    => $this->conf["mch_id"],
            'sign_type' => 'MD5',
            'sign_key'  => $this->conf["mch_key"],
        ];

        $orderquery = new OrderQuery;

        $result = $orderquery->query($data, $conf);

        return $result;
    }


    public function queryByOutTradeNo($out_trade_no)
    {
        $data = [
            "out_trade_no" => $out_trade_no,
        ];

        return $this->query($data);
    }


    public function queryByTransactionId($transaction_id)
    {
        $data = [
            "transaction_id" => $transaction_id,
        ];

        return $this->query($data);
    }


    public function parseNotify($xml, $mch_key)
    {
        $notify = Common::xmlToArray($xml);

        if ($notify === false) {
            \Dida\Log\Log::write("无效的微信支付通知xml");
            \Dida\Log\Log::write($xml);
            return [1, "支付通知不是一个有效的xml", null];
        }

        $result = Common::verify($notify, $mch_key);



        if ($result) {
            return [0, null, $notify];
        } else {
            return [1, "验证支付结果通知的签名失败，此消息不被信任", $notify];
        }
    }


    public function notifyOK()
    {
        return <<<TEXT
<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>
TEXT;
    }


    public function notifyFail($errinfo)
    {
        $response = [
            "return_code" => "FAIL",
            "return_msg"  => $errinfo
        ];

        return Common::arrayToXml($response);
    }
}
