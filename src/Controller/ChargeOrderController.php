<?php


namespace App\Controller;


use App\Entity\ChargeOrder;
use App\Helper\CodeGenerator;
use App\ServiceInterface\ChargeOrderServiceInterface;
use by\component\exception\NotLoginException;
use by\component\fyt\FytChargeInfo;
use by\component\fyt\FytPay;
use by\component\paging\vo\PagingParams;
use by\infrastructure\base\CallResult;
use Dbh\SfCoreBundle\Common\ByEnv;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use Dbh\SfCoreBundle\Common\UserLogServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class ChargeOrderController extends BaseNeedLoginController
{
    protected $chargeService;
    protected $userLogService;

    public function __construct(
        UserLogServiceInterface $userLogService,
        ChargeOrderServiceInterface $chargeOrderService,
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, KernelInterface $kernel)
    {
        $this->userLogService = $userLogService;
        $this->chargeService = $chargeOrderService;
        parent::__construct($userAccountService, $loginSession, $kernel);
    }

    /**
     * @param $name
     * @param $card
     * @param $evidence
     * @param $amount
     * @param $mark
     * @return CallResult
     * @throws NotLoginException
     */
    public function create($name, $card, $evidence, $amount, $mark) {
        $this->checkLogin();
        $entity = new ChargeOrder();
        $entity->setName($name);
        $entity->setCardNo($card);
        $entity->setMark($mark);
        $entity->setEvidence($evidence);
        $entity->setAmount($amount);
        $entity->setOrderNo((CodeGenerator::payCodeByClientId($card)));
        $entity->setNotifyUrl(ByEnv::get("FYT_CHARGE_NOTIFY_URL"));
        $this->chargeService->add($entity);
        $note = '用户'.$this->getUid().'发起了充值请求'.$entity->getOrderNo();
        $this->logUserAction($this->userLogService, $note);
        return $this->fytCharge($entity);
    }

    /**
     * @param ChargeOrder $order
     * @return CallResult
     * @throws Exception
     */
    protected function fytCharge(ChargeOrder $order) {
        $fytInfo = new FytChargeInfo();
        $fytInfo->setCporder($order->getOrderNo());
        $fytInfo->setNotifyUrl($order->getNotifyUrl());
        $fytInfo->setAmount($order->getAmount());
        $fytInfo->setEvidence($order->getEvidence());
        $fytInfo->setCard($order->getCardNo());
        $fytInfo->setName($order->getName());
        $fytInfo->setMark($order->getMark());
        return FytPay::getInstance()->charge($fytInfo);
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

        return $this->chargeService->queryAndCount($map, $pagingParams, ["createTime" => "desc"]);
    }

    public function info($orderNo) {
        return $this->chargeService->info(['order_no' => $orderNo]);
    }
}
