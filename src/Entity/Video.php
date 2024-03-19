<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VideoRepository;
use App\Service\VideoService;
use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\Type('int')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    #[ORM\Column(length: 2048)]
    #[Assert\NotBlank]
    #[AppAssert\Oembed]
    private ?string $url = null;

    private ?VideoService $videoService = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): static
    {
        $this->trick = $trick;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getIframe(): ?string
    {
        $videoService = $this->getVideoService();

        return $videoService->getIframeTag($this);
    }

    public function getThumbnailUrl(): ?string
    {
        $videoService = $this->getVideoService();

        return $videoService->getThumbnailUrl();
    }

    public function getTitle(): string
    {
        $videoService = $this->getVideoService();

        return $videoService->getTitle();
    }

    private function getVideoService(): VideoService
    {
        if (is_null($this->videoService)) {
            $this->videoService = new VideoService($this);
        }

        return $this->videoService;
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }
}
