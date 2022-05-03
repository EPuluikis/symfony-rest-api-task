<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\Sex;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Admin User');
        $user->setEmail('user@example.com');
        $user->setSex(Sex::MALE);
        $user->setRoles(Role::ROLE_ADMIN->value);
        $user->setPassword($this->hasher->hashPassword($user, 'secret'));

        $manager->persist($user);
        $manager->flush();
    }
}
