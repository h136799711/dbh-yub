<?php


namespace App\Service;


use App\Repository\ChargeOrderRepository;
use App\ServiceInterface\ChargeOrderServiceInterface;
use Dbh\SfCoreBundle\Common\BaseService;

class ChargeOrderService extends BaseService implements ChargeOrderServiceInterface
{
    public function __construct(ChargeOrderRepository $chargeOrderRepository)
    {
        $this->repo = $chargeOrderRepository;
    }
}
