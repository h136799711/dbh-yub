<?php

namespace App\Controller;


use App\Events\Pay361NotifyEvent;
use by\component\pay361\SignTool;
use Dbh\SfCoreBundle\Common\ByEnv;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Pay361CallbackController extends AbstractController
{
    protected $logService;
    protected $eventDispatcher;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logService = $logger;
    }

    /**
     * @Route("/pay361/notify", name="pay361_notify", methods={"POST","GET"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $all = json_encode($request->request->all());
        $get = json_encode($request->query->all());
        $this->logService->info($all.';'.$get, ["c" => 'Pay361Callback']);
        $shopSubNumber = $request->get('shop_sub_number', '');
        $shopPhone = $request->get('shop_phone', '');
        $actualMoney = $request->get('actual_money', '');
        $serviceCharge = $request->get('service_charge', '');
        $subMoney = $request->get('sub_money', '');
        $subState = $request->get('sub_state', '');
        $subPaymentNumber = $request->get('sub_payment_number', '');
        $sign = $request->get('sign', '');
        $data = [
            "shop_sub_number" => $shopSubNumber,
            "sub_payment_number" => $subPaymentNumber,
            "actual_money" => $actualMoney,
            "sub_money" => $subMoney,
            "service_charge" => $serviceCharge,
            "sub_state" => $subState,
            "shop_phone" => $shopPhone
        ];

        var_dump($data);
        $verifySign = SignTool::sign($data, ByEnv::get('PAY361_KEY'));

        if ($sign != $verifySign) {

            return new Response($sign.'verify failed'.$verifySign);
        }

        $event = new Pay361NotifyEvent();
        $event->setShopSubNumber($shopSubNumber);
        $event->setShopPhone($shopPhone);
        $event->setActualMoney($actualMoney);
        $event->setServiceCharge($serviceCharge);
        $event->setSubMoney($subMoney);
        $event->setSubState($subState);
        $event->setSubPaymentNumber($subPaymentNumber);
        $event->setSign($sign);

        $this->eventDispatcher->dispatch($event);

        return new Response('success');
    }
}
