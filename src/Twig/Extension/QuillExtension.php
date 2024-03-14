<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\QuillExtensionRuntime;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class QuillExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter(
                'quill',
                [QuillExtensionRuntime::class, 'quillFilter'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
