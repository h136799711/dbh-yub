<?php


namespace App\EventListener;


use App\Entity\ChargeOrder;
use App\Entity\Pay361WithdrawOrder;
use App\Events\Pay361NotifyEvent;
use App\Events\PayfytChargeNotifyEvent;
use App\Events\PayfytNotifyEvent;
use App\ServiceInterface\ChargeOrderServiceInterface;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use Doctrine\DBAL\LockMode;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Pay361NotifyEventListener implements EventSubscriberInterface
{
    protected $withdrawOrderService;
    protected $chargeOrderService;
    protected $logger;

    public function __construct(
        ChargeOrderServiceInterface $chargeOrderService,
        Pay361WithdrawOrderServiceInterface $withdrawOrderService, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->chargeOrderService = $chargeOrderService;
        $this->withdrawOrderService = $withdrawOrderService;
    }

    public static function getSubscribedEvents()
    {
        return [
            Pay361NotifyEvent::class => [
                ['index', 10]
            ],
            PayfytNotifyEvent::class => [
                ['fyt', 10]
            ],
            PayfytChargeNotifyEvent::class => [
                ['fytCharge', 10]
            ]
        ];
    }

    public function fytCharge(PayfytChargeNotifyEvent $event) {
        $orderNo = $event->getCporder();
        $order = $this->chargeOrderService->info(['order_no' => $orderNo]);
        if (!$order instanceof ChargeOrder) {
            $errMsg = 'invalid ChargeOrder ' . $orderNo;
            $this->logger->error($errMsg);
            throw new \Exception($errMsg);
        }

        try {
            $this->chargeOrderService->getEntityManager()->beginTransaction();
            $order = $this->chargeOrderService->findById($order->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$order instanceof ChargeOrder) {
                $this->chargeOrderService->getEntityManager()->rollback();
                $errMsg = 'invalid shop_sub_number ' . $orderNo;
                $this->logger->error($errMsg);
                return;
            }
            // 300表示下发成功，306下发失败
            if ($order->getNotifyStatus() === 1) {
  //              $this->logger->info('order is success: ' . $order->getOrderNo());
                $this->chargeOrderService->getEntityManager()->rollback();
                return;
            }
            if ($order->getNotifyStatus() == $event->getStatus()) {
                // 订单状态一样不处理
//                $this->logger->info('order status is same: ');
                $this->chargeOrderService->getEntityManager()->rollback();
                return;
            }
            // 更新状态
            $order->setNotifyTime(time());
            $order->setNotifyStatus(intval($event->getStatus()));

            $this->chargeOrderService->flush($order);
            $this->chargeOrderService->getEntityManager()->commit();
        } catch (\Exception $exception) {
            $this->chargeOrderService->getEntityManager()->rollback();
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }

    public function fyt(PayfytNotifyEvent $event) {
        $orderNo = $event->getCporder();
        $order = $this->withdrawOrderService->info(['order_no' => $orderNo]);
        if (!$order instanceof Pay361WithdrawOrder) {
            $errMsg = 'invalid shop_sub_number ' . $orderNo;
            $this->logger->error($errMsg);
            throw new \Exception($errMsg);
        }

        try {
            $this->withdrawOrderService->getEntityManager()->beginTransaction();
            $order = $this->withdrawOrderService->findById($order->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$order instanceof Pay361WithdrawOrder) {
                $this->withdrawOrderService->getEntityManager()->rollback();
                $errMsg = 'invalid shop_sub_number ' . $orderNo;
                $this->logger->error($errMsg);
                return;
            }
            // 300表示下发成功，306下发失败
            if ($order->getState() === '300') {
                $this->logger->info('order is success: ' . $order->getOrderNo());
                $this->withdrawOrderService->getEntityManager()->rollback();
                return;
            }
            if ($order->getState() == $event->getStatus()) {
                // 订单状态一样不处理
                $this->logger->info('order state is same: ' . $order->getState());
                $this->withdrawOrderService->getEntityManager()->rollback();
                return;
            }
            // 更新状态
            $order->setState($event->getStatus());
            $order->setNotifyTime(time());
            $order->setPayOrderNo('');
            $order->setSubMoney(0);
            $order->setServiceCharge(0);
            $order->setActualMoney($event->getAmount());
            $order->setNotifyShopPhone('');
            $order->setPaySign($event->getSign());
            $order->setRemark($event->getRemark());

            $this->withdrawOrderService->flush($order);
            $this->withdrawOrderService->getEntityManager()->commit();
        } catch (\Exception $exception) {
            $this->withdrawOrderService->getEntityManager()->rollback();
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
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
            $errMsg = 'invalid shop_sub_number ' . $orderNo;
            $this->logger->error($errMsg);
            throw new \Exception($errMsg);
        }

        try {
            $this->withdrawOrderService->getEntityManager()->beginTransaction();
            $order = $this->withdrawOrderService->findById($order->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$order instanceof Pay361WithdrawOrder) {
                $this->withdrawOrderService->getEntityManager()->rollback();
                $errMsg = 'invalid shop_sub_number ' . $orderNo;
                $this->logger->error($errMsg);
                return;
            }
            if ($order->getState() === 'success') {
                $this->logger->info('order is success: ' . $order->getOrderNo());
                $this->withdrawOrderService->getEntityManager()->rollback();
                return;
            }
            if ($order->getState() == $event->getSubState()) {
                // 订单状态一样不处理
                $this->logger->info('order state is same: ' . $order->getState());
                return;
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

