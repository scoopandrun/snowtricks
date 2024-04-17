<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Trick;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testInstanciation(): Category
    {
        $category = new Category();
        $this->assertInstanceOf(Category::class, $category);

        return $category;
    }

    /**
     * @depends testInstanciation
     */
    public function testSetAndGetName(Category $category): void
    {
        $name = "My test category";
        $category->setName($name);
        $this->assertSame($name, $category->getName());
    }

    /**
     * @depends testInstanciation
     */
    public function testToString(Category $category): void
    {
        $name = "My test category";
        $category->setName($name);
        $this->assertSame($name, (string) $category);
    }

    /**
     * Collections
     */

    /**
     * @depends testInstanciation
     */
    public function testGetTricks(Category $category): Collection
    {
        $tricks = $category->getTricks();
        $this->assertInstanceOf(Collection::class, $tricks);

        return $tricks;
    }

    /**
     * @depends testInstanciation
     * @depends testGetTricks
     */
    public function testAddTrick(Category $category, Collection $tricks): Trick
    {
        $initialCount = $tricks->count();
        $trick = new Trick();
        $category->addTrick($trick);
        $this->assertCount($initialCount + 1, $tricks);
        $this->assertSame($category, $trick->getCategory());

        return $trick;
    }

    /**
     * @depends testInstanciation
     * @depends testAddTrick
     */
    public function testRemoveTrick(Category $category, Trick $trick): void
    {
        $tricks = $category->getTricks();
        $initialCount = $tricks->count();
        $category->removeTrick($trick);
        $this->assertCount($initialCount - 1, $tricks);
        $this->assertNull($trick->getCategory());
    }
}
