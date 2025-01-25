<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskFileService;
use App\Service\TaskService;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class TaskFixture extends Fixture implements DependentFixtureInterface
{

    private Filesystem $filesystem;
    private TaskFileService $taskFileService;
    
    public function __construct(Filesystem $filesystem, TaskFileService $taskFileService) {
        $this->filesystem = $filesystem;
        $this->taskFileService = $taskFileService;
    }
    public function load(ObjectManager $manager): void
    {
        try {
            $this->filesystem->remove(['public/tasks']);
        } catch (IOException $e) {
            throw $e;
        }
        


        $user = $this->getReference('USER',User::class);

        $task1 = new Task();
        $task1->setName("task1");
        $task1->setDesciption("Lorem Ipsum");
        $task1->updateTimestamps();
        $task1->setAuthor($user);

        $task2 = new Task();
        $task2->setName("task2");
        $task2->setDesciption("Lorem Ipsum");
        $task2->updateTimestamps();
        $task2->setAuthor($user);

        $dateTask3 = new DateTime();
        $dateTask3->modify('-10 days');

        $task3 = new Task();
        $task3->setName("task3");
        $task3->setDesciption("Lorem Ipsum");
        $task3->setcreateDt($dateTask3);
        $task3->setAuthor($user);

        $manager->persist($task1);
        $manager->persist($task2);
        $manager->persist($task3);


        $manager->flush();
        $this->taskFileService->FileServiceOnAttribute($task1,'FILE_CREATE');
        $this->taskFileService->FileServiceOnAttribute($task2,'FILE_CREATE');
        $this->taskFileService->FileServiceOnAttribute($task3,'FILE_CREATE');
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class
        ];
    }
}
