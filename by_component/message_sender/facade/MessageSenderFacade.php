<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-14 17:01
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\message_sender\facade;


use by\component\message_sender\constants\MessageSenderTypeEnum;
use by\component\message_sender\impl\AlertMessageSender;
use by\component\message_sender\impl\AliyunSmsSender;
use by\component\message_sender\impl\JuheMessageSender;
use by\component\message_sender\impl\PushUMengMessageSender;
use by\component\message_sender\impl\QcloudMessageSender;
use by\component\message_sender\interfaces\SenderInterface;
use by\infrastructure\helper\CallResultHelper;

class MessageSenderFacade
{

    /**
     * @var SenderInterface
     */
    private static $sender;

    public static function create($type, $data)
    {
        switch ($type) {
            case MessageSenderTypeEnum::SMS_QCLOUD:
                self::$sender = new QcloudMessageSender($data);
                break;
            case MessageSenderTypeEnum::SMS_JUHE:
                self::$sender = new JuheMessageSender($data);
                break;
            case MessageSenderTypeEnum::PUSH_UMENG:
                self::$sender = new PushUMengMessageSender($data);
                break;
            case MessageSenderTypeEnum::SMS_Aliyun:
                self::$sender = new AliyunSmsSender($data);
                break;
            default:
                self::$sender = new AlertMessageSender($data);
                break;
        }
        return self::$sender;
    }

    public static function send()
    {
        if (self::$sender instanceof SenderInterface) {
            return self::$sender->send();
        }
        return CallResultHelper::fail('fail');
    }
}