<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-05-29
 * Time: 17:51
 */

namespace byTest\component\zmf_pay;

use by\component\dypay\DyPay;
use by\component\fyt\FytChargeInfo;
use by\component\fyt\FytPay;
use by\component\fyt\FytPayInfo;
use by\component\fyt\FytSignTool;
use by\component\wmpay\WmPay;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ZmfPayApiTest extends TestCase
{
    public function testHadProduct()
    {
        (new Dotenv(true))->load(__DIR__.'/../../.env.local');
//        $api = WmPay::getInstance()->openDebug();
        $api = DyPay::getInstance()->openDebug();
        $orderNo = date('Ymdhis');
        $subjet = ('采购');
        $body = ('Test');
        var_dump($api->getKey());
        var_dump($api->getAccount());
        $ret = $api->query("20191223034655");
        $paybanknumber = "103100000026";
//
        $ret = $api->pay($orderNo, "2", "6213361198015261579", "周道伟", "中国农业银行", $paybanknumber);
        var_dump($ret);
//        $api = FytPay::getInstance()->openDebug();
//        $chargeInfo = new FytChargeInfo();
//        $chargeInfo->setAmount(2);
//        $chargeInfo->setCporder('333');
//        $chargeInfo->setName('444');
//        $chargeInfo->setEvidence('http://www');
//        $chargeInfo->setNotifyurl('http://');
//        $chargeInfo->setMark('444');
//        $chargeInfo->setCard('333333');
//        $ret = $api->charge($chargeInfo);
//        var_dump($ret);

    //        $ret = $api->chargeInfo();
//        var_dump($ret);
//        $payInfo = new FytPayInfo();
//        $payInfo->setNotifyUrl('http://www.baidu.com/333');
//        $payInfo->setAmount(2);
//        $payInfo->setBankName('card');
//        $payInfo->setPayType(1);
//        $payInfo->setCardName('333');
//        $payInfo->setCardNo('444');
//        $payInfo->setCporder('66666');
//        $ret = $api->pay($payInfo);
//        var_dump($ret);
//        $this->assertTrue($ret->isSuccess(), $ret->getMsg());
    }
}
