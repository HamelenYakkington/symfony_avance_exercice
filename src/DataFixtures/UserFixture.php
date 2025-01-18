<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
private $passwordHasher;

public function __construct(UserPasswordHasherInterface $passwordHasher)
{
    $this->passwordHasher = $passwordHasher;
}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail("admin@admin.admin");
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            "admin"
        );

        $admin->setPassword($hashedPassword);
        $admin->setRoles(["ROLE_USER", "ROLE_ADMIN"]);


        $user = new User();
        $user->setEmail("arthur@maloron.fr");
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            "arthur"
        );

        $user->setPassword($hashedPassword);
        $user->setRoles(["ROLE_USER"]);

        $this->addReference('ADMIN', $admin);
        $this->addReference('USER', $user);


        $manager->persist($admin);
        $manager->persist(object: $user);

        $manager->flush();
    }
}
