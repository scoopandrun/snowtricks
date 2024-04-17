<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Service\FileManager;
use App\Service\ImageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserFixture extends Fixture
{
    private static ?array $users = null;
    private static array $usersData = [];

    public function __construct(
        #[Autowire('%app.uploads.pictures%/users')]
        private string $profilePictureUploadDirectory,
        private LoggerInterface $logger,
        private FileManager $fileManager,
        private ImageManager $imageManager,
    ) {
        $this->makeUsers();
    }

    public function load(ObjectManager $manager): void
    {
        foreach (static::$users as $user) {
            /** @var User $user */
            $this->addReference($user->getUserIdentifier(), $user);
            $manager->persist($user);
        }

        $manager->flush();

        // Delete existing user pictures
        $this->deleteUserPictures();

        $this->saveUserPictures();
    }

    /**
     * This method fills the `static::$usersData` and `static::$users` arrays.
     */
    private function makeUsers(): void
    {
        if (static::$users) {
            return;
        }

        try {
            $numberOfUsers = 10;
            $nationalities = join(',', ['fr', 'us', 'gb', 'de']);
            $fields = join(',', ['name', 'email', 'picture']);

            $randomuserAPI = "https://randomuser.me/api/?results={$numberOfUsers}&nat={$nationalities}&inc={$fields}&noinfo";

            $response = file_get_contents($randomuserAPI);

            $results = json_decode($response)->results;

            foreach ($results as $result) {
                array_push(static::$usersData, [
                    "username" => $result->name->first . ' ' . $result->name->last,
                    "email" => $result->email,
                    "picture" => $result->picture->large,
                ]);
            }
        } catch (\Exception $e) {
            // Manual backup in case the API call fails
            static::$usersData = [
                [
                    "username" => "Jimmy Sweat",
                    "email" => "jimmy@snowtricks.localhost",
                ],
                [
                    "username" => "John Doe",
                    "email" => "john.doe@example.com",
                ],
                [
                    "username" => "Jane Doe",
                    "email" => "jane.doe@example.com",
                ],
                [
                    "username" => "Mary Smith",
                    "email" => "mary.smith@example.com",
                ],
                [
                    "username" => "Roberta Perez",
                    "email" => "roberta.perez@example.com",
                ],
            ];
        }

        static::$users = array_map(function ($userData) {
            return (new User())
                ->setUsername($userData["username"])
                ->setEmail($userData["email"])
                ->setPassword(password_hash(bin2hex(random_bytes(20)), PASSWORD_DEFAULT))
                ->setIsVerified(true);
        }, static::$usersData);
    }

    private function deleteUserPictures(): void
    {
        $directory = $this->profilePictureUploadDirectory;

        $this->fileManager->clearDirectory($directory);

        $this->logger->debug("User pictures deleted.");
    }

    private function saveUserPictures(): void
    {
        // Get the pictures from the URLs and save them
        foreach (static::$users as $user) {
            /** @var User $user */

            $userData = array_values(array_filter(static::$usersData, function ($data) use ($user) {
                return $data['email'] === $user->getEmail();
            }))[0] ?? null;

            if (isset($userData['picture'])) {
                $filename = $this->imageManager->saveImage(
                    file_get_contents($userData['picture']),
                    $this->profilePictureUploadDirectory,
                    $user->getId() . '.' . pathinfo($userData['picture'], PATHINFO_EXTENSION),
                    [
                        ImageManager::SIZE_THUMBNAIL,
                        ImageManager::SIZE_SMALL,
                    ]
                );

                if ($filename) {
                    $this->logger->info("Picture saved" . $filename);
                } else {
                    $this->logger->error("Picture NOT saved: " . $filename);
                }
            }
        }
    }

    public static function getRandomUser(): User
    {
        $users = static::$users;

        return $users[array_rand($users)];
    }
}
