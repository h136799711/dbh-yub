<?php


namespace by\component\wmpay;

use by\component\http\HttpRequest;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\ByEnv;

class WmPay
{


    const DfCreateApiUrl = 'http://api.kvqym.com/resolve/daifu';

    protected $notifyUrl;
    protected $key;
    protected $mchid;
    protected $sendIp;

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
            self::$instance = new WmPay();
            self::$instance->sendIp = ByEnv::get('WMPAY_IP');
            self::$instance->setKey(ByEnv::get('WMPAY_KEY'));
            self::$instance->setMchid(ByEnv::get('WMPAY_MCHID'));
            self::$instance->setNotifyUrl(ByEnv::get('WMPAY_NOTIFY_URL'));
            if (empty(self::$instance->key)) {
                throw new \Exception('WMPAY_KEY must set');
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param mixed $notifyUrl
     */
    public function setNotifyUrl($notifyUrl): void
    {
        $this->notifyUrl = $notifyUrl;
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

    public function pay($orderNo, $amount, $accNo, $customerNm, $bankId, $subject = "升级VIP", $body = "升级VIP")
    {
        $params = [
            'signMethod' => 'MD5',
            'sendTime' => date("YmdHis"),
            'merchantId' => $this->mchid,
            'merOrderId' => $orderNo,
            'accNo' => $accNo,
            'sendIp' => $this->sendIp,
            'customerNm' => $customerNm,
            'txnAmt' => $amount, // 单位 分
            'backUrl' => $this->notifyUrl,
            'subject' => $subject,
            'body' => $body,
            'ppFlag' => "01",
            'gateway' => 'daifu',
            'bankId' => $bankId
        ];

        $ret = $this->getRequest(self::DfCreateApiUrl, $params);
        if ($ret->isFail()) return $ret;
        $data = $ret->getData();
        if (!array_key_exists('code', $data)) {
            return CallResultHelper::fail('返回结果格式错误', $data);
        }
        if ($data['code'] == '1111') {
            return CallResultHelper::success($data);
        } else {
            return CallResultHelper::fail('代付失败', $data);
        }
    }


    public function getRequest($url, $params)
    {
        $sign = WmPaySignTool::sign($params, $this->key);

        $params['subject'] = base64_encode($params['subject']);
        $params['body'] = base64_encode($params['body']);
        $params['signature'] = $sign;
        if ($this->isDebug) {
            var_dump('params');
            var_dump($params);
        }

        $http = HttpRequest::newSession();
        $ret = $http
            ->header('Content-Type', 'application/x-www-form-urlencoded')
            ->timeout(60 * 1000, 60 * 1000)
            ->retry(2)
            ->post($url, ($params));
        if ($ret->success) {
            $content = $ret->getBody()->getContents();

            if ($this->isDebug) {
                var_dump('HttpReturnContent=> ' . $content);
            }
            $arr = @json_decode($content, JSON_OBJECT_AS_ARRAY);
            if (empty($arr)) {
                return CallResultHelper::fail('返回数据错误', $content);
            }
            if (!array_key_exists('success', $arr)) {
                return CallResultHelper::fail('返回数据缺少success字段', $content);
            }
            if ($arr['success'] == 1) {
                return CallResultHelper::success($arr);
            } else {
                return CallResultHelper::fail($arr['msg'], $arr);
            }
        }
        return CallResultHelper::fail($ret->getError());
    }
}
