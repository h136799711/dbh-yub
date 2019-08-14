<?php


namespace by\component\pay361;


class PayInfo
{

    protected $shopPhone;
    protected $bankCardNumber;
    protected $bankName;
    protected $registBankName;
    protected $money;
    protected $notifyUrl;
    protected $passagewayCode;
    protected $cardUserName;
    protected $shopSubNumber;

    public function toArray() {
        return [
            'bank_card_number' => $this->getBankCardNumber(),
            'bank_name' => $this->getBankName(),
            'card_user_name' => $this->getCardUserName(),
            'money' => $this->getMoney(),
            'notify_url' => $this->getNotifyUrl(),
            'passageway_code' => $this->getPassagewayCode(),
            'regist_bank_name' => $this->getRegistBankName(),
            'shop_phone' => $this->getShopPhone(),
            'shop_sub_number' => $this->getShopSubNumber()
        ];
    }

    /**
     * @return mixed
     */
    public function getShopPhone()
    {
        return $this->shopPhone;
    }

    /**
     * @param mixed $shopPhone
     */
    public function setShopPhone($shopPhone): void
    {
        $this->shopPhone = $shopPhone;
    }

    /**
     * @return mixed
     */
    public function getBankCardNumber()
    {
        return $this->bankCardNumber;
    }

    /**
     * @param mixed $bankCardNumber
     */
    public function setBankCardNumber($bankCardNumber): void
    {
        $this->bankCardNumber = $bankCardNumber;
    }

    /**
     * @return mixed
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param mixed $bankName
     */
    public function setBankName($bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return mixed
     */
    public function getRegistBankName()
    {
        return $this->registBankName;
    }

    /**
     * @param mixed $registBankName
     */
    public function setRegistBankName($registBankName): void
    {
        $this->registBankName = $registBankName;
    }

    /**
     * @return mixed
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * @param mixed $money
     */
    public function setMoney($money): void
    {
        $this->money = $money;
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
    public function getPassagewayCode()
    {
        return $this->passagewayCode;
    }

    /**
     * @param mixed $passagewayCode
     */
    public function setPassagewayCode($passagewayCode): void
    {
        $this->passagewayCode = $passagewayCode;
    }

    /**
     * @return mixed
     */
    public function getCardUserName()
    {
        return $this->cardUserName;
    }

    /**
     * @param mixed $cardUserName
     */
    public function setCardUserName($cardUserName): void
    {
        $this->cardUserName = $cardUserName;
    }

    /**
     * @return mixed
     */
    public function getShopSubNumber()
    {
        return $this->shopSubNumber;
    }

    /**
     * @param mixed $shopSubNumber
     */
    public function setShopSubNumber($shopSubNumber): void
    {
        $this->shopSubNumber = $shopSubNumber;
    }


//        shop_phone	是	 商户手机号

//（商户在我方申请的商户号）
//bank_card_number	是	所属银行
//bank_name	是	交易金额，保留2位小数
//regist_bank	否	开户银行所在地区
//regist_bank_name	否	开户银行
//city_number	否	城市编码
//money	是	提现金额
//passageway_code	是	通道代码
//card_user_name	是	持卡人姓名
//cert_number	否	持卡人身份证号码
//shop_sub_number	是	商户代付单号
//notify_url	是	服务器回调地址用于接收回调，回调处理参考文档回调部分
}
