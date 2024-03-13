<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = $this->makeCategories();

        foreach ($categories as $category) {
            $this->addReference($category->getName(), $category);
            $manager->persist($category);
        }

        $manager->flush();
    }

    /**
     * @return Category[] 
     */
    private function makeCategories(): array
    {
        $categoriesData = [
            ["name" => "Air"],
            ["name" => "Grab"],
            ["name" => "Flip"],
            ["name" => "Spin"],
            ["name" => "Slide"],
        ];

        $categories = array_map(function ($category) {
            return (new Category)
                ->setName($category["name"]);
        }, $categoriesData);

        return $categories;
    }
}
