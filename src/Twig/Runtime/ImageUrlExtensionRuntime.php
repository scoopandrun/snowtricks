<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class ImageUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly string $tricksPicturesUploadsDirectory,
    ) {
        // Inject dependencies if needed
    }

    public function getUrl(string $library, ?string $filename): ?string
    {
        $directory = null;

        // Try directory mapping
        $directory = match ($library) {
            'tricks' => $this->tricksPicturesUploadsDirectory,
            default => null,
        };

        if (is_null($directory)) {
            return null;
        }

        // Check if the file exists
        $fullPath = $directory . '/' . $filename;

        if (!is_file($fullPath)) {
            return null;
        }

        $url = preg_replace('#.*/public#', '', $directory) . '/' . $filename;

        return $url;
    }
}
