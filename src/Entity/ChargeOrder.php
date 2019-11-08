<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChargeOrderRepository")
 */
class ChargeOrder extends BaseEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $orderNo;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=3)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $cardNo;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $evidence;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $mark;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $notifyUrl;

    /**
     * @ORM\Column(type="bigint")
     */
    private $createTime;

    /**
     * @ORM\Column(type="bigint")
     */
    private $updateTime;

    /**
     * @ORM\Column(type="bigint")
     */
    private $notifyTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $notifyStatus;

    public function __construct()
    {
        parent::__construct();
        $this->setNotifyStatus(0);
        $this->setNotifyTime(0);
        $this->setMark('');
        $this->setNotifyUrl('');
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

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCardNo(): ?string
    {
        return $this->cardNo;
    }

    public function setCardNo(string $cardNo): self
    {
        $this->cardNo = $cardNo;

        return $this;
    }

    public function getEvidence(): ?string
    {
        return $this->evidence;
    }

    public function setEvidence(string $evidence): self
    {
        $this->evidence = $evidence;

        return $this;
    }

    public function getMark(): ?string
    {
        return $this->mark;
    }

    public function setMark(string $mark): self
    {
        $this->mark = $mark;

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

    public function getNotifyTime(): ?int
    {
        return $this->notifyTime;
    }

    public function setNotifyTime(int $notifyTime): self
    {
        $this->notifyTime = $notifyTime;

        return $this;
    }

    public function getNotifyStatus(): ?int
    {
        return $this->notifyStatus;
    }

    public function setNotifyStatus(int $notifyStatus): self
    {
        $this->notifyStatus = $notifyStatus;

        return $this;
    }
}
