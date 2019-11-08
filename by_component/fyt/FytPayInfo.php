<?php


namespace by\component\fyt;


class FytPayInfo
{
    protected $amount;
    protected $cporder;
    protected $payType;
    protected $cardNo;
    protected $cardName;
    protected $bankName;
    protected $notifyUrl;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getCporder()
    {
        return $this->cporder;
    }

    /**
     * @param mixed $cporder
     */
    public function setCporder($cporder): void
    {
        $this->cporder = $cporder;
    }

    /**
     * @return mixed
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * @param mixed $payType
     */
    public function setPayType($payType): void
    {
        $this->payType = $payType;
    }

    /**
     * @return mixed
     */
    public function getCardNo()
    {
        return $this->cardNo;
    }

    /**
     * @param mixed $cardNo
     */
    public function setCardNo($cardNo): void
    {
        $this->cardNo = $cardNo;
    }

    /**
     * @return mixed
     */
    public function getCardName()
    {
        return $this->cardName;
    }

    /**
     * @param mixed $cardName
     */
    public function setCardName($cardName): void
    {
        $this->cardName = $cardName;
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

}
