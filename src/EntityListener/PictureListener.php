<?php

namespace App\EntityListener;

use App\Entity\Picture;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Picture::class)]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Picture::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Picture::class)]
class PictureListener
{
    public function __construct(private readonly string $trickPicturesUploadDirectory)
    {
    }

    public function prePersist(
        Picture $picture,
        PrePersistEventArgs $prePersistEventArgs,
    ): void {
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        $file = $picture->getFile();

        $safeFilename = hash('md5', $file->getContent()) . '-' . uniqid() . '.' . $file->guessExtension();

        $picture->setFilename($safeFilename);

        $file->move($uploadDirectory, $safeFilename);
    }

    public function postLoad(
        Picture $picture,
        PostLoadEventArgs $postLoadEventArgs,
    ): void {
        // Set the URL
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        $filename = $picture->getFilename();

        $url = preg_replace('#.*/public#', '', $uploadDirectory) . '/' . $filename;

        $picture->setUrl($url);
    }

    public function postRemove(
        Picture $picture,
        PostRemoveEventArgs $postRemoveEventArgs,
    ) {
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        $filename = $picture->getFilename();

        $fullPath = $uploadDirectory . '/' . $filename;

        unlink($fullPath);
    }
}
