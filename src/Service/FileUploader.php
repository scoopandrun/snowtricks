<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct()
    {
    }

    public function save(UploadedFile $file, string $uploadDirectory): string|false
    {
        try {
            $safeFilename = md5($file->getContent()) . '-' . uniqid() . '.' . $file->guessExtension();

            $file->move($uploadDirectory, $safeFilename);

            return $safeFilename;
        } catch (FileException $e) {
            return false;
        }
    }
}
