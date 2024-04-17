<?php

namespace App\Tests\Entity;

use App\Entity\Picture;
use App\Entity\Trick;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class PictureTest extends TestCase
{
    public function testInstanciation(): Picture
    {
        $picture = new Picture();
        $this->assertInstanceOf(Picture::class, $picture);

        return $picture;
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetTrick(Picture $picture): void
    {
        $trick = new Trick();
        $picture->setTrick($trick);
        $this->assertSame($trick, $picture->getTrick());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetFile(Picture $picture): void
    {
        /** @var File $file */
        $file = $this
            ->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $picture->setFile($file);
        $this->assertSame($file, $picture->getFile());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetFilename(Picture $picture): void
    {
        $filename = "picture.jpg";
        $picture->setFilename($filename);
        $this->assertSame($filename, $picture->getFilename());
        $this->assertSame($filename, (string) $picture);
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetDescription(Picture $picture): void
    {
        $description = "Lorem ipsum";
        $picture->setDescription($description);
        $this->assertSame($description, $picture->getDescription());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetSaveFile(Picture $picture): void
    {
        $picture->setSaveFile(true);
        $this->assertTrue($picture->getSaveFile());
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetSetAsMainPicture(Picture $picture): void
    {
        $picture->setSetAsMainPicture(true);
        $this->assertTrue($picture->getSetAsMainPicture());
    }
}
