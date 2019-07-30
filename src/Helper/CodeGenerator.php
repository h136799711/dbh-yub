<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | ©2018 California State Lottery All rights reserved.
// +----------------------------------------------------------------------
// | Author: Smith Jack
// +----------------------------------------------------------------------

namespace App\Helper;


use by\component\string_extend\helper\StringHelper;

class CodeGenerator
{
    /**
     * 长度取决uid的大小,  至少大于24
     * @param $uid
     * @param int $itemId
     * @return string
     */
    public static function orderCode($uid, $itemId = 0) {
        $md5Item = substr(md5($itemId), 0, 6);
        // 24+ 长度
        return strtoupper(date("YmdHis").StringHelper::intTo62($uid).StringHelper::randNumbers(4).$md5Item);
    }

    public static function payCode($uid, $orderId = 0) {
        $md5Item = substr(md5($orderId), 0, 6);
        // 24+ 长度
        return strtoupper(date("YmdHis").StringHelper::intTo62($uid).StringHelper::randNumbers(4).$md5Item);
    }

    public static function payCodeByClientId($clientId) {
        $mt = explode(" ", microtime());
        $time = str_pad(intval($mt[0] * 1000000), 6, "0", STR_PAD_RIGHT).$mt[1];

        $md5 = substr(md5($clientId), 0, 8);

        // 16 + 8 + 6 = 30位长度
        return strtoupper($time.$md5.StringHelper::randNumbers(6));
    }

}
