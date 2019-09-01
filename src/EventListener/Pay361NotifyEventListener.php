<?php


namespace App\EventListener;


use App\Entity\Pay361WithdrawOrder;
use App\Events\Pay361NotifyEvent;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use Doctrine\DBAL\LockMode;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Pay361NotifyEventListener implements EventSubscriberInterface
{
    protected $withdrawOrderService;
    protected $logger;

    public function __construct(Pay361WithdrawOrderServiceInterface $withdrawOrderService, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->withdrawOrderService = $withdrawOrderService;
    }

    public static function getSubscribedEvents()
    {
        return [
            Pay361NotifyEvent::class => [
                ['index', 10]
            ]
        ];
    }

    /**
     * 通知事件处理
     * @param Pay361NotifyEvent $event
     * @throws \Exception
     */
    public function index(Pay361NotifyEvent $event)
    {
        $orderNo = $event->getShopSubNumber();
        $order = $this->withdrawOrderService->info(['order_no' => $orderNo]);
        if (!$order instanceof Pay361WithdrawOrder) {
            $errMsg = 'invalid shop_sub_number '.$orderNo;
            $this->logger->error($errMsg);
            throw new \Exception($errMsg);
        }

        try {
            $this->withdrawOrderService->getEntityManager()->beginTransaction();
            $order = $this->withdrawOrderService->findById($order->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$order instanceof Pay361WithdrawOrder) {
                $this->withdrawOrderService->getEntityManager()->rollback();
                $errMsg = 'invalid shop_sub_number '.$orderNo;
                $this->logger->error($errMsg);
                return ;
            }
            if ($order->getState() === 'success') {
                $this->logger->info('order is success: '.$order->getOrderNo());
                $this->withdrawOrderService->getEntityManager()->rollback();
                return;
            }
            if ($order->getState() == $event->getSubState()) {
                // 订单状态一样不处理
                $this->logger->info('order state is same: '.$order->getState());
                return ;
            }
            // 更新状态
            $order->setState($event->getSubState());
            $order->setNotifyTime(time());
            $order->setPayOrderNo($event->getSubPaymentNumber());
            $order->setSubMoney($event->getSubMoney());
            $order->setServiceCharge($event->getServiceCharge());
            $order->setActualMoney($event->getActualMoney());
            $order->setNotifyShopPhone($event->getShopPhone());
            $order->setPaySign($event->getSign());

            $this->withdrawOrderService->flush($order);
            $this->withdrawOrderService->getEntityManager()->commit();
        } catch (\Exception $exception) {
            $this->withdrawOrderService->getEntityManager()->rollback();
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }
}

