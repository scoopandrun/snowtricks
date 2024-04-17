<?php

namespace App\Service;

use App\Component\Batch;
use App\Entity\Comment;
use App\Repository\CommentRepository;

class CommentService
{
    public function __construct(
        private CommentRepository $commentRepository,
    ) {
    }

    /**
     * Get a batch of comments from the database.
     * 
     * @param int $trickId
     * @param int $batchNumber 
     * @param int $batchSize   Number of comments to show in the batch.
     * 
     * @return Batch<\App\Entity\Comment>
     */
    public function getBatch(
        int $trickId,
        int $batchNumber = 1,
        int $batchSize = 10,
        bool $includeDeleted = false,
    ): Batch {
        $countCriteria = [
            'trick' => $trickId,
        ];

        if (false === $includeDeleted) {
            $countCriteria['deletedAt'] = null;
        }

        $count = $this->commentRepository->count($countCriteria);

        $batchCriteria = array_merge($countCriteria, ['replyTo' => null]);

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $batchNumber = max((int) $batchNumber, 1);

        // Show last page in case $pageNumber is too high
        if ($count < ($batchNumber * $batchSize)) {
            $batchNumber = max(ceil($count / $batchSize), 1);
        }

        $offset = ($batchNumber - 1) * $batchSize;

        $comments = $this->commentRepository->findBy(
            criteria: $batchCriteria,
            orderBy: ['createdAt' => 'DESC'],
            offset: $offset,
            limit: $batchSize,
        );

        return new Batch(
            $comments,
            pageNumber: $batchNumber,
            firstIndex: $offset + 1,
            totalCount: $count
        );
    }

    /**
     * Soft deletes a comment.
     * 
     * Removes the author and content.  
     * Sets a deletedAt property.
     */
    public function remove(Comment $comment): void
    {
        $comment
            ->setText("")
            ->setDeletedAt(new \DateTimeImmutable());
    }
}
