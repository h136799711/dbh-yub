<?php


namespace App\Events;


use Symfony\Contracts\EventDispatcher\Event;

class PayfytChargeNotifyEvent extends Event
{
    protected $mchid;
    protected $amount;
    protected $cporder;
    protected $status;

    protected $sign;


    public function __construct()
    {
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param mixed $sign
     */
    public function setSign($sign): void
    {
        $this->sign = $sign;
    }
}
