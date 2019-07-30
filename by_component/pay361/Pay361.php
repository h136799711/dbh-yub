<?php


namespace by\component\pay361;

use by\component\http\HttpRequest;
use by\infrastructure\helper\CallResultHelper;

class Pay361
{
    const PassagewayCode001 = 'DF00001';//连连代付
    const PassagewayCode002 = 'DF00002';//沃代付
    const PassagewayCode003 = 'DF00003';//平安代付

    protected  $key;


    protected static $instance;

    private function __construct()
    {
        $this->key = '';
    }

    public static function getInstance(): self {

        if (self::$instance == null) {
            self::$instance = new Pay361();
        }

        return self::$instance;
    }

    public function setKey($key = '')
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

        $http = HttpRequest::newSession();
        $ret = $http->header('Content-Type', 'application/json')
            ->get($url, $params);
        if ($ret->success) {
            $content = $ret->getBody()->getContents();
            $arr = @json_decode($content, JSON_OBJECT_AS_ARRAY);
            if ($arr === false) {
                return CallResultHelper::fail('返回数据错误', $content);
            }
            if (array_key_exists('code', $arr)) {
                if ($arr['code'] == 1) {
                    return CallResultHelper::success($arr['data'], $arr['msg']);
                } else {
                    return CallResultHelper::fail($arr['msg'], $arr['data']);
                }
            }

            return CallResultHelper::success($content);
        }
        return CallResultHelper::fail($ret->getError());
    }
}
