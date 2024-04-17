<?php

namespace App\Tests\Entity;

use App\Entity\Trick;
use App\Entity\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function testInstanciation(): Video
    {
        $video = new Video();
        $this->assertInstanceOf(Video::class, $video);

        return $video;
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetTrick(Video $video): void
    {
        $trick = new Trick();
        $video->setTrick($trick);
        $this->assertSame($trick, $video->getTrick());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetUrl(Video $video): void
    {
        $url = "https://...";
        $video->setUrl($url);
        $this->assertSame($url, $video->getUrl());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetIframe(Video $video): void
    {
        $iframe = "<iframe>...</iframe>";
        $video->setIframe($iframe);
        $this->assertSame($iframe, $video->getIframe());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetThumbnailUrl(Video $video): void
    {
        $thumbnailUrl = "https://...";
        $video->setThumbnailUrl($thumbnailUrl);
        $this->assertSame($thumbnailUrl, $video->getThumbnailUrl());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetTitle(Video $video): void
    {
        $title = "Lorem ipsum";
        $video->setTitle($title);
        $this->assertSame($title, $video->getTitle());
    }

    /**
     * @depends testInstanciation
     */
    public function testToString(Video $video): void
    {
        $url = "https://...";
        $video->setUrl($url);
        $this->assertSame($url, (string) $video);
    }
}
