<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-14 16:52
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\message_sender\constants;

/**
 * Class CodeSendTypeEnum
 *
 * @package by\component\message_sender\constants
 */
class MessageSenderTypeEnum
{
    /**
     *  只是返回
     */
    const ALERT = "alert";

    /**
     * 短信息 - 腾讯云
     */
    const SMS_QCLOUD = "sms_qcloud";

    /**
     * 短信息 - 聚合
     */
    const SMS_JUHE = "sms_juhe";

    /**
     * 推送信息 - 友盟
     */
    const PUSH_UMENG = "push_umeng";

    /**
     * 阿里云
     */
    const SMS_Aliyun = "sms_aliyun";
}