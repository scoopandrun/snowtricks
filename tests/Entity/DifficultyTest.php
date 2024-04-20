<?php

namespace App\Tests\Entity;

use App\Entity\Difficulty;
use App\Entity\Trick;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class DifficultyTest extends TestCase
{
    public function testGetId(): void
    {
        $difficulty = new Difficulty();
        $this->assertNull($difficulty->getId());
    }

    public function testGetValue(): void
    {
        $difficulty = new Difficulty();
        $this->assertNull($difficulty->getValue());
    }

    public function testSetValue(): void
    {
        $difficulty = new Difficulty();
        $value = 5;
        $difficulty->setValue($value);
        $this->assertEquals($value, $difficulty->getValue());
    }

    public function testGetName(): void
    {
        $difficulty = new Difficulty();
        $this->assertNull($difficulty->getName());
    }

    public function testSetName(): void
    {
        $difficulty = new Difficulty();
        $name = 'Easy';
        $difficulty->setName($name);
        $this->assertEquals($name, $difficulty->getName());
    }

    public function testGetTricks(): void
    {
        $difficulty = new Difficulty();
        $this->assertInstanceOf(ArrayCollection::class, $difficulty->getTricks());
    }

    public function testAddTrick(): void
    {
        $difficulty = new Difficulty();
        $trick = new Trick();

        $difficulty->addTrick($trick);

        $this->assertTrue($difficulty->getTricks()->contains($trick));
        $this->assertEquals($difficulty, $trick->getDifficulty());
    }

    public function testRemoveTrick(): void
    {
        $difficulty = new Difficulty();
        $trick = new Trick();
        $difficulty->addTrick($trick);

        $difficulty->removeTrick($trick);

        $this->assertFalse($difficulty->getTricks()->contains($trick));
        $this->assertNull($trick->getDifficulty());
    }

    public function testToString(): void
    {
        $difficulty = new Difficulty();
        $name = 'Easy';
        $difficulty->setName($name);
        $this->assertEquals($name, (string) $difficulty);
    }
}
