<?php

namespace App\DTO;

class TrickCard
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $mainPicture = null,
        public readonly ?string $category = null,
    ) {
    }
}
