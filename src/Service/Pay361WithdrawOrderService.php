<?php


namespace App\Service;


use App\Repository\Pay361WithdrawOrderRepository;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use Dbh\SfCoreBundle\Common\BaseService;

class Pay361WithdrawOrderService extends BaseService implements Pay361WithdrawOrderServiceInterface
{
    public function __construct(Pay361WithdrawOrderRepository $repository)
    {
        $this->repo = $repository;
    }
}
