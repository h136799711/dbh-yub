<?php
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------
// | ©2018 California State Lottery All rights reserved.
// +----------------------------------------------------------------------
// | Author: Smith Jack
// +----------------------------------------------------------------------

namespace App\Service;


use App\Repository\CityAreaRepository;
use App\Repository\CityRepository;
use App\Repository\ProvinceRepository;
use App\Repository\TownRepository;
use App\ServiceInterface\PcaServiceInterface;
use Dbh\SfCoreBundle\Common\BaseService;


class PcaService extends BaseService implements PcaServiceInterface
{
    protected $provinceRepo;
    protected $cityRepo;
    protected $cityAreaRepo;
    protected $townRepo;

    public function __construct(TownRepository $townRepository, CityAreaRepository $cityAreaRepository, CityRepository $cityRepository, ProvinceRepository $provinceRepository)
    {
        $this->provinceRepo = $provinceRepository;
        $this->cityRepo = $cityRepository;
        $this->cityAreaRepo = $cityAreaRepository;
        $this->townRepo = $townRepository;
    }

    function queryProvince()
    {
        return $this->provinceRepo->queryAllBy([], ['id' => 'asc']);
    }

    function queryCity($provinceCode = '')
    {
        if (empty($provinceCode)) {
            return [];
        }
        return $this->cityRepo->queryAllBy(['parentCode' => ['like', $provinceCode.'%']], ['id' => 'asc']);
    }

    function queryCityArea($cityCode = '')
    {
        if (empty($cityCode)) {
            return [];
        }
        return $this->cityAreaRepo->queryAllBy(['parent_code' => ['like', $cityCode.'%']], ['id' => 'asc']);
    }

    function queryTown($cityAreaCode = '')
    {
        if (empty($cityAreaCode)) {
            return [];
        }
        return $this->townRepo->queryAllBy(['parent_code' => ['like', $cityAreaCode.'%']], ['id' => 'asc']);
    }


}
