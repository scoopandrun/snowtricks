<?php

namespace App\Event;

use App\Entity\Trick;

class TrickUpdatedEvent
{
    public function __construct(
        private Trick $trick,
    ) {
    }

    public function getTrick(): Trick
    {
        return $this->trick;
    }
}
