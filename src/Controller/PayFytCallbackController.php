<?php

namespace App\Controller;


use App\Events\Pay361NotifyEvent;
use App\Events\PayfytChargeNotifyEvent;
use App\Events\PayfytNotifyEvent;
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
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $all = json_encode($request->request->all());
        $get = json_encode($request->query->all());
        $json = file_get_contents('php://input');

        if (ByEnv::get('FYT_DEBUG') == 1) {
            $this->logService->error($all . ';' . $get.'raw: '.$json, ["c" => 'PayfytChargeCallback']);
        }
//        $all = json_encode($request->request->all());
//        $get = json_encode($request->query->all());
//        $this->logService->info($all.';'.$get, ["c" => 'PayfytCallback']);
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

        $event = new PayfytNotifyEvent();
        $event->setStatus($status);
        $event->setPayType($payType);
        $event->setMchid($mchid);
        $event->setAmount($amount);
        $event->setCporder($cporder);
        $event->setRemark($remark);
        $event->setSign($sign);

        $this->eventDispatcher->dispatch($event);

        return new Response('success');
    }


    /**
     * @Route("/chargefyt/notify", name="chargefyt_notify", methods={"POST","GET"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function charge(Request $request)
    {
//        $all = json_encode($request->request->all());
//        $get = json_encode($request->query->all());
        $json = file_get_contents('php://input');

        if (ByEnv::get('FYT_DEBUG') == 1) {
            $this->logService->error('raw: '.$json, ["c" => 'PayfytChargeCallback']);
        }
        $data = json_decode($json, JSON_OBJECT_AS_ARRAY);

//        mchid	Y	String	分配的商户号
//amount	Y	Int	代付金额元
//cporder	Y	String	代付请求时候的渠道订单号
//remark	Y	String	下发失败原因描述
//payType	Y	String	下发类型，企业还是个人
//status	Y	String	300表示下发成功，306下发失败
//sign	Y	String	签名
        $mchid = $data['mchid'];
        if (empty($mchid)) {
            return new Response($mchid.'sign verify failed');
        }
        $amount = $data['amount'];
        $cporder = $data['cporder'];
        $status = $data['status'];
        $sign = $data['sign'];

        $data = [
            "mchid" => $mchid,
            "amount" => $amount,
            "cporder" => $cporder,
            "status" => $status
        ];
        // 不校验
        $fytPublicKey = FytPay::getInstance()->getSysPublicRsaKey();
        $verifySign = FytSignTool::verifySign($data, $sign, $fytPublicKey);
        if (!$verifySign) {
            return new Response('sign verify failed');
        }

        $event = new PayfytChargeNotifyEvent();
        $event->setStatus($status);
        $event->setMchid($mchid);
        $event->setAmount($amount);
        $event->setCporder($cporder);
        $event->setSign($sign);
        $this->eventDispatcher->dispatch($event);

        return new Response('success');
    }
}
