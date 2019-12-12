<?php
/**
 * Created by PhpStorm.
 * User: itboye
 * Date: 2018/8/8
 * Time: 10:53
 */

namespace App\Command;


use App\Common\ByPayEnum;
use App\Common\RabbitMqConstants;
use App\Dto\CfOrderPaySuccessDto;
use App\Entity\CfPayNotify;
use App\Entity\CfPayOrder;
use App\Entity\Pay361WithdrawOrder;
use App\Helper\RabbitMqClient;
use App\ServiceInterface\CfPayNotifyServiceInterface;
use App\ServiceInterface\CfPayOrderServiceInterface;
use App\ServiceInterface\Pay361WithdrawOrderServiceInterface;
use by\component\dypay\DyPay;
use by\component\paging\vo\PagingParams;
use by\infrastructure\helper\CallResultHelper;
use by\infrastructure\helper\Object2DataArrayHelper;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class
 * @package App\Command
 */
class DypayCommand extends Command
{

    /**
     * @var RabbitMqClient
     */
    private $client;

    private $logger;
    protected $service;

    public function __construct(Pay361WithdrawOrderServiceInterface $service, LoggerInterface $logger, ?string $name = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
        $this->service = $service;
    }

    public function process($data)
    {
        $channel = $data->delivery_info['channel'];
        $deliveryTag = $data->delivery_info['delivery_tag'];
        if (!$channel instanceof AMQPChannel) {
            return;
        }
        try {
            while (!$channel->getConnection()->isConnected()) {
                $channel->getConnection()->reconnect();
                echo "reconnect", "\n";
                sleep(1);
            }
            $msgContent = $data->body;
            $this->logger->debug('[MQ_PROCESS]' . $msgContent);
            if (strpos($msgContent, "\\\"") === false) {
                $msgContent = str_replace('\\', "", $msgContent);
            }
            $msgContent = json_decode($msgContent, JSON_OBJECT_AS_ARRAY);

            if (is_array($msgContent)) {
                if (array_key_exists('unique_order', $msgContent)) {
                    $payCode = $msgContent['unique_order'];

//                    $notify = $this->payNotifyService->info(['pay_code' => $payCode]);
//                    if ($notify instanceof CfPayNotify) {
//                        $ret = $this->payNotifyService->notify($notify->getId(), $notify);
//                        if ($ret->isFail()) {
//                            $this->logger->debug('payNotifyFirst.ERROR' . $ret->getMsg());
//                        } else {
//                            $this->logger->debug('notify_success');
//                        }
//                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error('payNotifyFirst.ERROR' . $exception->getMessage());
        } finally {
            $channel->basic_ack($deliveryTag, true);
        }
    }

    protected function configure()
    {
        $this->setName("dypay:query")
            ->setDescription("")
            ->setHelp('dypay first notify');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paging = new PagingParams();
        $map = [
            'passageway_code' => ByPayEnum::DyPay,
            'state' => ''
        ];
        $paging->setPageSize(30);
        $list = $this->service->queryBy($map, $paging, ["id" => 'asc']);

        $map = [
            'passageway_code' => ByPayEnum::DyPay,
            'state' => '3'
        ];
        $paging->setPageSize(30);
        $list3 = $this->service->queryBy($map, $paging, ["id" => 'asc']);

        $map = [
            'passageway_code' => ByPayEnum::DyPay,
            'state' => '0'
        ];
        $paging->setPageSize(10);
        $list0 = $this->service->queryBy($map, $paging, ["id" => 'asc']);
        $allList = array_merge($list, $list3, $list0);
        var_dump($allList);
        foreach ($allList as $vo) {
            $this->notify($vo['id']);
        }
    }

    protected function notify($id) {
        $order = $this->service->findById($id);
        if (!($order instanceof Pay361WithdrawOrder)) {
            return false;
        }
        $ret = DyPay::getInstance()->query($order->getOrderNo());
        if ($ret->isSuccess()) {
            $order->setState($ret->getData());
            $this->service->flush($order);
        } else {
            $order->setRemark($order->getRemark().$ret->getMsg());
            $order->setState(8);
            $this->service->flush($order);
        }
        return true;
    }
}
