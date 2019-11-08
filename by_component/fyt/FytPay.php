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
    const DfInfoApiUrl =  'https://f.dcdmef.cn/DF/getstatus.do';
    const DfChargeApiUrl =  'https://f.dcdmef.cn/DF/charge.do';
    const DfChargeInfoApiUrl =  'https://f.dcdmef.cn/DF/getChargeInfo.do';

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

    public static function getInstance(): self {
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

    public function openDebug(): self {
        $this->isDebug = true;
        return $this;
    }

    public function closeDebug(): self {
        $this->isDebug = false;
        return $this;
    }

    public function balance() {
        $params = [
            'mchid' => $this->getMchid()
        ];
        $ret = $this->getRequest(self::DfBalanceApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        var_dump($data);
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

    public function getRequest($url, $params) {

        $sign = FytSignTool::sign($params, $this->userPrivateRsaKey);
        $params['sign'] = $sign;
        if ($this->isDebug) {
            var_dump('params');
            var_dump($params);
        }
        if(!empty($params))
        {
            if(strpos($url, '?'))
            {
                $url .= '&';
            }
            else
            {
                $url .= '?';
            }
            $notifyUrl = '';
            if (array_key_exists('notify_url', $params)) {
                $notifyUrl = $params['notify_url'];
                unset($params['notify_url']);
            }
            $url .= http_build_query($params, '', '&');
            $url .= '&notify_url='.$notifyUrl;
        }
//        var_dump($url);
//        exit;
        $http = HttpRequest::newSession();
        $ret = $http->header('Content-Type', 'application/json')
            ->timeout(60 * 1000, 60 * 1000)
            ->retry(2)
            ->get($url);
        if ($ret->success) {
            $content = $ret->getBody()->getContents();

            if ($this->isDebug) {
                var_dump('HttpReturnContent=> '.$content);
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
