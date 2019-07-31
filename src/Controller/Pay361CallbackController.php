<?php

namespace App\Controller;


use App\Events\Pay361NotifyEvent;
use by\component\pay361\SignTool;
use Dbh\SfCoreBundle\Common\ByEnv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Pay361CallbackController extends AbstractController
{
    protected $logService;
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/pay361/notify", name="pay361_notify")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $shopSubNumber = $request->get('shop_sub_number', '');
        $shopPhone = $request->get('shop_phone', '');
        $actualMoney = $request->get('actual_money', '');
        $serviceCharge = $request->get('service_charge', '');
        $subMoney = $request->get('sub_money', '');
        $subState = $request->get('sub_state', '');
        $subPaymentNumber = $request->get('subPaymentNumber', '');
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

        $verifySign = SignTool::sign($data, ByEnv::get('PAY361_KEY'));

        if ($sign != $verifySign) {
            return new Response('verify failed');
        }

        $event = new Pay361NotifyEvent();
        $event->setShopSubNumber($shopSubNumber);
        $event->setShopPhone($shopPhone);
        $event->setActualMoney($actualMoney);
        $event->setServiceCharge($serviceCharge);
        $event->setSubMoney($subMoney);
        $event->setSubState($subState);
        $event->setSubPaymentNumber($subPaymentNumber);

        $this->eventDispatcher->dispatch($event);

        return new Response('success');
    }
}
