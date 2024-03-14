<?php

namespace App\Service;

use App\Entity\Trick;
use App\Entity\Picture;
use Psr\Log\LoggerInterface;
use App\Component\Batch;
use App\Repository\TrickRepository;

class TrickService
{
    public function __construct(
        private TrickRepository $trickRepository,
        private string $trickPicturesUploadDirectory,
        private LoggerInterface $logger,
        private SlugService $slugService,
    ) {
    }

    public function findById(int $id): ?Trick
    {
        return $this->trickRepository->find($id);
    }

    public function findBySlug(string $slug): ?Trick
    {
        return $this->trickRepository->findOneBy(["slug" => $slug]);
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

    public function setSlug(Trick $trick): void
    {
        $slug = $this->slugService->makeSlug($trick->getName());
        $trick->setSlug($slug);
    }

    public function setThumbnail(Trick $trick): void
    {
        $currentThumbnail = $trick->getThumbnail();

        $currentThumbnailIsInCollection = $trick->getPictures()->contains($currentThumbnail);

        if (!$currentThumbnail || $currentThumbnailIsInCollection === false) {
            /** @var Picture|false $firstPicture */
            $firstPicture = $trick->getPictures()->first();

            $trick->setThumbnail($firstPicture ?: null);
        }
    }
}
