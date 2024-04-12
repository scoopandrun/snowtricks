<?php

namespace App\Service;

use App\Utils\QuantityFormatter;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
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
        ?string $filename = null,
        bool $unique = true,
    ): string|false {
        try {
            $safeFilename = $this->makeFilename(
                $file->getContent(),
                $uploadDirectory,
                $filename,
                (string) $file->guessExtension(),
                $unique,
            );

            // Make sure we have an extension
            // (we might not have one when saving a user profile picture as only the ID is passed to the function)
            if (!pathinfo($filename, PATHINFO_EXTENSION)) {
                $filename = $filename . '.' . (string) $file->guessExtension();
            }

            $file->move($uploadDirectory, $safeFilename);

            return $safeFilename;
        } catch (FileException $e) {
            $this->logger->error($e);

            return false;
        }
    }

    /**
     * Save a raw file (string) to disk.
     * 
     * @param string $fileContent 
     * @param string $uploadDirectory 
     * @param null|string $filename 
     * @param bool $unique 
     * 
     * @return string|false Filename of the saved file of false on failure.
     */
    public function saveRawFile(
        string $fileContent,
        string $uploadDirectory,
        ?string $filename,
        bool $unique = false,
    ): string|false {
        try {
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, recursive: true);
            }

            $extension = pathinfo((string) $filename, PATHINFO_EXTENSION);

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
                $filename,
                $extension,
                $unique
            );

            if (!file_put_contents($uploadDirectory . '/' . $safeFilename, $fileContent)) {
                return false;
            }

            return $safeFilename;
        } catch (\Exception $e) {
            $this->logger->error($e);

            return false;
        }
    }

    /**
     * Save an image (GDImage) to WebP.
     * 
     * @param \GdImage $image 
     * @param string $uploadDirectory 
     * @param string $filename 
     */
    public function saveGdImageToWebp(
        \GdImage $image,
        string $uploadDirectory,
        string $filename,
    ): void {
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, recursive: true);
        }

        $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . ".webp";
        $filepath = $uploadDirectory . '/' . $webpFilename;

        if (imagewebp($image, $filepath, 100) === false) {
            throw new \Exception("Unable to save the image {$filename} to WebP.");
        }
    }

    /**
     * Make a safe filename for a file.
     * 
     * @param string $fileContent 
     * @param string $uploadDirectory 
     * @param null|string $filename 
     * @param string $extension 
     * @param bool $unique 
     * 
     * @return string Safe filename.
     */
    private function makeFilename(
        string $fileContent,
        string $uploadDirectory,
        ?string $filename,
        string $extension,
        bool $unique
    ): string {
        if (!$fileContent) {
            throw new \InvalidArgumentException("The file content is empty.");
        }

        if ($filename && !$unique) {
            return pathinfo($filename, PATHINFO_FILENAME) . '.' . $extension;
        }

        // Create a safe filename and check if it's available
        $filename = pathinfo($filename, PATHINFO_FILENAME) ?: md5($fileContent);
        do {
            $safeFilename = $filename . ($unique ? '-' . uniqid() : '') . '.' . $extension;
            if (!$unique) break;
        } while (is_file($uploadDirectory . '/' . $safeFilename));

        return $safeFilename;
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

        if (is_file((string) $fullPath)) {
            return unlink($fullPath);
        } else {
            return true;
        }
    }

    public function getFullpath(
        string $directory,
        string $filename,
    ): ?string {
        if (!is_dir($directory)) {
            return null;
        }

        $directoryIterator = new \DirectoryIterator($directory);

        foreach ($directoryIterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $extension = $fileInfo->getExtension();
                $currentFilename = $fileInfo->getBasename('.' . $extension);

                if ($currentFilename === pathinfo($filename, PATHINFO_FILENAME)) {
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
            if ($fileInfo->isFile()) {
                unlink($fileInfo->getRealPath());
            }

            if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                $this->clearDirectory($fileInfo->getRealPath());
                rmdir($fileInfo->getRealPath());
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
