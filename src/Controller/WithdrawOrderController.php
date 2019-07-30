<?php


namespace App\Controller;


use App\Entity\Pay361WithdrawOrder;
use App\Helper\CodeGenerator;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use by\component\paging\vo\PagingParams;
use by\component\pay361\Pay361;
use by\component\pay361\PayInfo;
use by\infrastructure\helper\Object2DataArrayHelper;
use Dbh\SfCoreBundle\Common\ByEnv;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use Dbh\SfCoreBundle\Common\UserLogServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Symfony\Component\HttpKernel\KernelInterface;

class WithdrawOrderController extends BaseNeedLoginController
{
    protected $pay361WithdrawOrderService;
    protected $userLogService;

    public function __construct(
        UserLogServiceInterface $userLogService,
        Pay361WithdrawOrderServiceInterface $pay361WithdrawOrderService,
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, KernelInterface $kernel)
    {
        $this->userLogService = $userLogService;
        $this->pay361WithdrawOrderService = $pay361WithdrawOrderService;
        parent::__construct($userAccountService, $loginSession, $kernel);
    }

    /**
     * @param $shopPhone
     * @param $bankCardNumber
     * @param $bankName
     * @param $registBank
     * @param $registBankName
     * @param $cityNumber
     * @param $money
     * @param $passagewayCode
     * @param $cardUserName
     * @param $certNumber
     * @return \by\infrastructure\base\CallResult
     * @throws \ReflectionException
     * @throws \by\component\exception\NotLoginException
     */
    public function create($shopPhone, $bankCardNumber, $bankName, $registBank, $registBankName,
                           $cityNumber, $money, $passagewayCode, $cardUserName, $certNumber
    ) {
        $this->checkLogin();
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

        $this->pay361WithdrawOrderService->add($entity);

        // 发起代付申请
        $payInfo = new PayInfo();
        $payInfo->setShopPhone($entity->getShopPhone());
        $payInfo->setBankCardNumber($entity->getBankCardNumber());
        $payInfo->setBankName($entity->getBankName());
        $payInfo->setRegistBankName($entity->getRegistBankName());
        $payInfo->setMoney($entity->getMoney());
        $payInfo->setPassagewayCode($entity->getPassagewayCode());
        $payInfo->setCardUserName($entity->getCardUserName());
        $payInfo->setShopSubNumber($entity->getOrderNo());
        $payInfo->setNotifyUrl($entity->getNotifyUrl());

        $note = '用户'.$this->getUid().'发起了代付请求'.json_encode($payInfo->toArray());
        $this->logUserAction($this->userLogService, $note);
        return Pay361::getInstance()->pay($payInfo);
    }

    public function query(PagingParams $pagingParams, $startTime, $endTime, $minMoney = 0, $maxMoney = 0) {
        $map = [];
        $minMoney = intval($minMoney);
        $maxMoney = intval($maxMoney);
        if ($maxMoney < $minMoney) {
            $tmp = $maxMoney;
            $maxMoney = $minMoney;
            $minMoney = $tmp;
        }
        if ($minMoney > 0) {
            $map['money'] = ['gt', $minMoney];
        }

        if ($maxMoney > 0) {
            if (array_key_exists('money', $map)) {
                $map['money'] = ['gt', $minMoney, 'lt', $maxMoney];
            } else {
                $map['money'] = ['lt', $maxMoney];
            }
        }

        $startTime = intval($startTime);
        $endTime = intval($endTime);
        if ($startTime > $endTime) {
            $tmp = $endTime;
            $endTime = $startTime;
            $startTime = $tmp;
        }
        if ($startTime > 0) {
            $map['create_time'] = ['gt', $startTime];
        }

        if ($endTime > 0) {
            if (array_key_exists('create_time', $map)) {
                $map['create_time'] = ['gt', $startTime, 'lt', $endTime];
            } else {
                $map['create_time'] = ['lt', $endTime];
            }
        }

        return $this->pay361WithdrawOrderService->queryAndCount($map, $pagingParams, ["createTime" => "desc"]);
    }
}
