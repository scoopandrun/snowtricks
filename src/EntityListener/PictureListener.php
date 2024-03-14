<?php

namespace App\EntityListener;

use App\Entity\Picture;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: Events::prePersist, method: 'onPrePersist', entity: Picture::class)]
#[AsEntityListener(event: Events::postLoad, method: 'onPostLoad', entity: Picture::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onPostRemove', entity: Picture::class)]
class PictureListener
{
    public function __construct(private readonly string $trickPicturesUploadDirectory)
    {
    }

    public function onPrePersist(
        Picture $picture,
        PrePersistEventArgs $prePersistEventArgs,
    ): void {
        // Save the file
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        $file = $picture->getFile();

        $safeFilename = hash('md5', $file->getContent()) . '-' . uniqid() . '.' . $file->guessExtension();

        $picture->setFilename($safeFilename);

        $file->move($uploadDirectory, $safeFilename);
    }

    public function onPostLoad(
        Picture $picture,
        PostLoadEventArgs $postLoadEventArgs,
    ): void {
        // Set the URL
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        $filename = $picture->getFilename();

        $url = preg_replace('#.*/public#', '', $uploadDirectory) . '/' . $filename;

        $picture->setUrl($url);
    }

    public function onPostRemove(
        Picture $picture,
        PostRemoveEventArgs $postRemoveEventArgs,
    ): void {
        // Delete the file
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        $filename = $picture->getFilename();

        $fullPath = $uploadDirectory . '/' . $filename;

        unlink($fullPath);
    }
}
