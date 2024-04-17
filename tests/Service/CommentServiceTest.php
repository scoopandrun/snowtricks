<?php

namespace Tests\App\Service;

use App\Component\Batch;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommentServiceTest extends TestCase
{
    private CommentRepository&MockObject $commentRepository;
    private CommentService $commentService;

    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->commentService = new CommentService($this->commentRepository);
    }

    public function testGetBatch()
    {
        $this->commentRepository->method('count')->willReturn(10);
        $this->commentRepository->method('findBy')->willReturn(array_fill(0, 10, new Comment()));

        $batch = $this->commentService->getBatch(1, 1, 10, false);

        $this->assertInstanceOf(Batch::class, $batch);
        $this->assertEquals(10, $batch->getSize());
        $this->assertEquals(1, $batch->getPageNumber());
    }

    public function testRemove()
    {
        $comment = new Comment();
        $comment->setText("Test comment");

        $this->commentService->remove($comment);

        $this->assertEquals("", $comment->getText());
        $this->assertInstanceOf(\DateTimeImmutable::class, $comment->getDeletedAt());
    }
}
