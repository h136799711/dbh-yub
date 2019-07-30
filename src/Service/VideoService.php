<?php


namespace App\Service;

use App\Repository\VideoRepository;
use App\ServiceInterface\VideoServiceInterface;
use Dbh\SfCoreBundle\Common\BaseService;

class VideoService extends BaseService implements VideoServiceInterface
{
    public function __construct(VideoRepository $repository)
    {
        $this->repo = $repository;
    }
}
