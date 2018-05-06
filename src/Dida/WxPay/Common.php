<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\WxPay;

class Common
{


    public static function randomString($num = 32, $set = null)
    {
        if (!$set) {
            $set = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }
        $len = strlen($set);
        $r = [];
        for ($i = 0; $i < $num; $i++) {
            $r[] = substr($set, mt_rand(0, $len - 1), 1);
        }
        return implode('', $r);
    }


    public static function sign(array $data, $sign_key)
    {
        $temp = [];

        foreach ($data as $k => $v) {
            if ($v) {
                $temp[$k] = $v;
            }
        }

        unset($temp["sign"]);

        ksort($temp);

        $temp["key"] = $sign_key;

        $raw = http_build_query($temp);

        var_dump($raw);

        $hash = md5($raw);

        $hash = strtoupper($hash);

        return $hash;
    }


    public static function toXml(array $data)
    {
        $output = [];

        $output[] = "<xml>";
        foreach ($data as $name => $value) {
            $output[] = "<$name>" . urlencode($value) . "</$name>";
        }
        $output[] = "</xml>";

        return implode('', $output);
    }


    public static function clientIP()
    {
        $ip = false;

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_X_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
        } elseif (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        return $ip;
    }


    public static function field_exists($field, array $data)
    {
        return (isset($data[$field]) && $data[$field]);
    }
}
