<?php

namespace App\Service;

use App\Entity\Video;
use Embera\Embera;

class VideoService
{
    private Embera $embera;
    private ?string $url = null;
    private ?Video $video = null;
    private ?array $urlData = null;

    public function __construct(Video|string $videoOrUrl)
    {
        if (is_string($videoOrUrl)) {
            $this->url = $videoOrUrl;
        }

        if ($videoOrUrl instanceof Video) {
            $this->video = $videoOrUrl;
            $this->url = $this->video->getUrl();
        }

        $this->embera = new Embera();
    }

    private function getUrlData(string $property = ""): mixed
    {
        if (is_null($this->urlData)) {
            $urlData = $this->embera->getUrlData($this->url);
            $this->urlData = $urlData[$this->url] ?? [];
        }

        if ("" === $property) {
            return $this->urlData;
        }

        if (empty($this->urlData)) {
            return null;
        }

        return $this->urlData[$property] ?? null;
    }

    /**
     * Get the iFrame string with the Oembed library.
     * 
     * @return string The iFrame HTML string.
     */
    public function getIframeTag(): string
    {
        $loading = "lazy"; // "lazy" or "eager"

        $this->embera->addFilter(static function ($response) use ($loading) {
            if (!empty($response['html'])) {
                $response['html'] = str_replace('<iframe', "<iframe loading=\"{$loading}\"", $response['html']);
            }

            return $response;
        });

        return $this->getUrlData('html') ?? "";
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->getUrlData('thumbnail_url');
    }

    public function getTitle(): string
    {
        return $this->getUrlData('title') ?? "Video";
    }

    public function isVideo(): bool
    {
        return $this->getUrlData('type') === 'video';
    }
}
