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


    public function __construct()
    {
        libxml_disable_entity_loader(true);
    }


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
        ksort($data);

        unset($data["sign"]);

        $temp = [];

        foreach ($data as $k => $v) {
            if ($v) {
                $temp[] = "$k=$v";
            }
        }

        $temp[] = "key={$sign_key}";

        $raw = implode('&', $temp);

        $hash = md5($raw);

        $hash = strtoupper($hash);

        return $hash;
    }


    public static function verify(array $msg, $key)
    {
        if (!isset($msg['sign'])) {
            return false;
        }

        $sign = $msg["sign"];

        $check = self::sign($msg, $key);

        return ($sign === $check);
    }


    public static function arrayToXml(array $array)
    {
        $output = [];

        $output[] = "<xml>";
        foreach ($array as $name => $value) {
            $output[] = "<{$name}><![CDATA[{$value}]]></{$name}>";
        }
        $output[] = "</xml>";

        return implode('', $output);
    }


    public static function xmlToArray($xml)
    {
        $temp = simplexml_load_string($xml, 'SimpleXMLElement');

        if ($temp === false) {
            return false;
        }

        $output = [];
        foreach ($temp as $key => $value) {
            $output[$key] = "$value";
        }

        return $output;
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


    public static function filterFields(array $field_list, array $input)
    {
        $output = [];

        foreach ($input as $name => $v) {
            if (array_key_exists($name, $field_list)) {
                $output[$name] = $v;
            }
        }

        return $output;
    }
}
