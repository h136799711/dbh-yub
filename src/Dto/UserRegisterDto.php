<?php
/**
 * 注意：本内容仅限于博也公司内部传阅,禁止外泄以及用于其他的商业目的
 * @author    hebidu<346551990@qq.com>
 * @copyright 2017 www.itboye.com Boye Inc. All rights reserved.
 * @link      http://www.itboye.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * Revision History Version
 ********1.0.0********************
 * file created @ 2017-12-18 13:51
 *********************************
 ********1.0.1********************
 *
 *********************************
 */

namespace App\Dto;

use by\infrastructure\base\BaseObject;
use by\infrastructure\helper\Object2DataArrayHelper;
use by\infrastructure\interfaces\ObjectToArrayInterface;

/**
 * Class UserInfoEntity
 * 用户注册信息
 */
class UserRegisterDto extends BaseObject implements ObjectToArrayInterface
{
    private $mobile;
    private $countryNo;
    private $username;
    private $password;

    public function __construct()
    {
        parent::__construct();
    }

    public function toArray()
    {
        return Object2DataArrayHelper::getDataArrayFrom($this);
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getCountryNo()
    {
        return $this->countryNo;
    }

    /**
     * @param mixed $countryNo
     */
    public function setCountryNo($countryNo): void
    {
        $this->countryNo = $countryNo;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

}