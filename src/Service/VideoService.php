<?php

namespace App\Service;

use App\Entity\Video;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VideoService
{
    private static array $oembedData = [];
    private static array $providerInfo = [];

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * Fill in the iFrame tag, thumbnail URL and title for a video.
     * 
     * @param Video $video 
     * 
     * @return void 
     */
    public function populateInfo(Video $video): void
    {
        $url = $video->getUrl();

        $video
            ->setIframe($this->getIframeTag($url))
            ->setThumbnailUrl($this->getThumbnailUrl($url))
            ->setTitle($this->getTitle($url));
    }

    private function getOembedData(string $url, string $property = ""): mixed
    {
        if (is_null(self::$oembedData[$url] ?? null)) {
            $oembedUrl = $this->getProviderInfo($url)["oembedUrl"];
            $oembedData = $this->fetchOembedData($oembedUrl);
            self::$oembedData[$url] = $oembedData;
        }

        if ("" === $property) {
            return self::$oembedData[$url];
        }

        if (is_null(self::$oembedData[$url])) {
            return null;
        }

        return self::$oembedData[$url][$property] ?? null;
    }

    private function fetchOembedData(string $url): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $url);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                return null;
            }

            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the iFrame string with the Oembed library.
     * 
     * @return string|false The iFrame HTML string or false if the URL isn't suported.
     */
    private function getIframeTag(string $url): string|false
    {
        $providerInfo = $this->getProviderInfo($url);

        if (is_null($providerInfo)) {
            return false;
        }

        $embedUrl = $providerInfo["embedUrl"];

        $iframeTemplate = '<iframe src="<url>" loading="lazy" frameborder="0" allow="fullscreen; accelerometer; gyroscope; encrypted-media; picture-in-picture; web-share"></iframe>';

        $iframeTag = preg_replace("/<url>/", $embedUrl, $iframeTemplate);

        return $iframeTag;
    }

    /**
     * Get the thumbnail URL for a video URL.
     * 
     * @return null|string 
     */
    private function getThumbnailUrl(string $url): ?string
    {
        return $this->getOembedData($url, 'thumbnail_url');
    }

    private function getTitle(string $url): string
    {
        return $this->getOembedData($url, 'title') ?? "Video";
    }

    private function getProviderInfo(string $url): array|null
    {
        if (self::$providerInfo[$url] ?? null) {
            return self::$providerInfo[$url];
        }

        $providers = [
            "YouTube" => [
                "urlTemplates" => [
                    "https://www.youtube.com/watch?v=<id>",
                    "https://m.youtube.com/watch?v=<id>",
                    "https://youtu.be/<id>",
                ],
                "normalizedUrlTemplate" => "https://www.youtube.com/watch?v=<id>",
                "embedUrlTemplate" => "https://www.youtube.com/embed/<id>",
                "oembedUrlTemplate" => "https://www.youtube.com/oembed?url=<normalizedUrl>",
                "videoIdRegex" => "\w+",
            ],
            "Dailymotion" => [
                "urlTemplates" => [
                    "https://www.dailymotion.com/video/<id>",
                    "https://dai.ly/<id>",
                ],
                "normalizedUrlTemplate" => "https://www.dailymotion.com/video/<id>",
                "embedUrlTemplate" => "https://www.dailymotion.com/embed/video/<id>",
                "oembedUrlTemplate" => "https://www.dailymotion.com/services/oembed?url=<normalizedUrl>",
                "videoIdRegex" => "\w+",
            ],
            "Vimeo" => [
                "urlTemplates" => [
                    "https://vimeo.com/<id>",
                ],
                "normalizedUrlTemplate" => "https://vimeo.com/<id>",
                "embedUrlTemplate" => "https://player.vimeo.com/video/<id>",
                "oembedUrlTemplate" => "https://vimeo.com/api/oembed.json?url=<normalizedUrl>",
                "videoIdRegex" => "\d+",
            ],
        ];

        /** @var ?string */
        $providerName = null;

        /** @var ?string */
        $urlTemplate = null;

        /** @var ?string */
        $normalizedUrlTemplate = null;

        /** @var ?string */
        $embedUrlTemplate = null;

        /** @var ?string */
        $oembedUrlTemplate = null;

        /** @var ?string */
        $videoIdRegex = null;

        foreach ($providers as $name => $data) {
            $templateFound = false;

            $urlTemplates = $data["urlTemplates"];

            foreach ($urlTemplates as $template) {
                if (preg_match('#' . preg_quote(str_replace("<id>", "", $template), '/') . '#', $url)) {
                    $providerName = $name;
                    $urlTemplate = $template;
                    $normalizedUrlTemplate = $data["normalizedUrlTemplate"];
                    $embedUrlTemplate = $data["embedUrlTemplate"];
                    $oembedUrlTemplate = $data["oembedUrlTemplate"];
                    $videoIdRegex = $data["videoIdRegex"];
                    $templateFound = true;
                    break;
                }
            }
            if ($templateFound) break;
        }

        if (false === $templateFound) {
            return null;
        }

        $regex = str_replace(
            preg_quote("<id>"),
            '(' . $videoIdRegex . ')',
            preg_quote($urlTemplate)
        );

        preg_match(
            '#' . $regex . '#i',
            $url,
            $matches
        );

        $videoId = $matches[1] ?? null;

        $normalizedUrl = str_replace("<id>", $videoId, $normalizedUrlTemplate);

        $embedUrl = str_replace("<id>", $videoId, $embedUrlTemplate);

        $oembedUrl = str_replace("<normalizedUrl>", urlencode($normalizedUrl), $oembedUrlTemplate);

        $providerInfo = [
            "name" => $providerName,
            "videoId" => $videoId,
            "normalizedUrl" => $normalizedUrl,
            "embedUrl" => $embedUrl,
            "oembedUrl" => $oembedUrl,
        ];

        self::$providerInfo[$url] = $providerInfo;

        return $providerInfo;
    }

    public function isSupported(string $url): bool
    {
        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            return false;
        };

        return $this->getOembedData($url, 'type') === 'video';
    }
}
