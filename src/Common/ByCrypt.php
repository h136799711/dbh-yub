<?php


namespace App\Common;


use by\component\encrypt\des\Des;

class ByCrypt
{
    public static function desEncode($content) {
        return Des::encode($content, ByEnv::get('APP_SECRET'));
    }

    public static function desDecode($content) {
        return Des::decode($content, ByEnv::get('APP_SECRET'));
    }

    /**
     * 隐藏关键信息
     * @param $str
     * @param int $firstLen
     * @param int $lastLen
     * @param string $replaceChar
     * @param int $replaceCount
     * @return mixed
     */
    public static function hideSensitive($str, $firstLen = 3, $lastLen = 4, $replaceCount = 4, $replaceChar = '*') {
        if (strlen($str) > $firstLen + $lastLen) {
            return substr($str, 0, $firstLen). str_repeat($replaceChar, $replaceCount).substr($str, -$lastLen);
        }
        return $str;
    }
}
