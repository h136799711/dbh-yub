<?php


namespace by\component\fyt;


use by\component\encrypt\rsa\Rsa;

class FytSignTool
{
    public static function verifySign($params, $base64Sign, $key) {
        ksort($params, SORT_ASC);
        $str = '';
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) continue;
            if (is_null($v)) {
                $v = '';
            }
            if (strlen($str) > 0) {
                $str .= '&';
            }
            $str .= $k.'='.$v;
        }
        return Rsa::verifySign($str, $base64Sign, $key);
    }

    public static function sign($params, $key) {
        ksort($params, SORT_ASC);
        $str = '';
        foreach ($params as $k => $v) {
            if (strlen($v) == 0) continue;
            if (is_null($v)) {
                $v = '';
            }
            if (strlen($str) > 0) {
                $str .= '&';
            }
            $str .= $k.'='.$v;
        }

        return Rsa::sign($str, $key);
    }
}
