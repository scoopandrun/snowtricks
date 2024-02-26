<?php

namespace App\Service;

use App\Core\Component\Batch;
use App\Entity\Trick;
use App\Repository\TrickRepository;

class TrickService
{
    public function __construct(
        private TrickRepository $trickRepository
    ) {
    }

    public function findOne(): ?Trick
    {
        return new Trick();
    }

    public function findAll(): array
    {
        return $this->trickRepository->findAll();
    }

    public function getCount(): int
    {
        return $this->trickRepository->count();
    }

    /**
     * Get a batch of tricks from the database.
     * 
     * @param int $batchNumber 
     * @param int $batchSize   Number of tricks to show in the batch.
     */
    public function getBatch(int $batchNumber = 1, int $batchSize = 4): Batch
    {
        $count = $this->trickRepository->count();

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $batchNumber = max((int) $batchNumber, 1);

        // Show last page in case $pageNumber is too high
        if ($count < ($batchNumber * $batchSize)) {
            $batchNumber = max(ceil($count / $batchSize), 1);
        }

        $offset = ($batchNumber - 1) * $batchSize;

        $tricks = $this->trickRepository->findBy([], offset: $offset, limit: $batchSize);

        return new Batch(
            $tricks,
            pageNumber: $batchNumber,
            firstIndex: $offset + 1,
            totalCount: $count
        );
    }

    private function makeSlug(Trick $trick): string
    {
        $name = $trick->getName();

        $slug = strtolower($name);
        $slug = preg_replace(" ", "-", $slug);
        $slug = preg_replace("/[\"'\(\)\[\]\{\}#]/", "", $slug);

        return $slug;
    }
}
