<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 */
class Video extends BaseEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $description;

    /**
     * @ORM\Column(type="bigint")
     */
    private $createTime;

    /**
     * @ORM\Column(type="bigint")
     */
    private $updateTime;

    /**
     * @ORM\Column(type="bigint")
     */
    private $cateId;

    /**
     * @ORM\Column(type="bigint")
     */
    private $uploaderId;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $uploadNick;

    /**
     * @ORM\Column(type="smallint")
     */
    private $showStatus;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $cover;

    /**
     * @ORM\Column(type="bigint")
     */
    private $views;

    /**
     * Video constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->showStatus = 0;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    public function setCreateTime(int $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    public function setUpdateTime(int $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    public function getCateId(): ?int
    {
        return $this->cateId;
    }

    public function setCateId(int $cateId): self
    {
        $this->cateId = $cateId;

        return $this;
    }

    public function getUploaderId(): ?int
    {
        return $this->uploaderId;
    }

    public function setUploaderId(int $uploaderId): self
    {
        $this->uploaderId = $uploaderId;

        return $this;
    }

    public function getUploadNick(): ?string
    {
        return $this->uploadNick;
    }

    public function setUploadNick(string $uploadNick): self
    {
        $this->uploadNick = $uploadNick;

        return $this;
    }

    public function getShowStatus(): ?int
    {
        return $this->showStatus;
    }

    public function setShowStatus(int $showStatus): self
    {
        $this->showStatus = $showStatus;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }
}
