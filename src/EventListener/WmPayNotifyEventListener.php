<?php


namespace App\EventListener;


use App\Common\ByPayEnum;
use App\Entity\ChargeOrder;
use App\Entity\Pay361WithdrawOrder;
use App\Events\Pay361NotifyEvent;
use App\Events\PayfytChargeNotifyEvent;
use App\Events\PayfytNotifyEvent;
use App\Events\WmPayNotifyEvent;
use App\ServiceInterface\ChargeOrderServiceInterface;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use by\component\string_extend\helper\StringHelper;
use Doctrine\DBAL\LockMode;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WmPayNotifyEventListener implements EventSubscriberInterface
{
    protected $withdrawOrderService;
    protected $logger;

    public function __construct(
        Pay361WithdrawOrderServiceInterface $withdrawOrderService, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->withdrawOrderService = $withdrawOrderService;
    }

    public static function getSubscribedEvents()
    {
        return [
            WmPayNotifyEvent::class => [
                ['index', 10]
            ]
        ];
    }

    public function index(WmPayNotifyEvent $event) {
        $orderNo = $event->getParams()->getMerOrderId();

        $order = $this->withdrawOrderService->info(['order_no' => $orderNo]);
        if (!$order instanceof Pay361WithdrawOrder) {
            $errMsg = 'invalid order no ' . $orderNo;
            $this->logger->error($errMsg);
            throw new \Exception($errMsg);
        }

        try {
            $this->withdrawOrderService->getEntityManager()->beginTransaction();
            $order = $this->withdrawOrderService->findById($order->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$order instanceof Pay361WithdrawOrder) {
                $this->withdrawOrderService->getEntityManager()->rollback();
                $errMsg = 'invalid order no ' . $orderNo;
                $this->logger->error($errMsg);
                return;
            }
            // 1 表示交易成功
            if (intval($order->getState()) === 1) {
                $this->logger->info('order is success: ' . $order->getOrderNo());
                $this->withdrawOrderService->getEntityManager()->rollback();
                return;
            }

            // 更新状态
            $order->setState($event->getParams()->getSuccess());
            $order->setPassagewayCode(ByPayEnum::WmPay);
            $order->setNotifyTime(time());
            $order->setPayOrderNo('');
            $order->setSubMoney(0);
            $order->setServiceCharge(0);
            $order->setActualMoney(StringHelper::numberFormat($event->getParams()->getTxnAmt() / 100, 2));
            $order->setNotifyShopPhone('');
            $order->setPaySign('');
            $order->setRemark($order->getRemark() . $event->getParams()->getRespMsg());

            $this->withdrawOrderService->flush($order);
            $this->withdrawOrderService->getEntityManager()->commit();
        } catch (\Exception $exception) {
            $this->withdrawOrderService->getEntityManager()->rollback();
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }
}

