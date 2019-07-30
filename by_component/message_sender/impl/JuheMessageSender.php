<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-14 16:58
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\message_sender\impl;


use by\component\config\ConfigStorageInterface;
use by\component\message_sender\interfaces\SenderInterface;
use by\component\sms\juhe\SmsManage;
use by\infrastructure\helper\CallResultHelper;

class JuheMessageSender implements SenderInterface
{
    private $sendUrl;
    private $config = [
        'key'=>'',
        'mobile'=>'',
        'tpl_id'=>'',
        'tpl_value'=>'',
    ];

    /**
     * JuheMessageSender constructor.
     * @param $data
     * @param ConfigStorageInterface $configStorage
     */
    public function __construct($data)
    {
        $this->sendUrl = "http://v.juhe.cn/sms/send";
        if (is_array($data) && array_key_exists('tpl_id', $data) && array_key_exists('app_key', $data)) {
            $this->config['tpl_id'] = $data['tpl_id'];
            $this->config['key'] = $data['app_key'];
        } else {
            throw new \InvalidArgumentException(('juhe sms config error'));
        }
        $this->config['mobile'] = $data['mobile'];
        unset($data['scene']);
        unset($data['project_id']);
        unset($data['mobile']);
        $tplValue = "";
        foreach ($data as $key => $vo)
        {
            if (strlen($tplValue) > 0) {
                $tplValue .= "&";
            }
            $tplValue .= "#".$key."#=".(strval($vo));
        }
        $this->config['tpl_value'] = urlencode($tplValue);
    }

    public function send()
    {
        $result = SmsManage::instance($this->config, $this->sendUrl)->send();
        if ($result->isSuccess()) {
            return CallResultHelper::success(('sms send success'));
        } else {
            return CallResultHelper::fail($result->getMsg(), $result->getData());
        }
    }

}