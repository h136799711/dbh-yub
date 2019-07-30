<?php


namespace App\AdminController;


use App\Entity\Video;
use App\Entity\VideoCate;
use App\Entity\VideoSource;
use App\ServiceInterface\VideoCateServiceInterface;
use App\ServiceInterface\VideoServiceInterface;
use App\ServiceInterface\VideoSourceServiceInterface;
use by\component\paging\vo\PagingParams;
use by\component\video\VideoType;
use by\infrastructure\constants\StatusEnum;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Symfony\Component\HttpKernel\KernelInterface;

class VideoSourceController extends BaseNeedLoginController
{
    protected $videoService;
    protected $videoSourceService;

    public function __construct(
        VideoSourceServiceInterface $videoSourceService,
        VideoServiceInterface $videoService,
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, KernelInterface $kernel)
    {
        parent::__construct($userAccountService, $loginSession, $kernel);
        $this->videoService = $videoService;
        $this->videoSourceService = $videoSourceService;
    }


    /**
     * @param $vid
     * @return mixed
     */
    public function query($vid) {
        $map = ['vid' => $vid];
        return $this->videoSourceService->queryAllBy($map, ['sort' => 'desc']);
    }

    /**
     * @param $vid
     * @param $comeFrom
     * @param $vType
     * @param $vUri
     * @param int $sort
     * @return mixed|string
     * @throws \by\component\exception\NotLoginException
     */
    public function create($vid, $comeFrom, $vType, $vUri, $sort = 0) {
        $this->checkLogin();

        if (!VideoType::isSupport($vType)) {
            return $vType." 视频源类型不支持";
        }

        $entity = new VideoSource();
        $entity->setVid($vid);
        $entity->setSort($sort);
        $entity->setStatus(StatusEnum::ENABLE);
        $entity->setComeFrom($comeFrom);
        $entity->setVType($vType);
        $entity->setVUri($vUri);

        return $this->videoSourceService->add($entity);
    }

    /**
     * @param $id
     * @param $comeFrom
     * @param $vType
     * @param $vUri
     * @param int $sort
     * @return \by\infrastructure\base\CallResult|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \by\component\exception\NotLoginException
     */
    public function update($id, $comeFrom, $vType, $vUri, $sort = 0) {

        $this->checkLogin();

        $entity = $this->videoSourceService->findById($id);
        if (!$entity instanceof VideoSource) return 'id 不存在';

        $entity->setComeFrom($comeFrom);
        $entity->setVType($vType);
        $entity->setVUri($vUri);
        $entity->setSort($sort);

        $this->videoSourceService->flush($entity);
        return CallResultHelper::success();
    }

    /**
     * @param $id
     * @return \by\infrastructure\base\CallResult|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \by\component\exception\NotLoginException
     */
    public function delete($id) {

        $this->checkLogin();

        $entity = $this->videoSourceService->findById($id);
        if (!$entity instanceof VideoSource) return CallResultHelper::success();

        $this->videoSourceService->flush($entity);

        return CallResultHelper::success();
    }

}
