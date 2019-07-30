<?php


namespace App\Controller;


use App\Entity\Video;
use App\Entity\VideoCate;
use App\Entity\VideoSource;
use App\ServiceInterface\VideoCateServiceInterface;
use App\ServiceInterface\VideoServiceInterface;
use App\ServiceInterface\VideoSourceServiceInterface;
use by\component\paging\vo\PagingParams;
use by\infrastructure\constants\StatusEnum;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Controller\BaseSymfonyApiController;
use Symfony\Component\HttpKernel\KernelInterface;

class VideoController extends BaseSymfonyApiController
{
    protected $videoCateService;
    protected $videoService;
    protected $videoSourceService;

    public function __construct(
        VideoSourceServiceInterface $videoSourceService,
        VideoServiceInterface $videoService, VideoCateServiceInterface $videoCateService,
        KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->videoCateService = $videoCateService;
        $this->videoService = $videoService;
        $this->videoSourceService = $videoSourceService;
    }


    /**
     * @param PagingParams $pagingParams
     * @param int $cateId
     * @param string $title
     * @return mixed
     */
    public function query(PagingParams $pagingParams, $cateId = 0, $title = '')
    {
        $map = [
            'show_status' => StatusEnum::ENABLE
        ];
        if (!empty($title)) {
            $map['title'] = ['like', '%' . $title . '%'];
        }
        if (!empty($cateId)) {
            $map['cate_id'] = $cateId;
        }
        $result = $this->videoService->queryAndCount($map, $pagingParams, ['updateTime' => 'desc']);
        return $result;
    }

    public function info($id)
    {
        $videoInfo = $this->videoService->info(['id' => $id]);
        if (!$videoInfo instanceof Video) {
            return CallResultHelper::fail('id invalid');
        }
        if ($videoInfo->getShowStatus() == StatusEnum::SOFT_DELETE) {
            return CallResultHelper::fail('视频已被删除');
        }
        $cate = $this->videoCateService->info(['id' => $videoInfo->getCateId()]);
        $cateName = 'Unknown';
        if ($cate instanceof VideoCate) {
            $cateName = $cate->getTitle();
        }

        $vSource = $this->videoSourceService->queryAllBy(['vid' => $id, 'status' => StatusEnum::ENABLE], ["sort" => "desc", "id" => "desc"]);

        $groupSource = $this->groupSource($vSource);

        $data = [
            'title' => $videoInfo->getTitle(),
            'description' => $videoInfo->getDescription(),
            'update_time' => $videoInfo->getUpdateTime(),
            'uploader' => $videoInfo->getUploadNick(),
            'cover' => $videoInfo->getCover(),
            'cate_name' => $cateName,
            'cate_id' => $videoInfo->getCateId(),
//            '_source' => $vSource,
            '_group_source' => $groupSource
        ];

        return CallResultHelper::success($data);
    }

    protected function groupSource($vSource)
    {
        $group = [];
        foreach ($vSource as $vo) {
            if ($vo instanceof VideoSource) {
                $comeFrom = $vo->getComeFrom();
            } elseif (is_array($vo)) {
                $comeFrom = $vo['come_from'];
            } else {
                continue;
            }

            if (!array_key_exists($comeFrom, $group)) {
                $group[$comeFrom] = [];
            }
            array_push($group[$comeFrom], $vo);
        }
        return $group;
    }

}
