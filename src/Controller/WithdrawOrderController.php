<?php


namespace App\Controller;


use App\Entity\Pay361WithdrawOrder;
use App\Helper\CodeGenerator;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use by\infrastructure\helper\Object2DataArrayHelper;
use Dbh\SfCoreBundle\Common\ByEnv;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Symfony\Component\HttpKernel\KernelInterface;

class WithdrawOrderController extends BaseNeedLoginController
{
    protected $pay361WithdrawOrderService;

    public function __construct(
        Pay361WithdrawOrderServiceInterface $pay361WithdrawOrderService,
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, KernelInterface $kernel)
    {
        $this->pay361WithdrawOrderService = $pay361WithdrawOrderService;
        parent::__construct($userAccountService, $loginSession, $kernel);
    }

    public function create($shopPhone, $bankCardNumber, $bankName, $registBank, $registBankName,
                           $cityNumber, $money, $passagewayCode, $cardUserName, $certNumber
    ) {
        $entity = new Pay361WithdrawOrder();
        $entity->setShopPhone($shopPhone);
        $entity->setBankCardNumber($bankCardNumber);
        $entity->setBankName($bankName);
        $entity->setRegistBankName($registBankName);
        $entity->setRegistBank($registBank);
        $entity->setCityNumber($cityNumber);
        $entity->setMoney($money);
        $entity->setPassagewayCode($passagewayCode);
        $entity->setCardUserName($cardUserName);
        $entity->setCertNumber($certNumber);

        $entity->setOrderNo((CodeGenerator::payCodeByClientId($shopPhone.$passagewayCode.$money)));
        $entity->setNotifyUrl(ByEnv::get("PAY361_NOTIFY_URL"));
        $data = Object2DataArrayHelper::getDataArrayFrom($entity);
        ksort($data);
        $sign = md5(json_encode($data, JSON_UNESCAPED_UNICODE));
        $entity->setSign($sign);

        return $this->pay361WithdrawOrderService->add($entity);
    }
}
