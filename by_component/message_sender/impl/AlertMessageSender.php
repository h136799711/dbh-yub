<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-14 16:59
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\message_sender\impl;


use by\component\message_sender\interfaces\SenderInterface;
use by\infrastructure\helper\CallResultHelper;

/**
 * Class AlertMessageSender
 * 仅返回配置消息
 * @package by\component\message_sender\impl
 */
class AlertMessageSender implements SenderInterface
{
    private $code;

    public function __construct($config)
    {
        if (array_key_exists('code', $config)) {
            $this->code = $config['code'];
        }
    }


    // construct

    public function send()
    {
        $msg = ["your code is %code%", ['%code%'=>$this->code]];
        return CallResultHelper::success($this->code, $msg);
    }
}