<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2018 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2018-01-19 11:12
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\user\helper;


use by\api\helper\ApiConfigHelper;
use by\component\powersystem\entity\AuthGroupAccessEntity;
use by\component\powersystem\logic\AuthGroupAccessLogic;

class DefaultGroupSetHelper
{

    /**
     * 设置用户的默认用户组
     * @param $projectId
     * @param $uid
     * @param string $defaultGroupId 默认用户组
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setUid($projectId, $uid, $defaultGroupId = '')
    {
        if (empty($defaultGroupId)) {
            $defaultGroupId = ApiConfigHelper::getConfig($projectId, 'sys_default_user_group');
        }
        if (!empty($defaultGroupId) && intval($uid) > 0) {
            $authGroupAccess = new AuthGroupAccessEntity();
            $authGroupAccess->setGroupId($defaultGroupId);
            $authGroupAccess->setUid($uid);
            (new AuthGroupAccessLogic())->add($authGroupAccess);
        }
    }
}