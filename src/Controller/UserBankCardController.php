<?php


namespace App\Controller;


use App\Common\ByCrypt;
use App\Entity\UserBankCard;
use App\Entity\UserIdCard;
use App\Exception\NotLoginException;
use App\Service\CbPayService;

use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use App\ServiceInterface\UserBankCardServiceInterface;
use App\ServiceInterface\UserIdCardServiceInterface;
use by\component\audit_log\AuditStatus;
use by\component\paging\vo\PagingParams;
use by\component\string_extend\helper\StringHelper;
use by\component\zmf_pay\common\ZmfProductCode;
use by\infrastructure\constants\StatusEnum;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class UserBankCardController extends BaseNeedLoginController
{
    protected $userBankCardService;
    protected $auditLogService;
    protected $userIdCardService;
    protected $payService;
    protected $logger;

    public function __construct(LoggerInterface $logger,
                                UserIdCardServiceInterface $userIdCardService, LoginSessionInterface $loginSession,
                                UserAccountServiceInterface $userAccountService,
                                UserBankCardServiceInterface $userBankCardService, KernelInterface $kernel)
    {
        parent::__construct($userAccountService, $loginSession, $kernel);
        $this->userBankCardService = $userBankCardService;
        $this->userAccountService = $userAccountService;
        $this->userIdCardService = $userIdCardService;
        $this->payService = new CbPayService();
        $this->logger = $logger;
    }

    /**
     * 设置主结算卡，并修改客户的结算卡
     * @param $id
     * @param $cardUsage
     * @return mixed|string
     * @throws NotLoginException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function setMasterBalance($id, $cardUsage)
    {
        $this->checkLogin();
        $bankCard = $this->userBankCardService->info(['id' => $id, 'uid' => $this->getUid(), 'card_usage' => $cardUsage, 'status' => StatusEnum::ENABLE]);
        if (!($bankCard instanceof UserBankCard)) return 'id invalid';

        if ($cardUsage == UserBankCard::UsageBalanceCard) {
            // 主结算卡
            $ret = $this->uploadBankCard($this->getUid(), $bankCard);
            if ($ret->isFail()) {
                $this->logger->error($ret->getMsg(), ['cm' => self::class . ':setMasterBalance']);
                return CallResultHelper::fail('上报卡信息失败,请重试');
            }
        }


        $this->userBankCardService->updateOne(['master' => 1, 'uid' => $this->getUid(), 'card_usage' => $cardUsage], ['master' => 0]);

        $bankCard->setMaster(1);
        $this->userBankCardService->flush($bankCard);

        return $bankCard;
    }

    /**
     * @param $cardNo
     * @param $bankName
     * @param $mobile
     * @param $cvn2
     * @param $expireDate
     * @param $billDate
     * @param $repaymentDate
     * @return mixed|string
     * @throws NotLoginException
     */
    public function bindPayCard($cardNo, $bankName, $mobile, $expireDate, $billDate, $repaymentDate, $cvn2)
    {
        if (empty($expireDate)) {
            return '卡有效期必填';
        }
        if (empty($billDate)) {
            return '卡账单日必填';
        }
        if (empty($cvn2)) {
            return '卡后3位必填';
        }
        if (empty($repaymentDate)) {
            return '还款日必填';
        }
        return $this->create($cardNo, $bankName, $bankName, $mobile, $cvn2, $expireDate, UserBankCard::UsagePaymentCard, UserBankCard::TypeCredit, $billDate, $repaymentDate);
    }

    /**
     * @param $cardNo
     * @param $bankName
     * @param $branchName
     * @param $mobile
     * @return mixed|string
     * @throws NotLoginException
     */
    public function bindBalanceCard($cardNo, $bankName, $branchName, $mobile)
    {
        return $this->create($cardNo, $bankName, $branchName, $mobile, '', '', UserBankCard::UsageBalanceCard, UserBankCard::TypeDebit, 0, 0);
    }

    /**
     * @param PagingParams $pagingParams
     * @param $cardUsage
     * @return mixed
     * @throws NotLoginException
     */
    public function query(PagingParams $pagingParams, $cardUsage)
    {
        $this->checkLogin();
        $map = [
            'uid' => $this->getUid(),
            'card_usage' => $cardUsage,
            'status' => StatusEnum::ENABLE
        ];
        $list = $this->userBankCardService->queryBy($map, $pagingParams, ['master' => 'desc', 'createTime' => 'desc']);
        foreach ($list as &$vo) {
            $vo['card_no'] = ByCrypt::desDecode($vo['card_no']);
            $vo['card_no'] = ByCrypt::hideSensitive($vo['card_no'], 4, 2, 8);
            $vo['id_no'] = ByCrypt::desDecode($vo['id_no']);
            $vo['id_no'] = ByCrypt::hideSensitive($vo['id_no'], 4, 3);
            $vo['mobile'] = ByCrypt::hideSensitive($vo['mobile'], 3, 4);
            $vo['branch_no'] = ByCrypt::hideSensitive($vo['branch_no'], 3, 3);
            //
            $vo['pay_agree_id'] = ByCrypt::hideSensitive($vo['pay_agree_id'], 3, 3);
        }
        return $list;
    }


    /**
     * @param $id
     * @return string
     * @throws NotLoginException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function unbind($id)
    {
        $this->checkLogin();
        $userBankCard = $this->userBankCardService->info(['id' => $id]);
        if (!($userBankCard instanceof UserBankCard)) return 'invalid id';
        if ($userBankCard->getCardUsage() == UserBankCard::UsageBalanceCard) {
            $masterCard = $this->userBankCardService->info(['master' => 1, 'status' => StatusEnum::ENABLE, 'card_usage' => UserBankCard::UsageBalanceCard]);
            if ($masterCard instanceof UserBankCard && $userBankCard->getId() == $masterCard->getId()) {
                return '主结算卡必须存在';
            }
        }
        $userBankCard->setMaster(0);
        $userBankCard->setStatus(StatusEnum::SOFT_DELETE);
        $this->userBankCardService->flush($userBankCard);
        return 'success';
    }


    /**
     * @param $cardNo
     * @param $bankName
     * @param $branchName
     * @param $mobile
     * @param $cvn2
     * @param $expireDate
     * @param $cardUsage
     * @param $cardType
     * @param int $billDate
     * @param int $repaymentDate
     * @return mixed|string
     * @throws NotLoginException
     */
    protected function create($cardNo, $bankName, $branchName, $mobile, $cvn2, $expireDate, $cardUsage, $cardType, $billDate = 0, $repaymentDate = 0)
    {
        $this->checkLogin();

        $userIdCard = $this->userIdCardService->info(['uid' => $this->getUid(), 'verify' => AuditStatus::Passed]);
        if (!($userIdCard instanceof UserIdCard)) {
            return 'user verified id card not exists';
        }

        $bankCard = $this->userBankCardService->info(['card_type' => $cardType, 'card_no' => $cardNo, 'uid' => $this->getUid(), 'status' => StatusEnum::ENABLE]);
        if ($bankCard instanceof UserBankCard) {
            return 'card exists';
        }

        $ret = $this->payService->fourEVerify($userIdCard->getRealName(), $userIdCard->getCardNo(), $mobile, $cardNo);
        if ($ret->isFail()) {
            return $ret;
        }
        if (strlen($expireDate) != 4) {
            return '卡有效期格式错误(例子:2402)';
        }

        $bankCard = new UserBankCard();
        $bankCard->setCvn2($cvn2);
        $bankCard->setOpeningBank($bankName);
        $bankCard->setBranchBank($branchName);
        $bankCard->setExpireDate($expireDate);
        $bankCard->setBillDate(intval($billDate));
        $bankCard->setRepaymentDate(intval($repaymentDate));
        $bankCard->setUid($this->getUid());
        $bankCard->setName($userIdCard->getRealName());
        $bankCard->setIdNo($userIdCard->getCardNo());
        $bankCard->setFrontImgId('');
        $bankCard->setFrontImg('');
        $bankCard->setMaster(0);
        $bankCard->setMobile($mobile);
        $bankCard->setCardUsage($cardUsage);
        $bankCard->setCardType($cardType);
        $bankCard->setCardNo($cardNo);
        $bankCard->setStatus(StatusEnum::ENABLE);
        $bankCard->setCardCode('');
        $bankCard->setVerify(1);



        return $this->userBankCardService->add($bankCard);
    }

    /**
     * 上报结算卡
     * @param $userId
     * @param UserBankCard $bankCard
     * @return \by\infrastructure\base\CallResult|string
     */
    public function uploadBankCard($userId, UserBankCard $bankCard)
    {
        $idCard = $this->userIdCardService->verifiedIdCard($userId);
        if (!$idCard instanceof UserIdCard) {
            return 'please verify user id card';
        }
        $cardNo = $bankCard->getCardNo();
        $bankName = $bankCard->getOpeningBank();
        $bankNo = $bankCard->getBranchNo();
        $branchBankName = $bankCard->getBranchBank();
        $mobile = $bankCard->getMobile();

        $masterBankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageBalanceCard, 'master' => 1]);

        if ($masterBankCard instanceof UserBankCard) {

            // if (!($ret->getCode() == '2002' &&  strpos($ret->getMsg(), '之前主卡信息相同') !== false)) {
            //                        return $ret;
            //                    }
            if ($masterBankCard->getCardNo() == $cardNo) {
                return CallResultHelper::success();
            }

            // 修改
            return $this->payService->modifyDebitCard($userId, $masterBankCard->getCardNo(), $idCard->getRealName(), $cardNo, $bankName, $bankNo, $branchBankName, $idCard->getCardNo(), $mobile);
        } else {
            // 新增
            return $this->payService->bindDebitCard($userId, $idCard->getRealName(), $cardNo, $bankName, $bankNo, $branchBankName, $idCard->getCardNo(), $mobile);
        }
    }

    /**
     * 代扣
     * @param $bankId
     * @param string $verificationCode
     * @return \by\infrastructure\base\CallResult|string
     */
    public function signWithhold($bankId, $verificationCode = '')
    {

        return $this->signProduct(ZmfProductCode::Pay, $bankId, $verificationCode);
    }

    /**
     * 代付
     * @param $bankId
     * @param string $verificationCode
     * @return \by\infrastructure\base\CallResult|string
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function signRepay($bankId, $verificationCode = '')
    {
        $ret = $this->signProduct(ZmfProductCode::WithDraw, $bankId, $verificationCode);

        return $ret;
    }

    /**
     * 签约协议
     * @param $productCode
     * @param $bankId
     * @param string $verificationCode
     * @return \by\infrastructure\base\CallResult|string
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function signProduct($productCode, $bankId, $verificationCode = '')
    {
        $userId = $this->getUid();
        $idCard = $this->userIdCardService->verifiedIdCard($userId);
        if (!$idCard instanceof UserIdCard) {
            return 'please verify user id card';
        }

        $bankCard = $this->userBankCardService->info(['uid' => $userId, 'id' => $bankId]);
        if (!$bankCard instanceof UserBankCard) {
            return 'bank card not exists';
        }

        $phoneNo = $bankCard->getMobile();
        $acctNo = $bankCard->getCardNo();
        if ($bankCard->getCardType() == UserBankCard::TypeCredit) {
            $expDate = $bankCard->getExpireDate();
            $cvn2 = $bankCard->getCvn2();
        } else {
            $expDate = null;
            $cvn2 = null;
        }

        $ret = $this->payService->signProduct($userId, $idCard->getCardNo(), $idCard->getRealName(), $productCode, $phoneNo, $acctNo, $expDate, $verificationCode, $cvn2);
        if ($ret->isSuccess() && !empty($verificationCode)) {
            $data = $ret->getData();
            if (is_array($data) && array_key_exists('payAgreeId', $data)) {
                $payAgreeId = $data['payAgreeId'];
                if ($productCode == ZmfProductCode::WithDraw) {
                    $update = ['withdraw_agree_id' => $payAgreeId];
                } elseif ($productCode == ZmfProductCode::Pay) {
                    $update = ['pay_agree_id' => $payAgreeId];
                } else {
                    return $productCode . '产品编码错误';
                }
                $this->userBankCardService->updateOne(['id' => $bankCard->getId()], $update);
            }
        }
        return $ret;
    }
}
