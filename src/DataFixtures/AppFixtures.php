<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordEncoder)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User;
        $plainPasswor = "admin1234";

        $user->setUsername("admin");
        $user->setPassword($this->passwordEncoder->hashPassword($user,  $plainPasswor));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEmail("admin@admin");
        $user->setTele("0677889809");

        $manager->persist($user);

        $manager->flush();
    }
}
