<?php


namespace App\AdminController;


use App\Entity\AuditLog;
use App\Entity\UserBankCard;
use App\Entity\UserIdCard;
use App\Entity\UserProfile;
use App\ServiceInterface\AuditLogServiceInterface;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use App\ServiceInterface\UserBankCardServiceInterface;
use App\ServiceInterface\UserGradeServiceInterface;
use App\ServiceInterface\UserIdCardServiceInterface;
use Dbh\SfCoreBundle\Common\UserLogServiceInterface;
use by\component\audit_log\AuditStatus;
use by\component\paging\vo\PagingParams;
use by\infrastructure\base\CallResult;
use by\infrastructure\constants\StatusEnum;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\UserProfileServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class UserIdCardController extends BaseNeedLoginController
{
    protected $userIdCardService;
    protected $userBankCardService;
    protected $auditLogService;
    protected $userProfileService;
    protected $userLogService;
    protected $logger;
    protected $userGradeService;

    public function __construct(
        UserGradeServiceInterface $userGradeService,
        LoggerInterface $logger,
        UserLogServiceInterface $userLogService, UserProfileServiceInterface $userProfile, UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, AuditLogServiceInterface $auditLogService, UserIdCardServiceInterface $userIdCardService, UserBankCardServiceInterface $userBankCardService, KernelInterface $kernel)
    {
        parent::__construct($userAccountService, $loginSession, $kernel);
        $this->logger = $logger;
        $this->userGradeService = $userGradeService;
        $this->userBankCardService = $userBankCardService;
        $this->userIdCardService = $userIdCardService;
        $this->auditLogService = $auditLogService;
        $this->userProfileService = $userProfile;
        $this->userLogService = $userLogService;
    }

    /**
     * @param $verify
     * @param PagingParams $pagingParams
     * @return CallResult|string
     */
    public function query($verify, PagingParams $pagingParams)
    {
        return $this->userIdCardService->queryAndCount(['verify' => $verify], $pagingParams, ["createTime" => 'desc']);
    }

    /**
     * 审核通过
     * 1. 调用客户录入操作
     * 2. 绑定结算卡操作
     * @param $userId
     * @return CallResult|string
     * @throws \by\component\exception\NotLoginException
     */
    public function pass($userId)
    {
        $this->checkLogin();
        $userIdCard = $this->userIdCardService->info(['uid' => $userId]);
        if (!($userIdCard instanceof UserIdCard)) {
            return 'user_id invalid';
        }

        if (empty($userIdCard->getFrontImgId())) {
            return 'invalid front img id';
        }
        if (empty($userIdCard->getBackImgId())) {
            return 'invalid back img id';
        }
        if (empty($userIdCard->getHandHoldImgId())) {
            return 'invalid hold img id';
        }

        $userBankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);

        if (!($userBankCard instanceof UserBankCard)) {
            return 'user bank card not exits';
        }

        if ($userIdCard->getVerify() == 1) {
            return CallResultHelper::success('', 'verified');
        }

        $userProfile = $this->userProfileService->info(['user' => $userId]);
        if (!($userProfile instanceof UserProfile)) return 'user is not exists';

        $this->userIdCardService->getEntityManager()->beginTransaction();
        try {
            // 更新身份证状态
            $userIdCard->setVerify(AuditStatus::Passed);
            $this->userIdCardService->flush($userIdCard);
            // 更新银行卡状态
            $userBankCard->setVerify(AuditStatus::Passed);
            $this->userBankCardService->flush($userBankCard);
            if (!$userProfile->isIdentityValidate()) {
                // 更新用户认证状态
                $userProfile->setIdentityValidate(true);
                $this->userProfileService->flush($userProfile);
            }

            // 复制卡 作为 主结算卡
            $this->copyAsMasterCard($userBankCard);
            $content = 'passed';
            $this->auditLogService->log($content, $this->getUid(), $this->getLoginUserNick(), $userId, AuditLog::IdentityAuth);
//            $this->logUserAction($this->userLogService, $this->getUid().' 审核了 '.$userId.' 的身份信息，结果为: 通过');
            $this->userIdCardService->getEntityManager()->commit();
            return CallResultHelper::success();
        } catch (Exception $exception) {
            $this->userIdCardService->getEntityManager()->rollback();
            return CallResultHelper::fail($exception->getMessage());
        }
    }


    protected function copyAsMasterCard(UserBankCard $userBankCard)
    {
        $masterUserBankCard = new UserBankCard();
        $masterUserBankCard->setBranchNo($userBankCard->getBranchNo());
        $masterUserBankCard->setRepaymentDate(intval($userBankCard->getRepaymentDate()));
        $masterUserBankCard->setExpireDate($userBankCard->getExpireDate());
        $masterUserBankCard->setPayAgreeId($userBankCard->getPayAgreeId());
        $masterUserBankCard->setFrontImgId($userBankCard->getFrontImgId());
        $masterUserBankCard->setMaster(AuditStatus::Passed);
        $masterUserBankCard->setVerify(intval($userBankCard->getVerify()));
        $masterUserBankCard->setCardUsage(UserBankCard::UsageBalanceCard);
        $masterUserBankCard->setCardNo($userBankCard->getCardNo());
        $masterUserBankCard->setFrontImg($userBankCard->getFrontImg());
        $masterUserBankCard->setCardCode($userBankCard->getCardCode());
        $masterUserBankCard->setName($userBankCard->getName());
        $masterUserBankCard->setCardType(intval($userBankCard->getCardType()));
        $masterUserBankCard->setOpeningBank($userBankCard->getOpeningBank());
        $masterUserBankCard->setBranchBank($userBankCard->getBranchBank());
        $masterUserBankCard->setMobile($userBankCard->getMobile());
        $masterUserBankCard->setUid($userBankCard->getUid());
        $masterUserBankCard->setIdNo($userBankCard->getIdNo());
        $masterUserBankCard->setCvn2($userBankCard->getCvn2());
        $masterUserBankCard->setStatus(StatusEnum::ENABLE);
        $masterUserBankCard->setBillDate($userBankCard->getBillDate());

        $this->userBankCardService->add($masterUserBankCard);
    }


    /**
     * @param $userId
     * @param $content
     * @return string
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \by\component\exception\NotLoginException
     */
    public function deny($userId, $content)
    {
        $this->checkLogin();
        $userIdCard = $this->userIdCardService->info(['uid' => $userId]);
        if (!($userIdCard instanceof UserIdCard)) {
            return 'id card not exists';
        }

        $userBankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);
        if (!($userBankCard instanceof UserBankCard)) {
            return 'user card not exists';
        }

        $userIdCard->setVerify(AuditStatus::Denied);
        $userBankCard->setVerify(AuditStatus::Denied);

        $this->auditLogService->log($content, $this->getUid(), $this->getLoginUserNick(), $userId, AuditLog::IdentityAuth);

        $this->userBankCardService->flush($userBankCard);
        $this->userIdCardService->flush($userIdCard);
        return CallResultHelper::success();
    }


    public function info($userId)
    {
        $bankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);
        if (!($bankCard instanceof UserBankCard)) {
            return CallResultHelper::success([], 'not exists');
        }

        $idCard = $this->userIdCardService->info(['uid' => $userId]);
        if (!($idCard instanceof UserIdCard)) {
            return CallResultHelper::success([], 'not exists');
        }
        $list = $this->auditLogService->queryBy(['object_id' => $userId, 'object_type' => AuditLog::IdentityAuth], new PagingParams(0, 10));

        array_walk($list, function ($item, $index) use (&$list) {
            $list[$index] = [
                'audit_nick' => $item['audit_nick'],
                'content' => $item['content'],
                'time' => $item['create_time']
            ];
        });

        return CallResultHelper::success([
            'verify' => $idCard->getVerify(),
            'id_front_img' => $idCard->getFrontImg(),
            'id_back_img' => $idCard->getBackImg(),
            'id_no' => $idCard->getCardNo(),
            'id_hold_img' => $idCard->getHandHoldImg(),
            'zipcode' => $idCard->getZipcode(),
            'address' => $idCard->getAddress(),
            'email' => $idCard->getEmail(),
            'name' => $idCard->getRealName(),
            'bank_card_no' => $bankCard->getCardNo(),
            'bank_front_img' => $bankCard->getFrontImg(),
            'branch_bank' => $bankCard->getBranchBank(),
            'opening_bank' => $bankCard->getOpeningBank(),
            'mobile' => $bankCard->getMobile(),
            'card_code' => $bankCard->getCardCode(),
            'log_his' => ($list)
        ]);
    }
}
