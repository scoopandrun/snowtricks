<?php

namespace App\DTO;

class TrickCardDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $mainPicture = null,
        public readonly ?string $category = null,
        public readonly ?string $difficulty = null,
    ) {
    }
}
