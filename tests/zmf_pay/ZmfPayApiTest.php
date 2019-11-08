<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2019-05-29
 * Time: 17:51
 */

namespace byTest\component\zmf_pay;

use by\component\fyt\FytPay;
use by\component\fyt\FytSignTool;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ZmfPayApiTest extends TestCase
{
    public function testHadProduct()
    {
        (new Dotenv())->load(__DIR__.'/../../.env.local');
        $api = FytPay::getInstance();
        $ret = $api->balance();
        var_dump($ret);
//        $this->assertTrue($ret->isSuccess(), $ret->getMsg());
    }
}
