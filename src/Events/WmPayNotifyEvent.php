<?php


namespace App\Events;


use by\component\wmpay\NotifyParams;
use Symfony\Contracts\EventDispatcher\Event;

class WmPayNotifyEvent extends Event
{
    /**
     * @var NotifyParams
     */
    protected $params;


    public function __construct(NotifyParams $params)
    {
        $this->params = $params;
    }

    /**
     * @return NotifyParams
     */
    public function getParams(): NotifyParams
    {
        return $this->params;
    }

    /**
     * @param NotifyParams $params
     */
    public function setParams(NotifyParams $params): void
    {
        $this->params = $params;
    }
}
