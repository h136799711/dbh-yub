<?php


namespace App\Controller;


use App\Entity\Video;
use App\Entity\VideoCate;
use App\ServiceInterface\VideoCateServiceInterface;
use App\ServiceInterface\VideoServiceInterface;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Controller\BaseSymfonyApiController;
use Symfony\Component\HttpKernel\KernelInterface;

class VideoCateController extends BaseSymfonyApiController
{
    protected $videoCateService;
    protected $videoService;

    public function __construct(
        VideoServiceInterface $videoService,
        VideoCateServiceInterface $videoCateService, KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->videoService = $videoService;
        $this->videoCateService = $videoCateService;
    }

    /**
     * @return mixed
     */
    public function query() {
        return $this->videoCateService->queryAllBy([], ['sort' => 'desc']);
    }

}
