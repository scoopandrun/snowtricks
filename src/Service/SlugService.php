<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class SlugService
{
    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    public function makeSlug(string $name): string
    {
        $slug = strtolower($this->slugger->slug($name));

        return $slug;
    }
}
