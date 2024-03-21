<?php

namespace App\EntityListener;

use App\Entity\Video;
use App\Service\VideoService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postLoad, method: 'onPostLoad', entity: Video::class)]
#[AsEntityListener(event: Events::prePersist, method: 'onPrePersist', entity: Video::class)]
class VideoListener
{
    public function __construct(
        private VideoService $videoService,
    ) {
    }

    public function onPrePersist(
        Video $video,
        PrePersistEventArgs $prePersistEventArgs,
    ) {
        $this->videoService->populateInfo($video);
    }

    public function onPostLoad(
        Video $video,
        PostLoadEventArgs $postLoadEventArgs,
    ): void {
        $video->setIframe($this->videoService->getIframeTag($video->getUrl()));
    }
}
