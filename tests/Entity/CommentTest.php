<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testInstanciation(): Comment
    {
        $comment = new Comment();
        $this->assertInstanceOf(Comment::class, $comment);

        return $comment;
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetAuthor(Comment $comment): void
    {
        $author = new User();
        $comment->setAuthor($author);
        $this->assertSame($author, $comment->getAuthor());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetText(Comment $comment): void
    {
        $text = "Lorem ispum...";
        $comment->setText($text);
        $this->assertSame($text, $comment->getText());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetTrick(Comment $comment): void
    {
        $trick = new Trick();
        $comment->setTrick($trick);
        $this->assertSame($trick, $comment->getTrick());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetCreatedAt(Comment $comment): void
    {
        $datetime = new \DateTimeImmutable();
        $comment->setCreatedAt($datetime);
        $this->assertSame($datetime, $comment->getCreatedAt());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetUpdatedAt(Comment $comment): void
    {
        $datetime = new \DateTimeImmutable();
        $comment->setUpdatedAt($datetime);
        $this->assertSame($datetime, $comment->getUpdatedAt());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetDeletedAt(Comment $comment): void
    {
        $datetime = new \DateTimeImmutable();
        $comment->setDeletedAt($datetime);
        $this->assertSame($datetime, $comment->getDeletedAt());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetReplyTo(Comment $comment): void
    {
        $parentComment = new Comment();
        $comment->setReplyTo($parentComment);
        $this->assertSame($parentComment, $comment->getReplyTo());
    }

    /**
     * @depends testInstanciation
     */
    public function testToString(Comment $comment): void
    {
        $text = "Lorem ipsum";
        $comment->setText($text);
        $this->assertSame($text, (string) $comment);
    }

    /**
     * Collections
     */

    /**
     * @depends testInstanciation
     */
    public function testGetReplies(Comment $comment): Collection
    {
        $replies = $comment->getReplies();
        $this->assertInstanceOf(Collection::class, $replies);

        return $replies;
    }

    /**
     * @depends testInstanciation
     * @depends testGetReplies
     */
    public function testAddReply(Comment $comment, Collection $replies): Comment
    {
        $initialCount = $replies->count();
        $reply = new Comment();
        $comment->addReply($reply);
        $this->assertCount($initialCount + 1, $replies);
        $this->assertSame($comment, $reply->getReplyTo());

        return $reply;
    }

    /**
     * @depends testInstanciation
     * @depends testAddReply
     */
    public function testRemoveReply(Comment $comment, Comment $reply): void
    {
        $replies = $comment->getReplies();
        $initialCount = $replies->count();
        $comment->removeReply($reply);
        $this->assertCount($initialCount - 1, $replies);
        $this->assertNull($reply->getReplyTo());
    }
}
