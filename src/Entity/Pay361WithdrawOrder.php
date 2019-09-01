<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pay361WithdrawOrderRepository")
 */
class Pay361WithdrawOrder extends BaseEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $orderNo;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $shopPhone;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $bankCardNumber;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $bankName;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $registBank;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $registBankName;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $cityNumber;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $money;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $passagewayCode;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $cardUserName;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $certNumber;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $notifyUrl;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $sign;

    /**
     * @ORM\Column(type="bigint")
     */
    private $createTime;

    /**
     * @ORM\Column(type="bigint")
     */
    private $updateTime;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $payOrderNo;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=4)
     */
    private $actualMoney;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=4)
     */
    private $subMoney;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=4)
     */
    private $serviceCharge;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $paySign;

    /**
     * @ORM\Column(type="bigint")
     */
    private $notifyTime;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $notifyShopPhone;

    public function __construct()
    {
        parent::__construct();
        $this->setState('');
        $this->setPaySign('');
        $this->setActualMoney(0);
        $this->setPayOrderNo('');
        $this->setServiceCharge(0);
        $this->setSubMoney(0);
        $this->setNotifyTime(0);
        $this->setNotifyShopPhone('');

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNo(): ?string
    {
        return $this->orderNo;
    }

    public function setOrderNo(string $orderNo): self
    {
        $this->orderNo = $orderNo;

        return $this;
    }

    public function getShopPhone(): ?string
    {
        return $this->shopPhone;
    }

    public function setShopPhone(string $shopPhone): self
    {
        $this->shopPhone = $shopPhone;

        return $this;
    }

    public function getBankCardNumber(): ?string
    {
        return $this->bankCardNumber;
    }

    public function setBankCardNumber(string $bankCardNumber): self
    {
        $this->bankCardNumber = $bankCardNumber;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getRegistBank(): ?string
    {
        return $this->registBank;
    }

    public function setRegistBank(string $registBank): self
    {
        $this->registBank = $registBank;

        return $this;
    }

    public function getRegistBankName(): ?string
    {
        return $this->registBankName;
    }

    public function setRegistBankName(string $registBankName): self
    {
        $this->registBankName = $registBankName;

        return $this;
    }

    public function getCityNumber(): ?string
    {
        return $this->cityNumber;
    }

    public function setCityNumber(string $cityNumber): self
    {
        $this->cityNumber = $cityNumber;

        return $this;
    }

    public function getMoney(): ?string
    {
        return $this->money;
    }

    public function setMoney(string $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getPassagewayCode(): ?string
    {
        return $this->passagewayCode;
    }

    public function setPassagewayCode(string $passagewayCode): self
    {
        $this->passagewayCode = $passagewayCode;

        return $this;
    }

    public function getCardUserName(): ?string
    {
        return $this->cardUserName;
    }

    public function setCardUserName(string $cardUserName): self
    {
        $this->cardUserName = $cardUserName;

        return $this;
    }

    public function getCertNumber(): ?string
    {
        return $this->certNumber;
    }

    public function setCertNumber(string $certNumber): self
    {
        $this->certNumber = $certNumber;

        return $this;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function setNotifyUrl(string $notifyUrl): self
    {
        $this->notifyUrl = $notifyUrl;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(string $sign): self
    {
        $this->sign = $sign;

        return $this;
    }

    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    public function setCreateTime(int $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    public function setUpdateTime(int $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPayOrderNo(): ?string
    {
        return $this->payOrderNo;
    }

    public function setPayOrderNo(string $payOrderNo): self
    {
        $this->payOrderNo = $payOrderNo;

        return $this;
    }

    public function getActualMoney(): ?string
    {
        return $this->actualMoney;
    }

    public function setActualMoney(string $actualMoney): self
    {
        $this->actualMoney = $actualMoney;

        return $this;
    }

    public function getSubMoney()
    {
        return $this->subMoney;
    }

    public function setSubMoney($subMoney): self
    {
        $this->subMoney = $subMoney;

        return $this;
    }

    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    public function setServiceCharge($serviceCharge): self
    {
        $this->serviceCharge = $serviceCharge;

        return $this;
    }

    public function getPaySign(): ?string
    {
        return $this->paySign;
    }

    public function setPaySign(string $paySign): self
    {
        $this->paySign = $paySign;

        return $this;
    }

    public function getNotifyTime(): ?int
    {
        return $this->notifyTime;
    }

    public function setNotifyTime(int $notifyTime): self
    {
        $this->notifyTime = $notifyTime;

        return $this;
    }

    public function getNotifyShopPhone(): ?string
    {
        return $this->notifyShopPhone;
    }

    public function setNotifyShopPhone(string $notifyShopPhone): self
    {
        $this->notifyShopPhone = $notifyShopPhone;

        return $this;
    }
}
