<?php

namespace App\Tests\Service;

use App\Service\FileManager;
use App\Service\ImageManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManagerTest extends TestCase
{
    private static string $tmpDirectory;
    private static string $uploadDirectory;
    private LoggerInterface&MockObject $logger;
    private FileManager $fileManager;
    private ImageManager $imageManager;

    public static function setUpBeforeClass(): void
    {
        static::$tmpDirectory = __DIR__ . '/tmp';
        static::$uploadDirectory = static::$tmpDirectory . '/uploads';

        if (!is_dir(static::$tmpDirectory)) {
            mkdir(static::$tmpDirectory);
        }
    }

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fileManager = new FileManager($this->logger);
        $this->imageManager = new ImageManager($this->fileManager);
    }

    // protected function tearDown(): void
    // {
    //     $this->clearUploadDirectory();
    // }

    public static function tearDownAfterClass(): void
    {
        $selfInstance = new self();
        $logger = $selfInstance->createMock(LoggerInterface::class);
        $fileManager = new FileManager($logger);

        $fileManager->clearDirectory(static::$tmpDirectory);

        if (is_dir(static::$uploadDirectory)) {
            rmdir(static::$uploadDirectory);
        }

        if (is_dir(static::$tmpDirectory)) {
            rmdir(static::$tmpDirectory);
        }
    }

    private function clearUploadDirectory(): void
    {
        $this->fileManager->clearDirectory(static::$uploadDirectory);
    }

    private function createTmpImage(): string
    {
        $tmpImagePath = static::$tmpDirectory . '/image.png';
        imagepng(imagecreatetruecolor(2000, 1500), $tmpImagePath);

        return $tmpImagePath;
    }

    public function testSaveImageWithUploadedFile(): void
    {
        $tmpImagePath = $this->createTmpImage();

        $uploadedFile = new UploadedFile(
            $tmpImagePath,
            'image.png',
            'image/png',
            null,
            true
        );

        $filename = $this->imageManager->saveImage(
            $uploadedFile,
            static::$uploadDirectory,
            null,
            [ImageManager::SIZE_THUMBNAIL, ImageManager::SIZE_SMALL],
            false
        );

        $this->assertFileExists(static::$uploadDirectory . '/' . ImageManager::SIZE_ORIGINAL . '/' . $filename);
        $this->assertFileExists(static::$uploadDirectory . '/' . ImageManager::SIZE_THUMBNAIL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');
        $this->assertFileExists(static::$uploadDirectory . '/' . ImageManager::SIZE_SMALL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');

        $this->clearUploadDirectory();
    }

    public function testSaveImageWithRawContent(): string
    {
        $tmpImagePath = $this->createTmpImage();
        $rawContent = file_get_contents($tmpImagePath);

        $filename = $this->imageManager->saveImage(
            $rawContent,
            static::$uploadDirectory,
            null,
            [ImageManager::SIZE_THUMBNAIL, ImageManager::SIZE_SMALL],
            false
        );

        $this->assertFileExists(static::$uploadDirectory . '/' . ImageManager::SIZE_ORIGINAL . '/' . $filename);
        $this->assertFileExists(static::$uploadDirectory . '/' . ImageManager::SIZE_THUMBNAIL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');
        $this->assertFileExists(static::$uploadDirectory . '/' . ImageManager::SIZE_SMALL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');

        unlink($tmpImagePath);

        return $filename;
    }

    /**
     * @depends testSaveImageWithRawContent
     */
    public function testGetImagePathWithProvidedSize(string $filename): void
    {
        $imagePath = $this->imageManager->getImagePath(
            static::$uploadDirectory,
            $filename,
            ImageManager::SIZE_THUMBNAIL
        );

        $this->assertEquals(
            static::$uploadDirectory . '/' . ImageManager::SIZE_THUMBNAIL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp',
            $imagePath
        );
    }

    /**
     * @depends testSaveImageWithRawContent
     */
    public function testGetImagePathWithMissingSize(string $filename): void
    {
        $imagePath = $this->imageManager->getImagePath(
            static::$uploadDirectory,
            $filename,
            ImageManager::SIZE_MEDIUM // Size medium should not exist
        );

        $this->assertEquals(
            static::$uploadDirectory . '/' . ImageManager::SIZE_ORIGINAL . '/' . $filename,
            $imagePath
        );
    }

    /**
     * @depends testSaveImageWithRawContent
     */
    public function testDeleteImage(string $filename): void
    {
        $this->imageManager->deleteImage(static::$uploadDirectory, $filename);

        $this->assertFileDoesNotExist(static::$uploadDirectory . '/' . ImageManager::SIZE_ORIGINAL . '/' . $filename);
        $this->assertFileDoesNotExist(static::$uploadDirectory . '/' . ImageManager::SIZE_THUMBNAIL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');
        $this->assertFileDoesNotExist(static::$uploadDirectory . '/' . ImageManager::SIZE_SMALL . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');

        $this->clearUploadDirectory();
    }
}
