<?php

namespace App\Controller;

use by\component\pay361\Pay361;
use by\component\pay361\PayInfo;
use Dbh\SfCoreBundle\Common\ByEnv;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use Dbh\SfCoreBundle\Common\UserLogServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Symfony\Component\HttpKernel\KernelInterface;

class Pay361Controller extends BaseNeedLoginController
{
    protected $logService;

    public function __construct(
        UserLogServiceInterface $logService,
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, KernelInterface $kernel)
    {
        $this->logService = $logService;
        parent::__construct($userAccountService, $loginSession, $kernel);
    }

    /**
     * @param string $shopPhone
     * @param $bankCardNumber
     * @param $bankName
     * @param $registBankName
     * @param $money
     * @param $passagewayCode
     * @param $cardUserName
     * @param $shopSubNumber
     * @return \by\infrastructure\base\CallResult
     * @throws \by\component\exception\NotLoginException
     */
    public function pay($shopPhone, $bankCardNumber, $bankName, $registBankName,
        $money, $passagewayCode, $cardUserName, $shopSubNumber
    ) {
        $this->checkLogin();
        $payInfo = new PayInfo();
        $payInfo->setShopPhone($shopPhone);
        $payInfo->setBankCardNumber($bankCardNumber);
        $payInfo->setBankName($bankName);
        $payInfo->setRegistBankName($registBankName);
        $payInfo->setMoney(strval($money));
        $payInfo->setPassagewayCode($passagewayCode);
        $payInfo->setCardUserName($cardUserName);
        $payInfo->setShopSubNumber($shopSubNumber);
        $payInfo->setNotifyUrl(ByEnv::get('PAY361_NOTIFY_URL'));

        $note = '用户'.$this->getUid().'发起了代付请求'.json_encode($payInfo->toArray());
        $this->logUserAction($this->logService, $note);
        return Pay361::getInstance()->setKey(ByEnv::get('PAY361_KEY'))->pay($payInfo);
    }

    /**
     * @param string $shopPhone
     * @param string $shopSubNumber
     * @return \by\infrastructure\base\CallResult
     * @throws \by\component\exception\NotLoginException
     */
    public function orderQuery($shopPhone = '', $shopSubNumber = '') {
        $this->checkLogin();
        return Pay361::getInstance()->setKey(ByEnv::get('PAY361_KEY'))->orderQuery($shopPhone, $shopSubNumber);
    }

    /**
     * @param string $shopPhone
     * @return \by\infrastructure\base\CallResult
     * @throws \by\component\exception\NotLoginException
     */
    public function balance($shopPhone = '') {
        $this->checkLogin();
        $note = '用户'.$this->getUid().'查看了余额';
        $this->logUserAction($this->logService, $note);
        return Pay361::getInstance()->setKey(ByEnv::get('PAY361_KEY'))->balance($shopPhone);
    }
}
