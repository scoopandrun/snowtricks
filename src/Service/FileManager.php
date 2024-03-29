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
     * Save an uploaded file to the specified directory.
     * 
     * @param UploadedFile $file            File to be saved.
     * @param string       $uploadDirectory Directory to save the file in.
     * @param ?string      $filename        Optional. Set the filename.  
     *                                      If null, the filename will be based on the file's contents hash.
     * @param bool         $overwrite       Optional. Overwrite the file if the name already exists. Default = false.
     * 
     * @return string|false Returns the filename (without the path) or false on failure.
     */
    public function saveUploadedFile(
        UploadedFile $file,
        string $uploadDirectory,
        ?string $filename = null
    ): string|false {
        try {
            $filename = $filename ?? $this->makeFilename(
                $file->getContent(),
                $uploadDirectory,
                $file->guessExtension(),
                $filename
            );

            $file->move($uploadDirectory, $filename);

            return $filename;
        } catch (FileException $e) {
            $this->logger->error($e);

            return false;
        }
    }

    public function makeFilename(
        string $fileContent,
        string $uploadDirectory,
        string $extension
    ): string {
        if (!$fileContent) {
            throw new \InvalidArgumentException("The file content is empty.");
        }

        // Create a safe filename and check if it's available
        $hash = md5($fileContent);
        do {
            $filename = $hash . '-' . uniqid() . '.' . $extension;
        } while (is_file($uploadDirectory . '/' . $filename));

        return $filename;
    }

    /**
     * Delete a file.
     * 
     * You must provide one of this:
     * - a fullpath, or...
     * - ... a filename AND directory
     * 
     * @param ?string $fullPath  Full path to the file.
     * @param ?string $directory Directory of the file.
     * @param ?string $filename  Filename.
     * 
     * @throws \LogicException
     * 
     * @return bool `true` on success, `false` on failure.
     */
    public function delete(
        ?string $fullPath = null,
        ?string $directory = null,
        ?string $filename = null,
    ): bool {
        if (is_null($fullPath) && (is_null($filename) || is_null($directory))) {
            throw new \LogicException("You must provide either a full path or a filename and directory");
        }

        $fullPath = $fullPath ?: $this->getFullpath($directory, $filename);

        if (is_file($fullPath)) {
            return unlink($fullPath);
        } else {
            return true;
        }
    }

    public function getFullpath(
        string $directory,
        string $filename,
        bool $freeExtension = true,
    ): ?string {
        if (!is_dir($directory)) {
            return null;
        }

        $directoryIterator = new \DirectoryIterator($directory);

        foreach ($directoryIterator as $fileInfo) {
            if (is_file($fileInfo->getRealPath())) {
                $extension = $fileInfo->getExtension();
                $currentFilename = $fileInfo->getBasename($freeExtension ? '.' . $extension : '');

                if ($currentFilename === $filename) {
                    return $fileInfo->getRealPath();
                }
            }
        }

        // File not found
        return null;
    }
}
