<?php


namespace App\AdminController;


use App\Entity\Video;
use App\Entity\VideoCate;
use App\ServiceInterface\VideoCateServiceInterface;
use App\ServiceInterface\VideoServiceInterface;
use App\ServiceInterface\VideoSourceServiceInterface;
use by\component\paging\vo\PagingParams;
use by\infrastructure\constants\StatusEnum;
use by\infrastructure\helper\CallResultHelper;
use Dbh\SfCoreBundle\Common\LoginSessionInterface;
use Dbh\SfCoreBundle\Common\UserAccountServiceInterface;
use Dbh\SfCoreBundle\Controller\BaseNeedLoginController;
use Symfony\Component\HttpKernel\KernelInterface;

class VideoController extends BaseNeedLoginController
{
    protected $videoCateService;
    protected $videoService;
    protected $videoSourceService;

    public function __construct(
        VideoSourceServiceInterface $videoSourceService,
        VideoServiceInterface $videoService, VideoCateServiceInterface $videoCateService,
        UserAccountServiceInterface $userAccountService, LoginSessionInterface $loginSession, KernelInterface $kernel)
    {
        parent::__construct($userAccountService, $loginSession, $kernel);
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
    public function query(PagingParams $pagingParams, $cateId = 0, $title = '') {
        $map = [];
        if (!empty($title)) {
            $map['title'] = ['like', '%'.$title.'%'];
        }
        if (!empty($cateId)) {
            $map['cate_id'] = $cateId;
        }
        return $this->videoService->queryAndCount($map, $pagingParams, ['updateTime' => 'desc']);
    }

    /**
     * @param $title
     * @param $cover
     * @param $description
     * @param $cateId
     * @return mixed|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \by\component\exception\NotLoginException
     */
    public function create($title, $cover, $description, $cateId) {

        $this->checkLogin();

        $cate = $this->videoCateService->findById($cateId);
        if (!$cate instanceof VideoCate) return '视频分类id错误';

        $cate->setVidCnt($cate->getVidCnt() + 1);

        $this->videoCateService->flush($cate);

        $entity = new Video();
        $entity->setCover($cover);
        $entity->setTitle($title);
        $entity->setDescription($description);
        $entity->setCateId($cateId);
        $entity->setUploaderId($this->getUid());
        $entity->setUploadNick($this->getLoginUserNick());
        $entity->setShowStatus(StatusEnum::DISABLED);

        return $this->videoService->add($entity);
    }

    public function show($id) {
        $entity = $this->videoService->findById($id);
        if (!$entity instanceof Video) return CallResultHelper::fail('id not exits');

        if ($entity->getShowStatus() == StatusEnum::SOFT_DELETE) {
            return CallResultHelper::fail('该视频已被删除');
        }
        $entity->setShowStatus(StatusEnum::ENABLE);

        return CallResultHelper::success();
    }

    public function hide($id) {
        $entity = $this->videoService->findById($id);
        if (!$entity instanceof Video) return CallResultHelper::fail('id not exits');

        if ($entity->getShowStatus() == StatusEnum::SOFT_DELETE) {
            return CallResultHelper::fail('该视频已被删除');
        }

        $entity->setShowStatus(StatusEnum::DISABLED);

        return CallResultHelper::success();
    }


    /**
     * @param $id
     * @param $title
     * @param $cover
     * @param $description
     * @param $cateId
     * @return \by\infrastructure\base\CallResult|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \by\component\exception\NotLoginException
     */
    public function update($id, $title, $cover, $description, $cateId) {

        $this->checkLogin();

        $entity = $this->videoService->findById($id);
        if (!$entity instanceof Video) return 'id 不存在';

        $entity->setCover($cover);
        $entity->setTitle($title);
        $entity->setDescription($description);
        $entity->setCateId($cateId);
        $entity->setUploaderId($this->getUid());
        $entity->setUploadNick($this->getLoginUserNick());

        $this->videoService->flush($entity);
        return CallResultHelper::success();
    }

    /**
     * @param $id
     * @return \by\infrastructure\base\CallResult|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete($id) {
        $cnt = $this->videoSourceService->count(['vid' => $id]);
        if ($cnt > 0) return '不能删除有视频源的视频,请先删除该视频源';

        $entity = $this->videoService->findById($id);
        if (!$entity instanceof Video) return CallResultHelper::success();

        $entity->setShowStatus(StatusEnum::SOFT_DELETE);

        $cate = $this->videoCateService->findById($entity->getCateId());
        if ($cate instanceof VideoCate) {
            $cate->setVidCnt($cate->getVidCnt() - 1);
            $this->videoCateService->flush($cate);
        }

        $this->videoService->flush($entity);

        return CallResultHelper::success();
    }

}
