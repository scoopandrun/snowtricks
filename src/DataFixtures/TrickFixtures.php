<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Trick;
use App\Entity\Video;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $tricks = $this->makeCategories();

        foreach ($tricks as $trick) {
            $manager->persist($trick);
        }

        $manager->flush();
    }

    /**
     * @return Trick[] 
     */
    private function makeCategories(): array
    {
        $authors = [
            "admin@snowtricks.localhost",
        ];

        $tricksData = [
            [
                "name" => "Ollie",
                "description" => "A simple trick where the boarder pops the snowboard by digging into the tail and then up into the air.",
                "category" => "Air",
                "videos" => [
                    "https://www.youtube.com/watch?v=D5sEJRx6QUY",
                    "https://www.dailymotion.com/video/xggf2a",
                    "https://vimeo.com/11823266",
                ],
                "author" => 0,
            ],
            [
                "name" => "Frontside Grab",
                "description" => "One of the most essential snowboard tricks in any pro snowboarder’s arsenal, the frontside grab entails grabbing the toe edge with the back hand.",
                "category" => "Grab",
                "author" => 0,
            ],
            [
                "name" => "Japan Air",
                "description" => "A grab of the toe edge using the front hand. The hand goes in between the feet to perform the move and the lead knee is also drawn to the snowboard.",
                "category" => "Grab",
                "author" => 0,
            ],
            [
                "name" => "Backflip",
                "description" => "The rider performs a backflip after gaining air over a jump.",
                "category" => "Flip",
                "author" => 0,
            ],
            [
                "name" => "180 spin",
                "description" => "The rider spins the board 180 degrees in the air.",
                "category" => "Spin",
                "author" => 0,
            ],
            [
                "name" => "360 spin",
                "description" => "The rider spins the board for a full rotation in the air.",
                "category" => "Spin",
                "author" => 0,
            ],
            [
                "name" => "50-50",
                "description" => "The fifty fifty is the most fundamental box or trail trick you can do on your snowboard. It will help make you comfortable on boxes or rails before you begin to do boardslides and other rail tricks. Before attempting a 50-50, be comfortable riding and carving in general. No other freestyle skills or tricks are really necessary to learn this. You will begin on a ride-on feature, where you don’t have to ollie, or jump, to get onto the feaure. Once you begin doing 50-50s onto urban-on features (features where you do have to ollie onto), you will need to know how to ollie off the lip as well.",
                "category" => "Slide",
                "author" => 0,
            ],
            [
                "name" => "Backside 180 rewind",
                "description" => "The Rewind was the freshest move back in 2017, and it’s pretty much the hardest trick variation you can do now. Basically it’s when you almost complete a full rotation – 360, 540, 720, whatever – then at the last minute reverse spin direction and ‘rewind’ 180 degrees. In this case, Marcus is sending a huge, inverted backside three, but stalls, pokes and brings it back to 180. This is the easiest rotation done in the hardest possible way.",
                "category" => "Spin",
                "author" => 0,
            ],
            [
                "name" => "Elbow carve",
                "description" => "The elbow carve is a direct descendent of the Euro carve, a stylised heel or toe side carve popularised by European hard booter Serge Vitelli back in the late 1980s. Today, style meisters like Tyler Chorlton are taking things to the next level of creativity and the variations seem to be endless. All you need to get your elbow carve going is a relatively smooth stretch of slope, edge control and a decent set of core muscles – what are you waiting for?",
                "category" => "Grab",
                "author" => 0,
            ],
            [
                "name" => "Frontside Boardslide Backside Grab",
                "description" => "This trick is something you’ve probably never thought of, but now you’ve seen it you’ll wish you could do it too. It’s probably not even that hard, if you do it on a long, widefunbox in the park. But Rene Rinnekangas prefers to do it on a legit rail, with one of the sketchiest in-runs ever.",
                "category" => "Grab",
                "author" => 0,
            ],
            [
                "name" => "540° spin rewind",
                "description" => "Spin the board twice and spin it backwards half a turn.",
                "category" => "Spin",
                "author" => 0,
            ],
            [
                "name" => "Japan 540° spin rewind (日本語)",
                "description" => "Japanese touch to the 540° rewind.",
                "category" => "Spin",
                "author" => 0,
            ],
        ];

        $tricks = array_map(function ($trickData) use ($authors) {
            $trick = (new Trick)
                ->setName($trickData["name"])
                ->setSlug(strtolower($this->slugger->slug($trickData["name"])))
                ->setCategory($this->getReference($trickData["category"]))
                ->setDescription($trickData["description"]);

            $videos = $trickData["videos"] ?? [];

            foreach ($videos as $url) {
                $video = (new Video())->setUrl($url);
                $trick->addVideo($video);
            }

            $author = $this->getReference($authors[$trickData["author"]]);

            $trick->setAuthor($author);

            return $trick;
        }, $tricksData);

        return $tricks;
    }

    public function getDependencies(): array
    {
        return [CategoryFixtures::class, UserFixtures::class];
    }
}
