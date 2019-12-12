<?php


namespace by\component\dypay;

use by\component\http\HttpRequest;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\ByEnv;

class DyPay
{

//39.98.134.65
    const DfCreateApiUrl = 'http://60.190.138.134/payapi/Admin/PayApi/pay_order';

    const DfQueryApiUrl = "http://60.190.138.134/payapi/Admin/PayApi/pay_query";

    protected $notifyUrl;
    protected $key;
    protected $account;

    protected $isDebug;


    protected static $instance;
    protected $logger;

    private function __construct()
    {
        $this->isDebug = false;
        $this->key = "";
        $this->account = "";
    }

    public static function getInstance(): self
    {
        if (self::$instance == null) {
            self::$instance = new DyPay();
            self::$instance->setKey(ByEnv::get('DYPAY_KEY'));
            self::$instance->setAccount(ByEnv::get('DYPAY_ACCOUNT'));
            self::$instance->setNotifyUrl(ByEnv::get('DYPAY_NOTIFY_URL'));
            if (empty(self::$instance->key)) {
                throw new \Exception('DYPAY_KEY must set');
            }
            if (empty(self::$instance->account)) {
                throw new \Exception('DYPAY Account must set');
            }
        }
        return self::$instance;
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

    /**
     * 0-代付申请成功
     * 2-代付成功
     * 3-正在代付
     * 5-人工确认
     * 4、6失败，
     * @param $orderId
     * @return \by\infrastructure\base\CallResult
     */
    public function query($orderId) {
        $params = [
            'account' => $this->account,
            'order_id' => $orderId,
            'nonce_str' => 'yub'.time(),
        ];
        $ret =  $this->getRequest(self::DfQueryApiUrl, $params);
        if ($ret->isSuccess()) {
            $data = $ret->getData();
            if (array_key_exists('status', $data)) {
                return CallResultHelper::success($data['status']);
            } else {
                return CallResultHelper::fail('缺少status参数', $data);
            }
        }
        return $ret;
    }

    /**
     * @param $orderId
     * @param string $amount 元
     * @param $accNo
     * @param $name
     * @param $bankName
     * @param string $body
     * @return \by\infrastructure\base\CallResult
     */
    public function pay($orderId, $amount, $accNo, $name, $bankName, $body = "baoshi")
    {
        $params = [
            'account' => $this->account,
            'order_id' => $orderId,
            'nonce_str' => 'yub'.time(),
            'payamount' => $amount,
            'body' => $body,
            'cardtype' => '1',
            'payaccount' => $accNo,
            'payname' => $name,
            'paybankname' => $bankName
        ];

        return $this->getRequest(self::DfCreateApiUrl, $params);
    }

    public function sign($params, $key) {
        ksort($params, SORT_ASC);
        return md5(http_build_query($params).$key);
    }

    public function getRequest($url, $params)
    {
        $sign = $this->sign($params, $this->key);
        $params['sign'] = $sign;
        if ($this->isDebug) {
            var_dump($params);
        }
        $strParams = http_build_query($params);

        if ($this->isDebug) {
            var_dump($strParams);
        }

        $http = HttpRequest::newSession();
        $ret = $http
//            ->header('Content-Type', 'application/x-www-form-urlencoded')
//            ->timeout(60 * 1000, 60 * 1000)
            ->params($strParams)
            ->post($url)
        ;
        if ($ret->success) {
            $content = $ret->getBody()->getContents();

            if ($this->isDebug) {
                var_dump('HttpReturnContent=> ' . $content);
            }
            $arr = @json_decode($content, JSON_OBJECT_AS_ARRAY);
            if (empty($arr)) {
                return CallResultHelper::fail('返回数据错误', $content);
            }

            if ($arr['code'] == '00') {
                return CallResultHelper::success($arr);
            } else {
                return CallResultHelper::fail($arr['msg'], $arr);
            }
        }
        return CallResultHelper::fail($ret->getError());
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
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account): void
    {
        $this->account = $account;
    }
}
