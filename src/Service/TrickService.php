<?php

namespace App\Service;

use App\Component\Batch;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Repository\TrickRepository;
use App\Service\FileManager;
use Psr\Log\LoggerInterface;

class TrickService
{
    public function __construct(
        private TrickRepository $trickRepository,
        private readonly string $tricksPicturesUploadsDirectory,
        private LoggerInterface $logger,
        private SlugService $slugService,
        private FileManager $fileManager,
    ) {
    }

    /**
     * Get a batch of tricks from the database.
     * 
     * @param int $batchNumber 
     * @param int $batchSize   Number of tricks to show in the batch.
     * 
     * @return Batch<App\DTO\TrickCardDTO>
     */
    public function getBatch(int $batchNumber = 1, int $batchSize = 10): Batch
    {
        $count = $this->trickRepository->count();

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $batchNumber = max((int) $batchNumber, 1);

        // Show last page in case $pageNumber is too high
        if ($count < ($batchNumber * $batchSize)) {
            $batchNumber = max(ceil($count / $batchSize), 1);
        }

        $offset = ($batchNumber - 1) * $batchSize;

        $tricks = $this->trickRepository->findTrickCards(offset: $offset, limit: $batchSize);

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

    public function setMainPicture(Trick $trick): void
    {
        $currentMainPicture = $trick->getMainPicture();

        $currentMainPictureIsInCollection = $trick->getPictures()->contains($currentMainPicture);

        if (is_null($currentMainPicture) || false === $currentMainPictureIsInCollection) {
            /** @var Picture|false $firstPicture */
            $firstPicture = $trick->getPictures()->first();

            $trick->setMainPicture($firstPicture ?: null);
        }
    }

    public function saveTrickPicture(Picture $picture): bool
    {
        $file = $picture->getFile();

        $filename = $this->fileManager->save($file, $this->tricksPicturesUploadsDirectory);

        $picture->setFilename($filename);

        return (bool) $filename;
    }

    public function deleteTrickPicture(Picture $picture): bool
    {
        $uploadDirectory = $this->tricksPicturesUploadsDirectory;

        $filename = $picture->getFilename();

        $fullPath = $uploadDirectory . '/' . $filename;

        return $this->fileManager->delete($fullPath);
    }
}
