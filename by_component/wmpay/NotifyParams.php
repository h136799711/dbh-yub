<?php


namespace by\component\wmpay;


class NotifyParams
{
    protected $success;
    protected $respCode;
    protected $respMsg;
    protected $merchantId;
    protected $merOrderId;
    protected $txnAmt;
    protected $signature;
    protected $signMethod;

    public function __construct($data)
    {
        if (is_array($data)) {
            array_key_exists('signMethod', $data) && $this->setSignMethod($data['signMethod']);
            array_key_exists('txnAmt', $data) && $this->setTxnAmt($data['txnAmt']);
            array_key_exists('signature', $data) && $this->setSignature($data['signature']);
            array_key_exists('respMsg', $data) && $this->setRespMsg($data['respMsg']);
            array_key_exists('merchantId', $data) && $this->setMerchantId($data['merchantId']);
            array_key_exists('merOrderId', $data) && $this->setMerOrderId($data['merOrderId']);
            array_key_exists('success', $data) && $this->setSuccess($data['success']);
            array_key_exists('respCode', $data) && $this->setRespCode($data['respCode']);
        }
    }

    public function verifySign($key) {
        $data = [
            'txnAmt' => $this->txnAmt,
            'respMsg' => $this->respMsg,
            'merchantId' => $this->merchantId,
            'merOrderId' => $this->merOrderId,
            'success' => $this->success,
            'respCode' => $this->respCode
        ];
        return WmPaySignTool::verifySign($data, $this->signature, $key);
    }

    public function isSuccess() {
        return intval($this->success) == 1;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess($success): void
    {
        $this->success = $success;
    }

    /**
     * @return mixed
     */
    public function getRespCode()
    {
        return $this->respCode;
    }

    /**
     * @param mixed $respCode
     */
    public function setRespCode($respCode): void
    {
        $this->respCode = $respCode;
    }

    /**
     * @return mixed
     */
    public function getRespMsg()
    {
        return $this->respMsg;
    }

    /**
     * @param mixed $respMsg
     */
    public function setRespMsg($respMsg): void
    {
        $this->respMsg = $respMsg;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param mixed $merchantId
     */
    public function setMerchantId($merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return mixed
     */
    public function getMerOrderId()
    {
        return $this->merOrderId;
    }

    /**
     * @param mixed $merOrderId
     */
    public function setMerOrderId($merOrderId): void
    {
        $this->merOrderId = $merOrderId;
    }

    /**
     * @return mixed
     */
    public function getTxnAmt()
    {
        return $this->txnAmt;
    }

    /**
     * @param mixed $txnAmt
     */
    public function setTxnAmt($txnAmt): void
    {
        $this->txnAmt = $txnAmt;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     */
    public function setSignature($signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @return mixed
     */
    public function getSignMethod()
    {
        return $this->signMethod;
    }

    /**
     * @param mixed $signMethod
     */
    public function setSignMethod($signMethod): void
    {
        $this->signMethod = $signMethod;
    }
}
