<?php


namespace by\component\pay361;

use by\component\http\HttpRequest;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\ByEnv;

class Pay361
{
    const PassagewayCode001 = 'DF00001';//连连代付
    const PassagewayCode002 = 'DF00002';//沃代付
    const PassagewayCode003 = 'DF00003';//平安代付

    protected  $key;
    protected $isDebug;


    protected static $instance;

    private function __construct()
    {
        $this->key = '';
        $this->isDebug = false;
    }

    public static function getInstance(): self {

        if (self::$instance == null) {
            self::$instance = new Pay361();
            self::$instance->setKey(ByEnv::get('PAY361_KEY'));
            if (empty(self::$instance->key)) {
                throw new \Exception('pay361 key must set');
            }
        }

        return self::$instance;
    }

    public function openDebug(): self {
        $this->isDebug = true;
        return $this;
    }

    public function closeDebug(): self {
        $this->isDebug = false;
        return $this;
    }


    public function setKey($key = ''): self
    {
        $this->key = $key;
        return $this;
    }


    public function orderQuery($shop_phone = '', $shop_sub_number = '')
    {
        $url = 'http://361pay.qu68s.cn/api/subpayment/subPaymentQuery';
        $params = [
            'shop_phone' => $shop_phone,
            'shop_sub_number' => $shop_sub_number
        ];
        return $this->getRequest($url, $params);
    }

    public function balance($shop_phone = '', $passageway_code = '') {
        $url = 'http://361pay.qu68s.cn/api/pay/checkPassagewayBalance';
        $params = [
            'shop_phone' => $shop_phone,
            'passageway_code' => $passageway_code
        ];

        return $this->getRequest($url, $params);
    }

    public function pay(PayInfo $payInfo) {
        $url = "http://361pay.qu68s.cn/api/subpayment/subPaymentInterface";
        $params = $payInfo->toArray();
        return $this->getRequest($url, $params);
    }

    public function getRequest($url, $params) {

        $sign = SignTool::sign($params, $this->key);
        $params['sign'] = $sign;
        if ($this->isDebug) {
            var_dump('params');
            var_dump($params);
        }

        $http = HttpRequest::newSession();
        $ret = $http->header('Content-Type', 'application/json')
            ->timeout(10 * 1000, 10 * 1000)
            ->retry(2)
            ->get($url, $params);
        if ($ret->success) {
            $content = $ret->getBody()->getContents();

            if ($this->isDebug) {
                var_dump('HttpReturnContent=> '.$content);
            }
            $arr = @json_decode($content, JSON_OBJECT_AS_ARRAY);
            if ($arr === false) {
                return CallResultHelper::fail('返回数据错误', $content);
            }
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 1) {
                    $msg = '---';
                    if (array_key_exists('msg', $arr)) {
                        $msg = $arr['msg'];
                    } elseif (array_key_exists('message', $arr)) {
                        $msg = $arr['message'];
                    }
                    return CallResultHelper::success($arr['data'], $msg);
                } else {
                    return CallResultHelper::fail($arr['msg'], $arr['data']);
                }
            }

            return CallResultHelper::success($content);
        }
        return CallResultHelper::fail($ret->getError());
    }
}
