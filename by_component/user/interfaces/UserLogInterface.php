<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2018 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2018-01-08 11:11
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\user\logic;


use by\component\tp5\logic\BaseLogic;
use by\component\user\entity\UserLogEntity;

interface  UserLogInterface
{
    /**
     * 用户登录日志记录
     * @param $uid
     * @param $ip
     * @param $deviceType
     * @param string $ua
     * @return bool|int
     */
    public function login($uid, $ip, $deviceType, $ua = '');
}