<?php


namespace App\Controller;


use by\component\pay361\Pay361;
use by\component\pay361\PayInfo;
use Dbh\SfCoreBundle\Common\ByEnv;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;

class Pay361Controller extends BaseNeedLoginController
{
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
        return Pay361::getInstance()->setKey(ByEnv::get('PAY361_KEY'))->balance($shopPhone);
    }
}
