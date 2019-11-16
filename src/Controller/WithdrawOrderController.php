<?php


namespace App\Controller;


use App\Common\ByPayEnum;
use App\Entity\Pay361WithdrawOrder;
use App\Helper\CodeGenerator;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use by\component\fyt\FytPay;
use by\component\fyt\FytPayInfo;
use by\component\paging\vo\PagingParams;
use by\component\pay361\Pay361;
use by\component\pay361\PayInfo;
use by\component\wmpay\WmPay;
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

    public function createWmpay($bankCardNumber, $bankId, $money, $cardUserName)
    {

        $this->checkLogin();
        $passagewayCode = ByPayEnum::WmPay;
        $entity = new Pay361WithdrawOrder();
        $entity->setBankCardNumber($bankCardNumber);
        $entity->setBankName($bankId);
        $entity->setMoney($money);
        $entity->setCardUserName($cardUserName);
        $entity->setOrderNo((CodeGenerator::payCodeByClientId($bankCardNumber)));
        $entity->setPassagewayCode($passagewayCode);
        $entity->setNotifyUrl(WmPay::getInstance()->getNotifyUrl());

        $this->pay361WithdrawOrderService->add($entity);
        $note = '用户' . $this->getUid() . '发起了代付请求' . $entity->getOrderNo();
        $this->logUserAction($this->userLogService, $note);
        $subject = $body = "升级VIP";
        return $this->wmpay($entity, $subject, $body);
    }

    protected function wmpay(Pay361WithdrawOrder $order, $subject, $body)
    {
        return WmPay::getInstance()->pay($order->getOrderNo(), intval($order->getMoney() * 100), $order->getBankCardNumber(), $order->getCardUserName(), $order->getBankName(), $subject, $body);
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
    public function create($bankCardNumber, $bankName, $registBankName,
                           $money, $passagewayCode, $cardUserName, $certNumber, $registBank = '', $cityNumber = '', $shopPhone = ''
    )
    {
        if (empty($shopPhone)) {
            $shopPhone = Pay361::getDefaultShopPhone();
        }

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

        $entity->setOrderNo((CodeGenerator::payCodeByClientId($shopPhone . $passagewayCode . $money)));

        $entity->setNotifyUrl(FytPay::getInstance()->getNotifyUrl());
//        $entity->setNotifyUrl(ByEnv::get('PAY361_NOTIFY_URL'));

        $data = Object2DataArrayHelper::getDataArrayFrom($entity);
        ksort($data);
        $sign = md5(json_encode($data, JSON_UNESCAPED_UNICODE));
        $entity->setSign($sign);

        $this->pay361WithdrawOrderService->add($entity);

        // 发起代付申请
//        $payInfo = new PayInfo();
//        $payInfo->setShopPhone($entity->getShopPhone());
//        $payInfo->setBankCardNumber($entity->getBankCardNumber());
//        $payInfo->setBankName($entity->getBankName());
//        $payInfo->setRegistBankName($entity->getRegistBankName());
//        $payInfo->setMoney($entity->getMoney());
//        $payInfo->setPassagewayCode($entity->getPassagewayCode());
//        $payInfo->setCardUserName($entity->getCardUserName());
//        $payInfo->setShopSubNumber($entity->getOrderNo());
//        $payInfo->setNotifyUrl(($entity->getNotifyUrl()));
        $note = '用户' . $this->getUid() . '发起了代付请求' . $entity->getOrderNo();
        $this->logUserAction($this->userLogService, $note);
        return $this->fytPay($entity);
//        return Pay361::getInstance()->pay($payInfo);
    }

    /**
     * @param Pay361WithdrawOrder $order
     * @return \by\infrastructure\base\CallResult
     * @throws \Exception
     */
    protected function fytPay(Pay361WithdrawOrder $order)
    {

        $fytPayInfo = new FytPayInfo();
        $fytPayInfo->setCporder($order->getOrderNo());
        $fytPayInfo->setNotifyUrl($order->getNotifyUrl());
        $fytPayInfo->setAmount($order->getMoney());
        $fytPayInfo->setCardNo($order->getBankCardNumber());
        $fytPayInfo->setCardName($order->getCardUserName());
        $fytPayInfo->setBankName($order->getBankName());
        return FytPay::getInstance()->pay($fytPayInfo);
    }


    public function query(PagingParams $pagingParams, $startTime, $endTime, $minMoney = 0, $maxMoney = 0)
    {
        $this->checkLogin();
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

        $fields = [
            "id", "order_no",
            "shop_phone", "bank_card_number",
            "bank_name", "regist_bank", "regist_bank_name",
            "money", "passageway_code",
            "card_user_name", "cert_number",
            "create_time", "update_time", "state", "pay_order_no",
            "actual_money", "sub_money", "service_charge",
            "notify_time", "notify_shop_phone",
            "remark"
        ];

        return $this->pay361WithdrawOrderService->queryAndCount($map, $pagingParams, ["createTime" => "desc"], $fields);
    }

    public function info($orderNo)
    {
        $this->checkLogin();
        return $this->pay361WithdrawOrderService->info(['order_no' => $orderNo]);
    }
}
