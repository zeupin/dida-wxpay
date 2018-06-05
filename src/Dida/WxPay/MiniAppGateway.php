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


    public function config(array $conf)
    {
        $this->conf = $conf;
    }


    public function apply(array $data)
    {
        $preset = [
            "trade_type" => "JSAPI",
            "appid"      => $this->conf["app_id"],
            "mch_id"     => $this->conf["mch_id"],
            'notify_url' => $this->conf["notify_url"],
            'sign_type'  => 'MD5',
            'sign_key'   => $this->conf["mch_key"],
        ];

        $temp = array_merge($data, $preset);

        $uniorder = new UnifiedOrder;

        $result = $uniorder->apply($temp);

        return $result;
    }


    public function receivedNotify($xml, $mch_key)
    {
        $msg = Common::xmlToArray($xml);

        if ($msg === false) {
            \Dida\Log\Log::write("无效的微信支付通知xml");
            \Dida\Log\Log::write($xml);
            return [1, "支付通知不是一个有效的xml", null];
        }

        $result = Common::verify($msg, $mch_key);



        if ($result) {
            return [0, null, $msg];
        } else {
            return [1, "验证支付结果通知的签名失败，此消息不被信任", $msg];
        }
    }
}
