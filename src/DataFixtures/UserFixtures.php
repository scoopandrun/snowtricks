<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private static ?array $users = null;

    public function __construct()
    {
        static::makeUsers();
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User[] */
        $users = static::$users;

        foreach ($users as $user) {
            $this->addReference($user->getUserIdentifier(), $user);
            $manager->persist($user);
        }

        $manager->flush();
    }

    private static function makeUsers(): void
    {
        if (static::$users) {
            return;
        }

        $usersData = [
            [
                "username" => "Nicolas DENIS",
                "email" => "admin@snowtricks.localhost",
                "password" => "9xrK5xEXp7lB0lYpEQPY",
                "verified" => true,
            ],
            [
                "username" => "John DOE",
                "email" => "johndoe@example.com",
                "password" => "ViKz12AuLlCz3lgOGuNC",
                "verified" => true,
            ],
            [
                "username" => "Jane DOE",
                "email" => "janedoe@example.com",
                "password" => "zVIrmcy54ymz4qxbigWN",
                "verified" => true,
            ],
            [
                "username" => "Mary SMITH",
                "email" => "marysmith@example.com",
                "password" => "1U6UgH1Sbs9rWmPbaq4G",
                "verified" => true,
            ],
            [
                "username" => "Roberta PEREZ",
                "email" => "robertaperez@example.com",
                "password" => "8NnCD4V9OemiiFJGa9k9",
                "verified" => false,
            ],
        ];

        $users = array_map(function ($userData) {
            $user = new User();

            $user->setUsername($userData["username"])
                ->setEmail($userData["email"])
                ->setPassword(password_hash($userData["password"], PASSWORD_DEFAULT))
                ->setIsVerified($userData["verified"]);

            return $user;
        }, $usersData);

        static::$users = $users;
    }

    /**
     * @return User[]
     */
    public static function getVerifiedUsers(): array
    {
        static::makeUsers();

        $verifiedUsers = array_values(array_filter(static::$users, fn (User $user) => $user->isVerified()));

        return $verifiedUsers;
    }

    public static function getRandomUser(bool $verified = true): User
    {
        static::makeUsers();

        $users = $verified ? static::getVerifiedUsers() : static::$users;

        return $users[array_rand($users)];
    }
}
