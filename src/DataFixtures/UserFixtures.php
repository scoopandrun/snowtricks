<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->makeUsers();

        foreach ($users as $user) {
            $this->addReference($user->getEmail(), $user);
            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @return User[] 
     */
    private function makeUsers(): array
    {
        $usersData = [
            [
                "username" => "Nicolas DENIS",
                "email" => "admin@snowtricks.localhost",
                "password" => "9xrK5xEXp7lB0lYpEQPY",
                "roles" => [],
            ],
            [
                "username" => "John DOE",
                "email" => "johndoe@example.com",
                "password" => "ViKz12AuLlCz3lgOGuNC",
                "roles" => [],
            ],
        ];

        $users = array_map(function ($userData) {
            $user = new User();

            $user->setUsername($userData["username"])
                ->setEmail($userData["email"])
                ->setPassword($this->hasher->hashPassword($user, $userData["password"]))
                ->setRoles($userData["roles"]);

            return $user;
        }, $usersData);

        return $users;
    }
}
