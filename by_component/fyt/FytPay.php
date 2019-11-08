<?php


namespace by\component\fyt;

use by\component\encrypt\rsa\Rsa;
use by\component\http\HttpRequest;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\ByEnv;

class FytPay
{
    //1.代付下单请求地址
    //https://f.dcdmef.cn/DF/gateway.do
    //2.代付记录查询接口
    //https://f.dcdmef.cn/DF/getstatus.do
    //3.账户余额查询接口
    //https://f.dcdmef.cn/DF/getamount.do
    //4.代付充值接口
    //https://f.dcdmef.cn/DF/charge.do
    //5.获取充值卡号信息接口
    //https://f.dcdmef.cn/DF/getChargeInfo.do

    const DfCreateApiUrl = 'https://f.dcdmef.cn/DF/gateway.do';
    const DfBalanceApiUrl = 'https://f.dcdmef.cn/DF/getamount.do';
    const DfInfoApiUrl = 'https://f.dcdmef.cn/DF/getstatus.do';
    const DfChargeApiUrl = 'https://f.dcdmef.cn/DF/charge.do';
    const DfChargeInfoApiUrl = 'https://f.dcdmef.cn/DF/getChargeInfo.do';

    protected $sysPublicRsaKey;
    protected $userPrivateRsaKey;
    protected $mchid;

    protected $isDebug;


    protected static $instance;
    protected $logger;

    private function __construct()
    {
        $this->isDebug = false;
    }

    public static function getInstance(): self
    {
        if (self::$instance == null) {
            self::$instance = new FytPay();
            self::$instance->setUserPrivateRsaKey(Rsa::formatPrivateText(ByEnv::get('FYT361_USER_PRI_KEY')));
            self::$instance->setSysPublicRsaKey(Rsa::formatPublicText(ByEnv::get('FYT361_SYS_PUB_KEY')));
            self::$instance->setMchid(ByEnv::get('FYT361_MCHID'));

            if (empty(self::$instance->userPrivateRsaKey)) {
                throw new \Exception('FYT361_USER_PRI_KEY must set');
            }
            if (empty(self::$instance->sysPublicRsaKey)) {
                throw new \Exception('FYT361_SYS_PUB_KEY must set');
            }
            if (empty(self::$instance->mchid)) {
                throw new \Exception('FYT361_MCHID must set');
            }
        }
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getMchid()
    {
        return $this->mchid;
    }

    /**
     * @param mixed $mchid
     */
    public function setMchid($mchid): void
    {
        $this->mchid = $mchid;
    }

    public function openDebug(): self
    {
        $this->isDebug = true;
        return $this;
    }

    public function closeDebug(): self
    {
        $this->isDebug = false;
        return $this;
    }

    public function charge(FytChargeInfo $chargeInfo) {
        $params = [
            'mchid' => $this->getMchid(),
            'amount' => $chargeInfo->getAmount(),
            'cporder' => $chargeInfo->getCporder(),
            'name' => $chargeInfo->getName(),
            'evidence' => $chargeInfo->getEvidence(),
            'mark' => $chargeInfo->getMark(),
            'notifyurl' => $chargeInfo->getNotifyurl(),
            'card' => $chargeInfo->getCard(),
        ];
        $ret = $this->getRequest(self::DfChargeApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        if (!array_key_exists('status', $data)) {
            return CallResultHelper::fail('返回结果格式错误', $data);
        }
        if ($data['status'] == '400') {
            return CallResultHelper::success($data);
        } else {
            return CallResultHelper::fail($data['msg'], $data);
        }
    }

    public function chargeInfo() {

        $params = [
            'mchid' => $this->getMchid()
        ];
        $ret = $this->getRequest(self::DfChargeInfoApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        if (!array_key_exists('status', $data)) {
            return CallResultHelper::fail('返回结果格式错误', $data);
        }
        if ($data['status'] == '500') {
            return CallResultHelper::success($data['amount']);
        } else {
            return CallResultHelper::fail($data['msg'], $data);
        }
    }

    public function pay(FytPayInfo $fytPayInfo)
    {
        $params = [
            'mchid' => $this->getMchid(),
            'cporder' => $fytPayInfo->getCporder(),
            'notifyUrl' => $fytPayInfo->getNotifyUrl(),
            'amount' => $fytPayInfo->getAmount(),
            'cardNo' => $fytPayInfo->getCardNo(),
            'cardName' => $fytPayInfo->getCardName(),
            'bankName' => $fytPayInfo->getBankName(),
        ];
        if ($fytPayInfo->getPayType()) {
            $params['payType'] = $fytPayInfo->getPayType();
        }
        $ret = $this->getRequest(self::DfCreateApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        if (!array_key_exists('status', $data)) {
            return CallResultHelper::fail('返回结果格式错误', $data);
        }
        $statusMsg = [
            '100' => '代付请求成功',
            '101' => '没有配置代付通道，请联系商务',
            '102' => '代付请求支付失败！',
            '103' => '签名错误',
            '104' => '无效商户ID',
            '105' => 'IP鉴权失败',
            '106' => '账户金额不足',
            '107' => '账号代付权限关闭',
            '108' => '订单号重复',
            '109' => '代付请求金额错误',
            '201' => '商户ID错误!',
            '200' => '查询成功!',
            '202' => '签名错误!',
            '203' => 'IP非法',
            '301' => '商户ID错误!',
            '302' => '无代付订单记录！',
            '303' => '签名错误!',
            '304' => 'IP非法',
            '400' => '充值提交成功！',
            '401' => '商户ID错误!',
            '402' => 'IP鉴权失败！',
            '403' => '签名错误!',
            '500' => '获取账号成功！',
            '501' => '商户ID错误!',
            '502' => '无充值账号信息！'];
        $msg = array_key_exists($data['status'], $statusMsg) ? $statusMsg[$data['status']] : $data['msg'];
        if ($data['status'] == '100') {
            return CallResultHelper::success($data['status'], $msg);
        } else {
            return CallResultHelper::fail($msg, $data);
        }
    }

    /**
     * @param $cporder
     * @return \by\infrastructure\base\CallResult
     */
    public function info($cporder)
    {
        $params = [
            'mchid' => $this->getMchid(),
            'cporder' => $cporder
        ];
        $ret = $this->getRequest(self::DfInfoApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        if (!array_key_exists('status', $data)) {
            return CallResultHelper::fail('返回结果格式错误', $data);
        }
//        300表示代付下发成功，305表示处理中，306表示下发失败
        $statusMsg = [
            '300' => '下发成功',
            '305' => '处理中',
            '306' => '下发失败',
        ];
        $msg = array_key_exists($data['status'], $statusMsg) ? $statusMsg[$data['status']] : $data['msg'];
        return CallResultHelper::success($data['status'], $msg);
    }

    public function balance()
    {
        $params = [
            'mchid' => $this->getMchid()
        ];
        $ret = $this->getRequest(self::DfBalanceApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        if (!array_key_exists('status', $data)) {
            return CallResultHelper::fail('返回结果格式错误', $data);
        }
        if ($data['status'] == '200') {
            return CallResultHelper::success($data['amount']);
        } else {
            return CallResultHelper::fail($data['msg'], $data);
        }
    }

    /**
     * @return mixed
     */
    public function getSysPublicRsaKey()
    {
        return $this->sysPublicRsaKey;
    }

    /**
     * @param mixed $sysPublicRsaKey
     */
    public function setSysPublicRsaKey($sysPublicRsaKey): void
    {
        $this->sysPublicRsaKey = $sysPublicRsaKey;
    }

    /**
     * @return mixed
     */
    public function getUserPrivateRsaKey()
    {
        return $this->userPrivateRsaKey;
    }

    /**
     * @param mixed $userPrivateRsaKey
     */
    public function setUserPrivateRsaKey($userPrivateRsaKey): void
    {
        $this->userPrivateRsaKey = $userPrivateRsaKey;
    }

    public function getRequest($url, $params)
    {

        $sign = FytSignTool::sign($params, $this->userPrivateRsaKey);
        $params['sign'] = $sign;
        if ($this->isDebug) {
            var_dump('params');
            var_dump($params);
        }
//        if(!empty($params))
//        {
//            if(strpos($url, '?'))
//            {
//                $url .= '&';
//            }
//            else
//            {
//                $url .= '?';
//            }
//            $notifyUrl = '';
//            if (array_key_exists('notify_url', $params)) {
//                $notifyUrl = $params['notify_url'];
//                unset($params['notify_url']);
//            }
//            $url .= http_build_query($params, '', '&');
//            $url .= '&notify_url='.$notifyUrl;
//        }
//        var_dump($url);
//        exit;
        $http = HttpRequest::newSession();
        $ret = $http->header('Content-Type', 'application/json')
            ->timeout(60 * 1000, 60 * 1000)
            ->retry(2)
            ->params(json_encode($params))
            ->post($url);
        if ($ret->success) {
            $content = $ret->getBody()->getContents();

            if ($this->isDebug) {
                var_dump('HttpReturnContent=> ' . $content);
            }
            $arr = @json_decode($content, JSON_OBJECT_AS_ARRAY);
            if (empty($arr)) {
                return CallResultHelper::fail('返回数据错误', $content);
            }

            return CallResultHelper::success($arr);
        }
        return CallResultHelper::fail($ret->getError());
    }
}
