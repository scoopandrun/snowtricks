<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class TrickTest extends TestCase
{
    public function testInstanciation(): Trick
    {
        $trick = new Trick();
        $this->assertInstanceOf(Trick::class, $trick);

        return $trick;
    }

    /**
     * Getters and setters
     */

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetName(Trick $trick): void
    {
        $name = "My test trick";
        $trick->setName($name);
        $this->assertSame($name, $trick->getName());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetSlug(Trick $trick): void
    {
        $slug = "my-test-trick";
        $trick->setSlug($slug);
        $this->assertSame($slug, $trick->getSlug());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetDescription(Trick $trick): void
    {
        $description = "My trick description";
        $trick->setDescription($description);
        $this->assertSame($description, $trick->getDescription());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetCategory(Trick $trick): void
    {
        $category = new Category();
        $trick->setCategory($category);
        $this->assertSame($category, $trick->getCategory());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetAuthor(Trick $trick): void
    {
        $author = new User();
        $trick->setAuthor($author);
        $this->assertSame($author, $trick->getAuthor());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetMainPicture(Trick $trick): void
    {
        $picture = new Picture();
        $trick->setMainPicture($picture);
        $this->assertSame($picture, $trick->getMainPicture());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetCreatedAt(Trick $trick): void
    {
        $datetime = new \DateTimeImmutable();
        $trick->setCreatedAt($datetime);
        $this->assertSame($datetime, $trick->getCreatedAt());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetUpdatedAt(Trick $trick): void
    {
        $datetime = new \DateTimeImmutable();
        $trick->setUpdatedAt($datetime);
        $this->assertSame($datetime, $trick->getUpdatedAt());
    }

    /**
     * @depends testInstanciation
     */
    public function testToString(Trick $trick): void
    {
        $name = "My test trick";
        $trick->setName($name);
        $this->assertSame($name, (string) $trick);
    }

    /**
     * Collections
     */

    /**
     * Pictures
     */

    /**
     * @depends testInstanciation
     */
    public function testGetPictures(Trick $trick): Collection
    {
        $pictures = $trick->getPictures();
        $this->assertInstanceOf(Collection::class, $pictures);

        return $pictures;
    }

    /**
     * @depends testGetPictures
     */
    public function testEmptyPicturesCollection(Collection $pictures): void
    {
        $this->assertCount(0, $pictures);
    }

    /**
     * @depends testInstanciation
     * @depends testGetPictures
     */
    public function testAddPicture(Trick $trick, Collection $pictures): Picture
    {
        $initialCount = $pictures->count();
        $picture = new Picture();
        $trick->addPicture($picture);
        $this->assertCount($initialCount + 1, $pictures);

        return $picture;
    }

    /**
     * @depends testInstanciation
     * @depends testAddPicture
     */
    public function testRemovePicture(Trick $trick, Picture $picture): void
    {
        $pictures = $trick->getPictures();
        $initialCount = $pictures->count();
        $trick->removePicture($picture);
        $this->assertCount($initialCount - 1, $pictures);
    }

    /**
     * @depends testInstanciation
     * @depends testAddPicture
     */
    public function testMainPictureIsSetWhenAddingFirstPicture(Trick $trick): Trick
    {
        // Make sure the collection is empty before the test
        $trick->getPictures()->clear();

        // Make sure the mainPicture is null before the test
        $trick->setMainPicture(null);

        $picture = new Picture();

        $trick->addPicture($picture);
        $this->assertSame($picture, $trick->getMainPicture());

        return $trick;
    }

    /**
     * @depends testMainPictureIsSetWhenAddingFirstPicture
     */
    public function testMainPictureIsUnchangedWhenAddingSecondPicture(Trick $trick): Trick
    {
        // Make sure we only have 1 picture in the colleciton
        $this->assertNotEmpty($trick->getPictures());

        $mainPicture = $trick->getMainPicture();

        $additionalPicture = new Picture();

        $trick->addPicture($additionalPicture);

        $this->assertSame($mainPicture, $trick->getMainPicture());

        return $trick;
    }

    /**
     * @depends testMainPictureIsUnchangedWhenAddingSecondPicture
     */
    public function testMainPictureIsUpdatedWhenRemovingFirstInCollection(Trick $trick): void
    {
        // Make sure we have at least 2 pictures in the collection
        $this->assertGreaterThanOrEqual(2, $trick->getPictures()->count());

        $firstPictureInCollection = $trick->getPictures()->first();
        $secondPictureInCollection = $trick->getPictures()->get(1);

        $trick->removePicture($firstPictureInCollection);

        $this->assertSame($secondPictureInCollection, $trick->getMainPicture());
    }

    /**
     * @depends testMainPictureIsSetWhenAddingFirstPicture
     */
    public function testMainPictureIsNullWhenRemovingAllPictures(Trick $trick): void
    {
        foreach ($trick->getPictures() as $picture) {
            $trick->removePicture($picture);
        }

        $this->assertNull($trick->getMainPicture());
    }

    /**
     * Videos
     */

    /**
     * @depends testInstanciation
     */
    public function testGetVideos(Trick $trick): Collection
    {
        $videos = $trick->getVideos();
        $this->assertInstanceOf(Collection::class, $videos);

        return $videos;
    }

    /**
     * @depends testInstanciation
     * @depends testGetVideos
     */
    public function testAddVideo(Trick $trick, Collection $videos): Video
    {
        $initialCount = $videos->count();
        $video = new Video();
        $trick->addVideo($video);
        $this->assertCount($initialCount + 1, $videos);

        return $video;
    }

    /**
     * @depends testInstanciation
     * @depends testAddVideo
     */
    public function testRemoveVideo(Trick $trick, Video $video): void
    {
        $videos = $trick->getVideos();
        $initialCount = $videos->count();
        $trick->removeVideo($video);
        $this->assertCount($initialCount - 1, $videos);
    }

    /**
     * Comments
     */

    /**
     * @depends testInstanciation
     */
    public function testGetComments(Trick $trick): Collection
    {
        $videos = $trick->getComments();
        $this->assertInstanceOf(Collection::class, $videos);

        return $videos;
    }

    /**
     * @depends testInstanciation
     * @depends testGetComments
     */
    public function testAddComment(Trick $trick, Collection $comments): Comment
    {
        $initialCount = $comments->count();
        $comment = new Comment();
        $trick->addComment($comment);
        $this->assertCount($initialCount + 1, $comments);

        return $comment;
    }

    /**
     * @depends testInstanciation
     * @depends testAddComment
     */
    public function testRemoveComment(Trick $trick, Comment $comment): void
    {
        $comments = $trick->getComments();
        $initialCount = $comments->count();
        $trick->removeComment($comment);
        $this->assertCount($initialCount - 1, $comments);
    }
}
