<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail("admin@admin.admin");
        $admin->setPassword("admin");
        $admin->setRoles(["ROLE_USER", "ROLE_ADMIN"]);


        $user = new User();
        $user->setEmail("arthur@maloron.fr");
        $user->setPassword("arthur");
        $user->setRoles(["ROLE_USER"]);

        $this->addReference('ADMIN', $admin);
        $this->addReference('USER', $user);


        $manager->persist($admin);
        $manager->persist(object: $user);

        $manager->flush();
    }
}
