<?php

namespace App\Controller;


use App\Events\Pay361NotifyEvent;
use App\Events\WmPayNotifyEvent;
use by\component\pay361\SignTool;
use by\component\wmpay\NotifyParams;
use by\component\wmpay\WmPaySignTool;
use Dbh\SfCoreBundle\Common\ByEnv;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WmpayCallbackController extends AbstractController
{
    protected $logService;
    protected $eventDispatcher;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logService = $logger;
    }

    /**
     * @Route("/wmpay/notify", name="wmpay_notify", methods={"POST","GET"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if (ByEnv::get('WMPAY_DEBUG')) {
            $all = json_encode($request->request->all());
            $get = json_encode($request->query->all());
            $this->logService->error($all.';'.$get, ["c" => 'wmpay']);
        }
        $data = $request->request->all();
        $sign = $request->get('signature', '');
        $verify = WmPaySignTool::verifySign($data, $sign , ByEnv::get('WMPAY_KEY'));
        if (!$verify) {
            return new Response($sign.'verify failed');
        }
        $params = new NotifyParams($data);
        $event = new WmPayNotifyEvent($params);
        $this->eventDispatcher->dispatch($event);
        return new Response('success');
    }
}
