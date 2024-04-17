<?php

namespace App\Service;

use App\Component\Batch;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Repository\TrickRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TrickService
{
    public function __construct(
        private TrickRepository $trickRepository,
        private LoggerInterface $logger,
        private SlugService $slugService,
        private ImageManager $imageManager,
        #[Autowire('%app.uploads.pictures%/tricks')]
        private readonly string $tricksPicturesUploadsDirectory,
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
        // Default main picture = first of collection
        $trick->setMainPicture($trick->getPictures()->first() ?: null);

        foreach ($trick->getPictures() as $picture) {
            if (true === $picture->getSetAsMainPicture()) {
                $trick->setMainPicture($picture);
                break;
            }
        }
    }

    public function saveTrickPicture(Picture $picture): bool
    {
        $filename = $this->imageManager->saveImage(
            $picture->getFile(),
            $this->tricksPicturesUploadsDirectory,
            sizes: ImageManager::SIZE_ALL,
            unique: true,
        );

        $picture->setFilename($filename);

        return (bool) $filename;
    }

    public function deleteTrickPicture(Picture $picture): void
    {
        $this->imageManager->deleteImage(
            $this->tricksPicturesUploadsDirectory,
            $picture->getFilename()
        );
    }

    public function getTrickPicturePath(?string $filename, string $size = ImageManager::SIZE_ORIGINAL): ?string
    {
        if (is_null($filename)) {
            return null;
        }

        $fullpath = $this->imageManager->getImagePath(
            $this->tricksPicturesUploadsDirectory,
            $filename,
            $size
        );

        return $fullpath;
    }
}
