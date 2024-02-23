<?php

namespace App\Service;

use App\Repository\TrickRepository;

class TrickService
{
    public function __construct(
        private TrickRepository $trickRepository
    ) {
    }

    public function findAll(): array
    {
        return $this->trickRepository->findAll();
    }
}
