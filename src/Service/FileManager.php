<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Save a file to the specified directory.
     * 
     * @param UploadedFile $file            File to be saved.
     * @param string       $uploadDirectory Directory to save the file in.
     * @param string       $prefix          Optional. Prefix to be added before the saved filename.
     * 
     * @return string|false Returns the filename (without the path) or false on failure.
     */
    public function save(
        UploadedFile $file,
        string $uploadDirectory,
        string $prefix = "",
    ): string|false {
        try {
            // Create a safe filename and check if it's available
            do {
                $safeFilename = ($prefix ? $prefix . "-" : "") . md5($file->getContent()) . '-' . uniqid() . '.' . $file->guessExtension();
            } while (is_file($uploadDirectory . '/' . $safeFilename));

            $file->move($uploadDirectory, $safeFilename);

            return $safeFilename;
        } catch (FileException $e) {
            $this->logger->error($e);

            return false;
        }
    }

    /**
     * Delete a file.
     * 
     * @param string $fullPath 
     * 
     * @return bool `true` on success, `false` on failure.
     */
    public function delete(string $fullPath): bool
    {
        return unlink($fullPath);
    }
}
