<?php


namespace by\component\fyt;


class FytChargeInfo
{
    protected $amount;
    protected $cporder;
    protected $name;
    protected $card;
    protected $evidence;
    protected $mark;
    protected $notifyurl;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param mixed $card
     */
    public function setCard($card): void
    {
        $this->card = $card;
    }

    /**
     * @return mixed
     */
    public function getEvidence()
    {
        return $this->evidence;
    }

    /**
     * @param mixed $evidence
     */
    public function setEvidence($evidence): void
    {
        $this->evidence = $evidence;
    }

    /**
     * @return mixed
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @param mixed $mark
     */
    public function setMark($mark): void
    {
        $this->mark = $mark;
    }

    /**
     * @return mixed
     */
    public function getNotifyurl()
    {
        return $this->notifyurl;
    }

    /**
     * @param mixed $notifyurl
     */
    public function setNotifyurl($notifyurl): void
    {
        $this->notifyurl = $notifyurl;
    }
}
