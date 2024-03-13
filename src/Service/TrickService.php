<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;
use Psr\Log\LoggerInterface;
use App\Core\Component\Batch;
use App\Repository\TrickRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class TrickService
{
    public function __construct(
        private TrickRepository $trickRepository,
        private string $trickPicturesUploadDirectory,
        private LoggerInterface $logger,
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

    /**
     * Save trick pictures.
     * 
     * @param FormInterface  $picturesForms 
     * @param Trick          $trick
     * 
     * @return bool `true` if the pictures have been saved, `false` in case of an error
     */
    public function savePictures(FormInterface $picturesForms, Trick $trick): bool
    {
        $uploadDirectory = $this->trickPicturesUploadDirectory;

        for ($i = 0; $i < count($picturesForms->getData()); $i++) {
            $pictureForm = $picturesForms->get("$i");

            /** @var UploadedFile $file */
            $file = $pictureForm->get('file')->getData();

            /** @var string $description */
            $description = $pictureForm->get('description')->getData();

            $safeFilename = hash('md5', $file->getContent()) . '-' . uniqid() . '.' . $file->guessExtension();

            try {

                $file->move($uploadDirectory, $safeFilename);

                $picture = (new Picture())
                    ->setFilename($safeFilename)
                    ->setDescription($description);

                $trick->addPicture($picture);

                if (is_null($trick->getThumbnail())) {
                    $trick->setThumbnail($picture);
                }
            } catch (FileException $e) {
                $this->logger->error($e);

                return false;
            }
        }

        return true;
    }
}
