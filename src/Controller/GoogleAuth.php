<?php


namespace App\Controller;


use App\Entity\UserAccount;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

class GoogleAuth extends BaseNeedLoginController
{
    public function qrcode($size = 300) {
        $this->checkLogin();
        $auth = new \App\Helper\GoogleAuth();
        $user = $this->getUser();
        if (!$user instanceof UserAccount) {
            return 'failed';
        }

        $url = $auth->getQRCodeGoogleUrl('yub', $user->getGoogleSecret());

        $qrCode = new QrCode();
        $qrCode->setSize($size);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM));
        return new QrCodeResponse($qrCode);
    }
}
