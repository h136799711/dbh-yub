<?php


namespace by\component\wmpay;

class WmPaySignTool
{
    public static function verifySign($params, $base64Sign, $key) {
        $sign = self::sign($params, $key);
        return $sign === $base64Sign;
    }

    public static function sign($params, $key) {
        ksort($params, SORT_ASC);
        $str = '';
        foreach ($params as $k => $v) {
            if ($k == "signMethod" ||  $k == "signature") continue;

            if (is_null($v)) {
                $v = '';
            }
            if (strlen($v) == 0) continue;
            if (strlen($str) > 0) {
                $str .= '&';
            }
            $str .= $k.'='.$v;
        }
        var_dump($str);

        return base64_encode(md5($str.$key));
    }
}
