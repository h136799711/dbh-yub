<?php


namespace App\Controller;


use App\Entity\AuditLog;
use App\Entity\UserAccount;
use App\Entity\UserBankCard;
use App\Entity\UserIdCard;
use App\Exception\NoParamException;
use App\Service\CbPayService;
use App\ServiceInterface\AuditLogServiceInterface;
use App\ServiceInterface\CfPayInterface;

use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use App\ServiceInterface\UserBankCardServiceInterface;
use App\ServiceInterface\UserIdCardServiceInterface;
use by\component\helper\ValidateHelper;
use by\infrastructure\base\CallResult;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class UserIdCardController extends BaseNeedLoginController
{
    protected $userIdCardService;
    protected $userBankCardService;
    protected $auditLogService;

    public function __construct(
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, AuditLogServiceInterface $auditLogService, UserIdCardServiceInterface $userIdCardService, UserBankCardServiceInterface $userBankCardService, KernelInterface $kernel)
    {
        parent::__construct($userAccountService, $loginSession, $kernel);
        $this->userBankCardService = $userBankCardService;
        $this->userIdCardService = $userIdCardService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * @param $userId
     * @param $name
     * @param $idNo
     * @param $mobile
     * @param $cardNo
     * @param $openingBank
     * @param $branchBank
     * @param $branchNo
     * @return CallResult|string
     * @throws NoParamException
     */
    public function createAuthInfo($userId, $name, $idNo, $mobile, $cardNo, $openingBank, $branchBank, $branchNo)
    {
        $idFrontImg = $this->getParam('id_front_img', '', true);
        $idBackImg = $this->getParam('id_back_img', '', true);
        $idHoldImg = $this->getParam('id_hold_img', '', true);
        $bankImg = $this->getParam('bank_img', '', true);

        $frontImgId = $this->getParam('id_front_img_id', '', true);
        $backImgId = $this->getParam('id_back_img_id', '', true);
        $holdImgId = $this->getParam('id_hold_img_id', '', true);
        $bankImgId = $this->getParam('bank_img_id', '', true);
        $zipcode = $this->getParam('zipcode', '', true);
        $email = $this->getParam('email', '', true);
        $address = $this->getParam('address', '', true);
        $expireDate = $this->getParam('expire_date', '', true);

        if (empty($branchNo)) return '支行联号缺失';

        if (!ValidateHelper::isEmail($email)) {
            return '邮箱格式错误';
        }

        if (strlen($expireDate) !== 8) {
            return '身份证过期日期格式错误(正确: 20201201)';
        }

        if (strlen($idNo) < 15) return 'id no error';

        $userAccount = $this->userAccountService->info(['id' => $userId]);
        if (!($userAccount instanceof UserAccount)) return 'user is not exists';

        // 已认证 则返回
        if ($userAccount->getProfile()->isIdentityValidate()) {
            return 'user verified';
        }

        // 是否存在记录
        $userIdCard = $this->userIdCardService->info(['uid' => $userId]);
        if ($userIdCard instanceof UserIdCard) {
            return 'user bank card had exists';
        }
        $userIdCard = new UserIdCard();
        $userIdCard->setBackImgId($backImgId);
        $userIdCard->setFrontImgId($frontImgId);
        $userIdCard->setHandHoldImgId($holdImgId);
        $userIdCard->setExpireDate($expireDate);

        $userIdCard->setAddress($address);
        $userIdCard->setEmail($email);
        $userIdCard->setZipcode($zipcode);
        $userIdCard->setUid($userId);
        $userIdCard->setCardNo($idNo);
        $userIdCard->setRealName($name);
        $userIdCard->setFrontImg($idFrontImg);
        $userIdCard->setBackImg($idBackImg);
        $userIdCard->setHandHoldImg($idHoldImg);
        $userIdCard->setVerify(0);
        $birthday = strlen($idNo) == 15 ? ('19' . substr($idNo, 6, 6)) : substr($idNo, 6, 8);
        $userIdCard->setBirthday($birthday);
        $sex = substr($idNo, (strlen($idNo) == 15 ? -2 : -1), 1) % 2 ? '1' : '0';
        $userIdCard->setSex($sex == '1' ? true : false);

        $bankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);
        if ($bankCard instanceof UserBankCard) {
            return 'user bank card had exists';
        }
        $bankCard = new UserBankCard();
        $bankCard->setPayAgreeId('');
        $bankCard->setCvn2('');
        $bankCard->setBranchNo($branchNo);
        $bankCard->setFrontImgId($bankImgId);
        $bankCard->setRepaymentDate(0);
        $bankCard->setCvn2('');
        $bankCard->setExpireDate('');
        $bankCard->setBillDate(0);
        $bankCard->setFrontImgId($frontImgId);
        $bankCard->setFrontImg($bankImg);
        $bankCard->setCardNo($cardNo);
        $bankCard->setUid($userId);
        $bankCard->setIdNo($idNo);
        $bankCard->setBranchBank($branchBank);
        $bankCard->setMobile($mobile);
        $bankCard->setName($name);
        $bankCard->setOpeningBank($openingBank);
        $bankCard->setCardType(UserBankCard::TypeDebit);
        $bankCard->setCardUsage(UserBankCard::UsageVerifyCard);
        $bankCard->setCardCode('');
        $bankCard->setVerify(0);

        // TODO: 做四要素校验，校验失败则返回，成功则进入人工审核环节
        $this->userIdCardService->getEntityManager()->beginTransaction();
        try {
            $this->userIdCardService->getEntityManager()->persist($userIdCard);
            $this->userIdCardService->getEntityManager()->flush();
            $this->userBankCardService->getEntityManager()->persist($bankCard);
            $this->userBankCardService->getEntityManager()->flush();

            $this->userIdCardService->getEntityManager()->commit();

            return CallResultHelper::success();
        } catch (Exception $exception) {
            $this->userIdCardService->getEntityManager()->rollback();
            return CallResultHelper::fail($exception->getMessage());
        }
    }

    /**
     * 更新
     * @param $userId
     * @param $name
     * @param $idNo
     * @param $mobile
     * @param $cardNo
     * @param $openingBank
     * @param $branchBank
     * @param $branchNo
     * @return CallResult|string
     * @throws NoParamException
     * @throws \by\component\exception\NotLoginException
     */
    public function updateAuthInfo($userId, $name, $idNo, $mobile, $cardNo, $openingBank, $branchBank, $branchNo)
    {
        $this->checkLogin();

        $idFrontImg = $this->getParam('id_front_img', '', true);
        $idBackImg = $this->getParam('id_back_img', '', true);
        $idHoldImg = $this->getParam('id_hold_img', '', true);
        $bankImg = $this->getParam('bank_img', '', true);

        $frontImgId = $this->getParam('id_front_img_id', '', true);
        $backImgId = $this->getParam('id_back_img_id', '', true);
        $holdImgId = $this->getParam('id_hold_img_id', '', true);
        $bankImgId = $this->getParam('bank_img_id', '', true);
        $zipcode = $this->getParam('zipcode', '', true);
        $email = $this->getParam('email', '', true);
        $address = $this->getParam('address', '', true);
        $expireDate = $this->getParam('expire_date', '', true);

        if (empty($branchNo)) return '支行联号缺失';

        if (!ValidateHelper::isEmail($email)) {
            return '邮箱格式错误';
        }

        if (strlen($expireDate) !== 8) {
            return '身份证过期日期格式错误(正确: 20201201)';
        }

        if (strlen($idNo) < 15) return 'id no error';

        $userAccount = $this->userAccountService->info(['id' => $userId]);
        if (!($userAccount instanceof UserAccount)) return 'user is not exists';

        // 已认证 则返回
        if ($userAccount->getProfile()->isIdentityValidate()) {
            return 'user verified';
        }
        // 是否存在记录
        $userIdCard = $this->userIdCardService->info(['uid' => $userId]);
        if (!$userIdCard instanceof UserIdCard) {
            return 'user bank card not exists';
        }
        $userIdCard->setBackImgId($backImgId);
        $userIdCard->setFrontImgId($frontImgId);
        $userIdCard->setHandHoldImgId($holdImgId);
        $userIdCard->setExpireDate($expireDate);

        $userIdCard->setAddress($address);
        $userIdCard->setEmail($email);
        $userIdCard->setZipcode($zipcode);
        $userIdCard->setUid($userId);
        $userIdCard->setCardNo($idNo);
        $userIdCard->setRealName($name);
        $userIdCard->setFrontImg($idFrontImg);
        $userIdCard->setBackImg($idBackImg);
        $userIdCard->setHandHoldImg($idHoldImg);
        $userIdCard->setVerify(0);
        $birthday = strlen($idNo) == 15 ? ('19' . substr($idNo, 6, 6)) : substr($idNo, 6, 8);
        $userIdCard->setBirthday($birthday);
        $sex = substr($idNo, (strlen($idNo) == 15 ? -2 : -1), 1) % 2 ? '1' : '0';
        $userIdCard->setSex($sex == '1' ? true : false);

        $bankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);
        if (!$bankCard instanceof UserBankCard) {
            return 'user bank card not exists';
        }
        $bankCard->setPayAgreeId('');
        $bankCard->setCvn2('');
        $bankCard->setBranchNo($branchNo);
        $bankCard->setFrontImgId($bankImgId);
        $bankCard->setRepaymentDate(0);
        $bankCard->setCvn2('');
        $bankCard->setExpireDate('');
        $bankCard->setBillDate(0);
        $bankCard->setFrontImgId($frontImgId);
        $bankCard->setFrontImg($bankImg);
        $bankCard->setCardNo($cardNo);
        $bankCard->setUid($userId);
        $bankCard->setIdNo($idNo);
        $bankCard->setBranchBank($branchBank);
        $bankCard->setMobile($mobile);
        $bankCard->setName($name);
        $bankCard->setOpeningBank($openingBank);
        $bankCard->setCardType(UserBankCard::TypeDebit);
        $bankCard->setCardUsage(UserBankCard::UsageVerifyCard);
        $bankCard->setCardCode('');
        $bankCard->setVerify(0);

        // TODO: 做四要素校验，校验失败则返回，成功则进入人工审核环节
        $this->userIdCardService->getEntityManager()->beginTransaction();
        try {
            $this->userIdCardService->getEntityManager()->flush($userIdCard);
            $this->userBankCardService->getEntityManager()->flush($bankCard);
            $this->userIdCardService->getEntityManager()->commit();

            return CallResultHelper::success();
        } catch (Exception $exception) {
            $this->userIdCardService->getEntityManager()->rollback();
            return CallResultHelper::fail($exception->getMessage());
        }
    }


    /**
     * 先查询是否有记录，记录是否认证失败了
     * @param $userId
     * @return array|CallResult
     */
    public function info($userId)
    {
        $bankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);
        if (!($bankCard instanceof UserBankCard)) {
            return CallResultHelper::fail('not exists', '', -2);
        }

        $idCard = $this->userIdCardService->info(['uid' => $userId]);
        if (!($idCard instanceof UserIdCard)) {
            return CallResultHelper::fail('not exists', '', -2);
        }
        $list = $this->auditLogService->queryAllBy(['object_id' => $userId, 'object_type' => AuditLog::IdentityAuth]);

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
            'log_his' => $list //审核日志
        ]);
    }


    /**
     * app端 认证详情
     * @return CallResult
     * @throws \by\component\exception\NotLoginException
     */
    public function apiInfo()
    {
        $this->checkLogin();
        $userId = $this->getUid();
        $bankCard = $this->userBankCardService->info(['uid' => $userId, 'card_usage' => UserBankCard::UsageVerifyCard]);
        if (!($bankCard instanceof UserBankCard)) {
            return CallResultHelper::success(['info' => '0']);
        }

        $idCard = $this->userIdCardService->info(['uid' => $userId]);
        if (!($idCard instanceof UserIdCard)) {
            return CallResultHelper::success(['info' => '0']);
        }

        $list = $this->auditLogService->queryAllBy(['object_id' => $userId, 'object_type' => AuditLog::IdentityAuth]);

        return CallResultHelper::success([
            'info' => '1',
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
            'log_his' => $list //审核日志
        ]);
    }
}
