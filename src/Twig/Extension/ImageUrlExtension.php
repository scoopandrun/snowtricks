<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\ImageUrlExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageUrlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('image', [ImageUrlExtensionRuntime::class, 'getUrl']),
        ];
    }
}
