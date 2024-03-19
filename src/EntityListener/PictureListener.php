<?php

namespace App\EntityListener;

use App\Entity\Picture;
use App\Service\TrickService;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: Events::prePersist, method: 'onPrePersist', entity: Picture::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onPostRemove', entity: Picture::class)]
class PictureListener
{
    public function __construct(
        private TrickService $trickService,
        private readonly string $tricksPicturesUploadsDirectory
    ) {
    }

    public function onPrePersist(
        Picture $picture,
        PrePersistEventArgs $prePersistEventArgs,
    ): void {
        $this->trickService->saveTrickPicture($picture);
    }

    public function onPostRemove(
        Picture $picture,
        PostRemoveEventArgs $postRemoveEventArgs,
    ): void {
        $this->trickService->deleteTrickPicture($picture);
    }
}
