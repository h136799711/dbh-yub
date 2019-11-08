<?php

namespace App\Controller;


use App\Events\Pay361NotifyEvent;
use by\component\fyt\FytPay;
use by\component\fyt\FytSignTool;
use by\component\pay361\SignTool;
use Dbh\SfCoreBundle\Common\ByEnv;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PayFytCallbackController  extends AbstractController
{
    protected $logService;
    protected $eventDispatcher;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logService = $logger;
    }

    /**
     * @Route("/payfyt/notify", name="payfyt_notify", methods={"POST","GET"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $all = json_encode($request->request->all());
        $get = json_encode($request->query->all());
        $this->logService->info($all.';'.$get, ["c" => 'PayfytCallback']);
//        mchid	Y	String	分配的商户号
//amount	Y	Int	代付金额元
//cporder	Y	String	代付请求时候的渠道订单号
//remark	Y	String	下发失败原因描述
//payType	Y	String	下发类型，企业还是个人
//status	Y	String	300表示下发成功，306下发失败
//sign	Y	String	签名
        $mchid = $request->get('mchid', '');
        $amount = $request->get('amount', '');
        $cporder = $request->get('cporder', '');
        $remark = $request->get('remark', '');
        $payType = $request->get('payType', '');
        $status = $request->get('status', '');
        $sign = $request->get('sign', '');
        $data = [
            "mchid" => $mchid,
            "amount" => $amount,
            "cporder" => $cporder,
            "remark" => $remark,
            "payType" => $payType,
            "status" => $status
        ];
        $fytPublicKey = FytPay::getInstance()->getSysPublicRsaKey();
        $verifySign = FytSignTool::verifySign($data, $sign, $fytPublicKey);
        if (!$verifySign) {
            return new Response('sign verify failed');
        }

        $event = new Pay361NotifyEvent();
        $event->setActualMoney($amount);
        $event->setServiceCharge(0);
        $event->setSubMoney(0);
        $event->setSubState($status);
        $event->setRemark($remark);
        $event->setSubPaymentNumber('');
        $event->setSign($sign);

        $this->eventDispatcher->dispatch($event);

        return new Response('success');
    }
}
