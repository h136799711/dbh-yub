<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2018 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2018-01-08 11:13
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\user\enum;


class UserLogType
{
    /**
     * 登录日志
     */
    const LOGIN = 1;

    /**
     * 经验值变动
     */
    const EXP = 2;

    /**
     * 管理应用
     */
    const Clients = 3;

    /**
     * 操作日志
     */
    const Operation = 4;

    public static function isLegal($type) {
        if (intval($type) <= 4 && intval($type) >= 1) {
            return true;
        }
        return false;
    }
}