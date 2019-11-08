<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-05-29
 * Time: 17:51
 */

namespace byTest\component\zmf_pay;

use by\component\fyt\FytPay;
use by\component\fyt\FytPayInfo;
use by\component\fyt\FytSignTool;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ZmfPayApiTest extends TestCase
{
    public function testHadProduct()
    {
        (new Dotenv())->load(__DIR__.'/../../.env.local');
        $api = FytPay::getInstance()->openDebug();
//        $ret = $api->balance();
//        var_dump($ret);
        $payInfo = new FytPayInfo();
        $payInfo->setNotifyUrl('http://www.baidu.com/333');
        $payInfo->setAmount(2);
        $payInfo->setBankName('card');
        $payInfo->setPayType(1);
        $payInfo->setCardName('333');
        $payInfo->setCardNo('444');
        $payInfo->setCporder('66666');
        $ret = $api->pay($payInfo);
        var_dump($ret);
//        $this->assertTrue($ret->isSuccess(), $ret->getMsg());
    }
}
