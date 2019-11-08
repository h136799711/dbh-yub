<?php


namespace by\component\fyt;


use by\component\encrypt\rsa\Rsa;

class FytSignTool
{
    public static function verifySign($data, $base64Sign, $key) {
        return Rsa::verifySign($data, $base64Sign, $key);
    }

    public static function sign($params, $key) {
        ksort($params, SORT_ASC);
        $str = '';
        foreach ($params as $k => $v) {
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
