<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Service\FileManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrickFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private LoggerInterface $logger,
        private FileManager $fileManager,
        #[Autowire('%app.uploads.pictures%/tricks')]
        private string $trickPictureUploadDirectory,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Categories
        foreach ($this->generateCategories() as $category) {
            $this->addReference($category->getName(), $category);
            $manager->persist($category);
        }

        // Delete existing trick pictures
        $this->fileManager->clearDirectory($this->trickPictureUploadDirectory);

        // Tricks
        foreach ($this->generateTricks() as $trick) {
            /** @var Trick $trick */

            $manager->persist($trick);
        }

        $manager->flush();
    }

    /**
     * @return \Generator<Category> 
     */
    private function generateCategories(): \Generator
    {
        $categoriesNames = array_unique(
            array_column($this->getTricksData(), 'category')
        );

        foreach ($categoriesNames as $categoryName) {
            yield (new Category)->setName($categoryName);
        }
    }

    /**
     * @return \Generator<Trick>
     */
    private function generateTricks(): \Generator
    {
        $tricksData = $this->getTricksData();

        foreach ($tricksData as $trickData) {
            $trick = (new Trick)
                ->setName($trickData["name"])
                ->setSlug(strtolower($this->slugger->slug($trickData["name"])))
                ->setCategory($this->getReference($trickData["category"]))
                ->setDescription($trickData["description"]);

            foreach ($trickData["pictures"] ?? [] as $url) {
                try {
                    $response = $this->httpClient->request('GET', $url);

                    if ($response->getStatusCode() >= 400) {
                        throw new \Exception();
                    }

                    $fileContent = $response->getContent();

                    $filename = $this->fileManager->saveRawFile(
                        $fileContent,
                        $this->trickPictureUploadDirectory,
                        parse_url($url, PHP_URL_PATH),
                        true
                    );

                    if (!$filename) {
                        throw new \Exception();
                    }

                    $this->logger->info("Picture saved: " . $url);

                    $picture = (new Picture())
                        ->setFilename($filename)
                        ->setDescription("Picture of " . $trick->getName())
                        ->setSaveFile(false); // Do not save the file on persist, as it is already saved above

                    $trick->addPicture($picture);
                } catch (\Exception) {
                    $this->logger->info("Picture not saved: " . $url);
                }
            }

            foreach ($trickData["videos"] ?? [] as $url) {
                $video = (new Video())->setUrl($url);
                $trick->addVideo($video);
            }

            $author = $this->getReference(
                UserFixture::getRandomUser()->getUserIdentifier()
            );

            $trick->setAuthor($author);

            foreach ($this->generateComments(rand(15, 25)) as $comment) {
                $trick->addComment($comment);
            }

            yield $trick;
        }
    }

    private function generateComments(int $number): \Generator
    {
        if ($number < 1) {
            throw new \LogicException("The number of comments must be greater than 0");
        }

        $commentCreatedAt = new \DateTimeImmutable();

        $index = 1;

        do {
            // Delay 1 second to have sequential comments
            $interval = new \DateInterval("PT1S");
            $commentCreatedAt = $commentCreatedAt->add($interval);

            $text = "Comment #{$index}" . PHP_EOL . "Lorem ipsum...";

            $comment = (new Comment())
                ->setCreatedAt($commentCreatedAt)
                ->setAuthor($this->getReference(
                    UserFixture::getRandomUser()->getUserIdentifier()
                ))
                ->setText($text);

            $index++;

            yield $comment;
        } while (--$number);
    }

    private function getTricksData(): array
    {
        return [
            [
                "name" => "Ollie",
                "description" => "A simple trick where the boarder pops the snowboard by digging into the tail and then up into the air.",
                "category" => "Air",
                "pictures" => [
                    "https://i.ytimg.com/vi/AnI7qGQs0Ic/maxresdefault.jpg",
                    "https://asomammoth.com/wp-content/uploads/2021/12/Snowboarder-Ollie-on-a-Snowboard.jpg",
                    "https://www.wikihow.com/images/thumb/e/ef/Ollie-on-a-Snowboard-Step-7.jpg/v4-460px-Ollie-on-a-Snowboard-Step-7.jpg",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=D5sEJRx6QUY",
                    "https://www.dailymotion.com/video/xggf2a",
                    "https://vimeo.com/11823266",
                ],
            ],
            [
                "name" => "Frontside Grab",
                "description" => "One of the most essential snowboard tricks in any pro snowboarder’s arsenal, the frontside grab entails grabbing the toe edge with the back hand.",
                "category" => "Grab",
                "pictures" => [
                    "https://twistedsifter.com/wp-content/uploads/2011/01/indy-grab.jpg?resize=150",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=dciGawPvgro",
                ],
            ],
            [
                "name" => "Japan Air",
                "description" => "A grab of the toe edge using the front hand. The hand goes in between the feet to perform the move and the lead knee is also drawn to the snowboard.",
                "category" => "Grab",
                "pictures" => [
                    "https://i.pinimg.com/originals/70/63/59/7063597c6740cfeba3513d6448d604be.jpg",
                    "https://media.istockphoto.com/photos/snowboarder-performing-japan-air-trick-in-flight-picture-id139896027?k=6&m=139896027&s=170667a&w=0&h=dUJyqgnykjLWTWMPpUpnRXVUOZQJhLSE0i_4sVhbsJg=",

                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=CzDjM7h_Fwo",
                ],
            ],
            [
                "name" => "Backflip",
                "description" => "The rider performs a backflip after gaining air over a jump.",
                "category" => "Flip",
                "pictures" => [
                    "https://snowbrains.com/wp-content/uploads/2016/03/snowboard-cliff-backflip.jpg",
                    "https://i.ytimg.com/vi/Xv8iZKH4kV8/maxresdefault.jpg",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=arzLq-47QFA",
                ],
            ],
            [
                "name" => "180 spin",
                "description" => "The rider spins the board 180 degrees in the air.",
                "category" => "Spin",
                "pictures" => [
                    "https://cdn.shopify.com/s/files/1/0230/2239/articles/How-To-Shifty-Frontside-180_1024x1024.jpg?v=1537038662",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=Hb-X84nrNig",
                ],
            ],
            [
                "name" => "360 spin",
                "description" => "The rider spins the board for a full rotation in the air.",
                "category" => "Spin",
                "pictures" => [
                    "https://www.imperiumsnow.com/upload/friedl-fs-360-0.jpg",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=H0Izq1fAM5w",
                ],
            ],
            [
                "name" => "50-50",
                "description" => "The fifty fifty is the most fundamental box or trail trick you can do on your snowboard. It will help make you comfortable on boxes or rails before you begin to do boardslides and other rail tricks. Before attempting a 50-50, be comfortable riding and carving in general. No other freestyle skills or tricks are really necessary to learn this. You will begin on a ride-on feature, where you don’t have to ollie, or jump, to get onto the feaure. Once you begin doing 50-50s onto urban-on features (features where you do have to ollie onto), you will need to know how to ollie off the lip as well.",
                "category" => "Slide",
                "pictures" => [
                    "https://i.ytimg.com/vi/kxZbQGjSg4w/maxresdefault.jpg",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=e-7NgSu9SXg",
                ],
            ],
            [
                "name" => "Backside 180 rewind",
                "description" => "The Rewind was the freshest move back in 2017, and it’s pretty much the hardest trick variation you can do now. Basically it’s when you almost complete a full rotation – 360, 540, 720, whatever – then at the last minute reverse spin direction and ‘rewind’ 180 degrees. In this case, Marcus is sending a huge, inverted backside three, but stalls, pokes and brings it back to 180. This is the easiest rotation done in the hardest possible way.",
                "category" => "Spin",
                "pictures" => [
                    "https://miro.medium.com/max/1400/1*hGzc0ZXd_6uN4WHVCiaeHA.jpeg",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=3UZwv8-U88I",
                ],
            ],
            [
                "name" => "Elbow carve",
                "description" => "The elbow carve is a direct descendent of the Euro carve, a stylised heel or toe side carve popularised by European hard booter Serge Vitelli back in the late 1980s. Today, style meisters like Tyler Chorlton are taking things to the next level of creativity and the variations seem to be endless. All you need to get your elbow carve going is a relatively smooth stretch of slope, edge control and a decent set of core muscles – what are you waiting for?",
                "category" => "Grab",
                "pictures" => [
                    "https://nomadsnowboard.com/wp-content/uploads/2020/02/elbow-euro-body-carve-desous-banderole-1024x576.jpg",
                ],
                "videos" => [
                    "https://www.youtube.com/watch?v=JwuCBG_5TZc",
                ],
            ],
            [
                "name" => "Frontside Boardslide Backside Grab",
                "description" => "This trick is something you’ve probably never thought of, but now you’ve seen it you’ll wish you could do it too. It’s probably not even that hard, if you do it on a long, widefunbox in the park. But Rene Rinnekangas prefers to do it on a legit rail, with one of the sketchiest in-runs ever.",
                "category" => "Grab",
                "pictures" => [],
                "videos" => [],
            ],
            [
                "name" => "540° spin rewind",
                "description" => "Spin the board twice and spin it backwards half a turn.",
                "category" => "Spin",
                "pictures" => [],
                "videos" => [],
            ],
            [
                "name" => "Japan 540° spin rewind (日本語)",
                "description" => "Japanese touch to the 540° rewind.",
                "category" => "Spin",
                "pictures" => [],
                "videos" => [],
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}
