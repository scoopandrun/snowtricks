<?php

namespace App\Twig\Runtime;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\RuntimeExtensionInterface;

class ImageUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        #[Autowire('%app.uploads.pictures%/')]
        private readonly string $picturesUploadsDirectory,
    ) {
        // Inject dependencies if needed
    }

    public function getUrl(string $library, ?string $filename): ?string
    {
        $directory = null;

        // Try directory mapping
        $directory = match ($library) {
            'tricks' => $this->picturesUploadsDirectory . 'tricks',
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
