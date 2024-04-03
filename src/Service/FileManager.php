<?php

namespace App\Service;

use App\Utils\QuantityFormatter;
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

    public function saveRawFile(
        string $fileContent,
        string $uploadDirectory,
        string $filename,
        bool $unique = false,
    ): string|false {
        try {
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, recursive: true);
            }

            $safeFilename = $filename;

            if ($unique) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);

                // If there is no extension, save to a temporary file and guess the MIME type
                // Not 100% accurate, but will do the job most of the time
                if (!$extension) {
                    try {
                        $tmpPath = $uploadDirectory . '/' . bin2hex(random_bytes(10));

                        if (!file_put_contents($tmpPath, $fileContent)) {
                            throw new \Exception();
                        }

                        $mime = mime_content_type($tmpPath);

                        if (!$mime) {
                            throw new \Exception();
                        }

                        $extension = explode('/', $mime)[1];
                    } catch (\Exception) {
                        // Unable to guess the extension
                    } finally {
                        if (is_file($tmpPath)) {
                            unlink($tmpPath);
                        }
                    }
                }

                $safeFilename = $this->makeFilename(
                    $fileContent,
                    $uploadDirectory,
                    $extension
                );
            }


            if (!file_put_contents($uploadDirectory . '/' . $safeFilename, $fileContent)) {
                return false;
            }

            return $safeFilename;
        } catch (\Exception $e) {
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

    public function clearDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            $this->logger->debug(sprintf("%s is not a directory!", $directory));
            return;
        }

        $directoryIterator = new \DirectoryIterator($directory);

        foreach ($directoryIterator as $fileInfo) {
            if (is_file($fileInfo->getRealPath())) {
                unlink($fileInfo->getRealPath());
            }
        }
    }

    /**
     * Get the php.ini setting `upload_max_filesize`, with the desired format.
     * 
     * Available units:
     * 
     * | format | description                 |
     * |--------|-----------------------------|
     * |  null  | the original php.ini value  |
     * |   B    | value in bytes (B)          |
     * |   K    | value in kilobytes (KB)     |
     * |   M    | value in megabytes (MB)     |
     * |   G    | value in gigabytes (GB)     |
     * |  auto  | value with the closest unit |
     * 
     * @param null|string $unit        See table above.
     * @param bool        $displayUnit Add the unit at the end of the value. Eg: '2MB'.
     * 
     * @return string Formatted max file size value.
     */
    public static function getUploadMaxFilesize(
        ?string $unit = null,
        bool $displayUnit = false,
    ): string {
        /** @var string Original php.ini value. */
        $phpIniUploadMaxFilesize = ini_get('upload_max_filesize');

        return QuantityFormatter::formatQuantity($phpIniUploadMaxFilesize, $unit, $displayUnit);
    }

    /**
     * Get the php.ini setting `post_max_size`, with the desired format.
     * 
     * Available units:
     * 
     * | format | description                 |
     * |--------|-----------------------------|
     * |  null  | the original php.ini value  |
     * |   B    | value in bytes (B)          |
     * |   K    | value in kilobytes (KB)     |
     * |   M    | value in megabytes (MB)     |
     * |   G    | value in gigabytes (GB)     |
     * |  auto  | value with the closest unit |
     * 
     * @param null|string $unit        See table above.
     * @param bool        $displayUnit Add the unit at the end of the value. Eg: '2MB'.
     * 
     * @return string Formatted max file size value.
     */
    public static function getPostMaxSize(
        ?string $unit = null,
        bool $displayUnit = false,
    ): string {
        /** @var string Original php.ini value. */
        $phpIniPostMaxSize = ini_get('post_max_size');

        return QuantityFormatter::formatQuantity($phpIniPostMaxSize, $unit, $displayUnit);
    }
}
