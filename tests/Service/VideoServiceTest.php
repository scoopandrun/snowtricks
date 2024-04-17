<?php

namespace App\Tests\Service;

use App\Entity\Video;
use App\Service\VideoService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class VideoServiceTest extends TestCase
{
    private VideoService $videoService;
    private HttpClientInterface&MockObject $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->videoService = new VideoService($this->httpClient);
    }

    public function testPopulateInfo(): void
    {
        $video = new Video();
        $video->setUrl('https://www.youtube.com/watch?v=abc123');

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3Dabc123')
            ->willReturn($this->createMockSuccessResponse());

        $this->videoService->populateInfo($video);

        $this->assertEquals('https://www.youtube.com/watch?v=abc123', $video->getUrl());
        $this->assertEquals('https://i.ytimg.com/vi/abc123/hqdefault.jpg', $video->getThumbnailUrl());
        $this->assertEquals('Video Title', $video->getTitle());
    }

    public function testGetIframeTag(): void
    {
        $iframeTag = $this->videoService->getIframeTag('https://www.youtube.com/watch?v=abc123');

        $expectedIframeTag = '<iframe src="https://www.youtube.com/embed/abc123" loading="lazy" frameborder="0" allow="fullscreen; accelerometer; gyroscope; encrypted-media; picture-in-picture; web-share"></iframe>';

        $this->assertEquals($expectedIframeTag, $iframeTag);
    }

    public function testIsSupported(): void
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3Dabc456')
            ->willReturn($this->createMockSuccessResponse());

        $isSupported = $this->videoService->isSupported('https://www.youtube.com/watch?v=abc456');

        $this->assertTrue($isSupported);
    }

    public function testIsNotSupported(): void
    {
        // Not a valid URL
        $isSupported = $this->videoService->isSupported('not a url');
        $this->assertFalse($isSupported);

        // Not a supported provider
        $isSupported = $this->videoService->isSupported('https://www.unsupported.com');
        $this->assertFalse($isSupported);

        // Not a valid video
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3Dabc789')
            ->willReturn($this->createMockFailureResponse());

        $isSupported = $this->videoService->isSupported('https://www.youtube.com/watch?v=abc789');
        $this->assertFalse($isSupported);
    }

    private function createMockSuccessResponse(): ResponseInterface&MockObject
    {
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(200);

        $mockResponse
            ->method('toArray')
            ->willReturn([
                'thumbnail_url' => 'https://i.ytimg.com/vi/abc123/hqdefault.jpg',
                'title' => 'Video Title',
                'type' => 'video',
            ]);

        return $mockResponse;
    }

    private function createMockFailureResponse(): ResponseInterface&MockObject
    {
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(404);

        return $mockResponse;
    }
}
