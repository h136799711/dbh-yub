<?php


namespace by\component\pay361;


class SignTool
{
    public static function sign($params, $key) {
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
        return strtolower(hash('sha256', $str.$key));
    }
}
