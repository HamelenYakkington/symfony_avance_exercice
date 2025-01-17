<?php

namespace App\DataFixtures;

use App\Entity\Task;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference('USER');

        $task1 = new Task();
        $task1->setName("task1");
        $task1->setDesciption("Lorem Ipsum");
        $task1->setCreateDt(new DateTime());
        $task1->setAuthor($user);

        $task2 = new Task();
        $task2->setName("task2");
        $task2->setDesciption("Lorem Ipsum");
        $task2->setCreateDt(new DateTime());
        $task2->setAuthor($user);

        $manager->persist($task1);
        $manager->persist($task2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class
        ];
    }
}
