<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\WxPay;

class MiniAppGateway extends Gateway
{


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
}
