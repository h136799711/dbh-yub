<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-21 11:23
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace by\component\config\constants;


class SysConfigTypeEnum
{
    const DIGIT = 0;
    const CHAR = 1;
    const TEXT = 2;
    const ARRAY_TYPE = 3;
    const ENUM = 4;
    const PICTURE = 5;


    public static function isValid($type) {
        $validTypes = [SysConfigTypeEnum::DIGIT, SysConfigTypeEnum::CHAR, SysConfigTypeEnum::TEXT
            ,SysConfigTypeEnum::ARRAY_TYPE, SysConfigTypeEnum::ENUM, SysConfigTypeEnum::PICTURE
        ];
        return in_array($type, $validTypes);
    }
}