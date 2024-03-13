<?php

namespace App\EntityListener;

use App\Entity\Trick;
use App\Entity\Picture;
use Doctrine\ORM\Events;
use App\Service\SlugService;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: Events::prePersist, method: 'onPrePersist', entity: Trick::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'onPreUpdate', entity: Trick::class)]
class TrickListener
{
    public function __construct(private SlugService $slugService)
    {
    }

    public function onPrePersist(
        Trick $trick,
        PrePersistEventArgs $eventArgs,
    ): void {
        // Set slug
        $slug = $this->slugService->makeSlug($trick->getName());
        $trick->setSlug($slug);

        $trick->setCreatedAt(new \DateTimeImmutable());

        // Set thumbnail
        /** @var Picture|false $firstPicture */
        $firstPicture = $trick->getPictures()->first();

        if ($firstPicture) {
            $trick->setThumbnail($firstPicture);
        }
    }

    public function onPreUpdate(
        Trick $trick,
        PreUpdateEventArgs $eventArgs,
    ): void {
        // Set slug
        $slug = $this->slugService->makeSlug($trick->getName());
        $trick->setSlug($slug);

        $trick->setUpdatedAt(new \DateTimeImmutable());
    }
}
