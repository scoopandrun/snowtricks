<?php

namespace App\Tests\Service;

use App\Service\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerTest extends TestCase
{
    private static string $tmpDirectory;
    private static string $uploadDirectory;
    private LoggerInterface&MockObject $logger;
    private FileManager $fileManager;

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
    }

    protected function tearDown(): void
    {
        $this->clearUploadDirectory();
    }

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

    public function testSaveUploadedFile(): void
    {
        file_put_contents(__DIR__ . '/tmp/test.txt', 'This is a test file content.');

        $file = new UploadedFile(
            static::$tmpDirectory . '/test.txt',
            'test.txt',
            'text/plain',
            null,
            true
        );

        $filename = $this->fileManager->saveUploadedFile($file, static::$uploadDirectory);

        $this->assertFileExists(static::$uploadDirectory . '/' . $filename);
    }

    public function testSaveRawFile(): void
    {
        $fileContent = 'This is a test file content.';
        $filename = 'test.txt';

        $savedFilename = $this->fileManager->saveRawFile($fileContent, static::$uploadDirectory, $filename);

        $this->assertFileExists(static::$uploadDirectory . '/' . $savedFilename);
    }

    public function testSaveGdImageToWebp(): void
    {
        $image = imagecreatetruecolor(100, 100);
        $filename = 'test.png';

        $this->fileManager->saveGdImageToWebp($image, static::$uploadDirectory, $filename);

        $this->assertFileExists(static::$uploadDirectory . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp');
    }

    public function testDelete(): void
    {
        $fileContent = 'This is a test file content.';
        $filename = 'test.txt';

        $savedFilename = $this->fileManager->saveRawFile($fileContent, static::$uploadDirectory, $filename);

        $this->assertTrue($this->fileManager->delete(null, static::$uploadDirectory, $savedFilename));
        $this->assertFileDoesNotExist(static::$uploadDirectory . '/' . $savedFilename);
    }

    public function testClearDirectory(): void
    {
        $this->fileManager->clearDirectory(static::$uploadDirectory);

        $directoryIterator = new \DirectoryIterator(static::$uploadDirectory);

        $fileAndDirCount = 0;

        foreach ($directoryIterator as $fileInfo) {
            if (!$fileInfo->isDot()) {
                $fileAndDirCount++;
            }
        }

        $this->assertEquals(0, $fileAndDirCount);
    }

    public function testClearInvalidDirectory(): void
    {
        $this->logger->expects($this->once())
            ->method('debug')
            ->with("'not a valid directory' is not a directory!");

        $this->fileManager->clearDirectory('not a valid directory');
    }

    public function testGetUploadMaxFilesize(): void
    {
        $uploadMaxFilesize = FileManager::getUploadMaxFilesize('M', true);

        $this->assertStringContainsString('MB', $uploadMaxFilesize);
    }

    public function testGetPostMaxSize(): void
    {
        $uploadMaxFilesize = FileManager::getPostMaxSize('M', true);

        $this->assertStringContainsString('MB', $uploadMaxFilesize);
    }
}
