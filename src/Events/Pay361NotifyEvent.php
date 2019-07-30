<?php


namespace App\Events;


use Symfony\Contracts\EventDispatcher\Event;

class Pay361NotifyEvent extends Event
{
    protected $shopSubNumber;
    protected $subPaymentNumber;
    protected $actualMoney;
    protected $subMoney;
    protected $serviceCharge;
    protected $subState;
    protected $shopPhone;


    public function __construct()
    {
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

    /**
     * @return mixed
     */
    public function getSubPaymentNumber()
    {
        return $this->subPaymentNumber;
    }

    /**
     * @param mixed $subPaymentNumber
     */
    public function setSubPaymentNumber($subPaymentNumber): void
    {
        $this->subPaymentNumber = $subPaymentNumber;
    }

    /**
     * @return mixed
     */
    public function getActualMoney()
    {
        return $this->actualMoney;
    }

    /**
     * @param mixed $actualMoney
     */
    public function setActualMoney($actualMoney): void
    {
        $this->actualMoney = $actualMoney;
    }

    /**
     * @return mixed
     */
    public function getSubMoney()
    {
        return $this->subMoney;
    }

    /**
     * @param mixed $subMoney
     */
    public function setSubMoney($subMoney): void
    {
        $this->subMoney = $subMoney;
    }

    /**
     * @return mixed
     */
    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    /**
     * @param mixed $serviceCharge
     */
    public function setServiceCharge($serviceCharge): void
    {
        $this->serviceCharge = $serviceCharge;
    }

    /**
     * @return mixed
     */
    public function getSubState()
    {
        return $this->subState;
    }

    /**
     * @param mixed $subState
     */
    public function setSubState($subState): void
    {
        $this->subState = $subState;
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
}
