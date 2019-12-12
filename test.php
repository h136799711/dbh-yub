<?php
class Demo{
    private $account = "1000010000"; //
    private $merkey = "abc123";
//http://60.190.138.134/payapi/Admin/PayApi/pay_order
    private $url = "http://39.98.134.65/payapi/Admin/PayApi/";
    //≤È—Ø
    public function queryorder() {
        $pbc = new Public1();

        $client['account'] = $this->account;
        $client['nonce_str'] = $pbc->create_nonce_str();
        $client['order_id'] = '201907010000001';
        $client['sign'] = $pbc->sign($client, $this->merkey);


        $ret = $pbc->curl_post($this->url."pay_query", $pbc->createLinkStr($client));

        $ret_json = json_decode($ret, 1);
		if (!$ret || !$ret_json){

            return;
        }
        if($ret_json['code'] == '00' && isset($ret_json['status'])){
            if ($ret_json['status'] == '2'){
            }else if ($ret_json['status'] == '0' || $ret_json['status'] == '3'){
            }else if ($ret_json['status'] == '4' || $ret_json['status'] == '6'){
            }else{
            }
        }else{
        }
    }

    public function order() {
        $pbc = new Public1();

        $client['account'] = $this->account;
        $client['nonce_str'] = $pbc->create_nonce_str();
        $client['order_id'] = 'T' . date('YmdHis'); //
        $client['payamount'] = 0.3; //
        $client['body'] = 'iphone';
        $client['cardtype'] = '1';
		$client['payaccount'] = '622909503021092881';
		$client['payname'] = '3333';
		$client['paybankname'] = '3333';
        $client['sign'] = $pbc->sign($client, $this->merkey);

        $ret = $pbc->curl_post($this->url."pay_order", $pbc->createLinkStr($client));
		var_dump($ret);
        $ret_json = json_decode($ret, 1);
		if (!$ret || !$ret_json){
            return;
        }
        if ($ret_json['code'] == '00'){
        }else if (isset($ret_json['code'])){
        }else{
        }
		return;


    }
    public function checkaccount() {
        $pbc = new Public1();

        $client['account'] = $this->account;
        $client['nonce_str'] = $pbc->create_nonce_str();
        $client['order_id'] = '2018010100001';
        $client['sign'] = $pbc->sign($client, $this->merkey);


        $ret = $pbc->curl_post($this->url."check_account", $pbc->createLinkStr($client));

        $ret_json = json_decode($ret, 1);
		if (!$ret || !$ret_json){
            //≥¨ ±ªÚ±®Œƒ¥ÌŒÛ£¨÷ÿ–¬≤È—Ø»∑»œ
            return;
        }
        if($ret_json['code'] == '00' ){
            echo $ret_json['amount']; //ø…¥˙∏∂”‡∂Ó£®‘™£©
        }else{
            //—È«© ß∞‹µ»
        }
    }


}

class Public1 {


    //…˙≥…∂©µ•∫≈
    public function makeOrderNo() {
        $rand = mt_rand(10000, 99999);
        list($usec, $sec) = explode(" ", microtime());
        $usec = substr($usec, 2, 6);
        $out_trade_no = $sec . $usec . $rand;
        return $out_trade_no;
    }

    //…˙≥…ÀÊª˙◊÷∑˚¥Æ
    public function create_nonce_str($pw_length = 24) {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';    //◊÷∑˚≥ÿ
        for ($i = 0; $i < $pw_length; $i++) {
            $key .= $pattern[mt_rand(0, 61)];
        }
        return $key;
    }

    //◊È◊∞«©√˚◊÷∑˚¥Æ
    public function createLinkStr($data) {
        ksort($data);
        $str = '';
        $i = 0;
        while (list($key, $val) = each($data)) {
            if (false === $this->checkEmpty($val) && "@" != substr($val, 0, 1)) {
                if ($i == 0) {
                    $str .= $key . '=' . $val;
                } else {
                    $str .= '&' . $key . '=' . $val;
                }
                $i++;
            }
        }
        unset($key, $value);
        return $str;
    }

    public function sign($data, $key) {
        return MD5($this->createLinkStr($data) . $key);
    }

    public function curl_post($url, $data) {
        $timeout = 65;
        var_dump($data);
        var_dump($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
//        if ($this->is_json($data)){
//            curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));  //¥´ ‰json±®Œƒ
//        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        if (strncasecmp($url, "https", 5) == 0) { //https
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // –≈»Œ»Œ∫Œ÷§ È
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // ºÏ≤È÷§ È÷– «∑Ò…Ë÷√”Ú√˚
            // curl_setopt($url, CURLOPT_HTTPHEADER, array('Expect:'));
//        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function checkEmpty($value) {
        if (!isset($value)) {
            return true;
        }
        if ($value === NUll) {
            return true;
        }
        if (trim($value) == '') {
            return true;
        }
        return false;
    }
    public function is_json($data){
        try{
            $flag = is_null(json_decode($data,1));
            if($flag){
                return false;
            }
            else{
                return true;
            }
        }
        catch (Exception $exc) {
            return false;
        }
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

$demo = new Demo();
$demo->order();
