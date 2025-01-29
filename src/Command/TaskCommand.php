<?php

namespace App\Command;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\TaskFileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task',
    description: 'Gestion des tâches via la console.',
    hidden: false,
)]
class TaskCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TaskFileService  $taskFileService;
    private UserRepository $userRepository;
    private TaskRepository $taskRepository;

    public function __construct(TaskFileService  $taskFileService, TaskRepository $taskRepository,UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->taskFileService = $taskFileService;
        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Gestion des tâches via la console')
            ->addArgument('action', InputArgument::OPTIONAL, 'Action à effectuer (create, update, list, get, delete)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $io->choice(
            'Choisissez une action : ',
            ['CREATE', 'UPDATE', 'DELETE', 'LIST', 'GET']
        );

        switch ($action) {
            case 'CREATE':
                $userEmails = $this->userRepository->findAllEmail();

                $mixedOption = new Task;
                $mixedOption->setName($io->ask('Choisissez un nom pour la task'));
                $mixedOption->setDesciption($io->ask('Ecrivez une description pour la task'));
                $userMailSelected = $io->choice(
                    'Choisissez un utilisateur : ',
                    $userEmails
                );
                $authors = $this->userRepository->findOneByEmail($userMailSelected);
                if(!$authors) {
                    exit;
                }
                $mixedOption->setAuthor($authors);
                $mixedOption->updateTimestamps();

                $this->entityManager->persist($mixedOption);
                $this->entityManager->flush();

                $rez = $this->taskFileService->FileServiceOnAttribute($mixedOption, 'FILE_CREATE');
                break;
            case 'UPDATE':
                $taskNames = $this->taskRepository->findAllName();
                $taskName = $io->choice(
                    'Choisissez une task : ',
                    $taskNames
                );
                $mixedOption = $this->taskRepository->findOneByName($taskName);
                $mixedOption->updateTimestamps();
                $mixedOption->setName($io->ask('Entrez un nouveau nom ? ', $mixedOption->getName()));
                $mixedOption->setDesciption($io->ask('Entre une nouvelle une desciprition ?', $mixedOption->getDesciption()));

                $this->entityManager->persist($mixedOption);
                $this->entityManager->flush();

                $rez = $this->taskFileService->FileServiceOnAttribute($mixedOption, 'FILE_UPDATE');
                break;
            case 'DELETE':
                $taskNames = $this->taskRepository->findAllName();
                $taskName = $io->choice(
                    'Choisissez une task : ',
                    $taskNames
                );
                $mixedOption = $this->taskRepository->findOneByName($taskName);
                $rez = $this->taskFileService->FileServiceOnAttribute($mixedOption, 'FILE_DELETE');
                $this->entityManager->remove($mixedOption);
                $this->entityManager->flush();

                break;
            case 'LIST':
                $files = $this->taskFileService->FileServiceOnAttribute(null, 'FILE_LIST');
                $nameFiles = [];
                foreach ($files as $key => $value) {
                    foreach ($value as $key => $val) {
                        $nameFiles[] = $key;
                    }
                }
                $io->text($nameFiles);
                break;
            case 'GET':
                $files = $this->taskFileService->FileServiceOnAttribute(null, 'FILE_LIST');
                $nameFiles = [];
                foreach ($files as $key => $value) {
                    foreach ($value as $key => $val) {
                        $nameFiles[] = $key;
                    }
                }

                $nameFile = $io->choice("Choisissez une fichier task : ", $nameFiles);

                $file = $this->taskFileService->FileServiceOnAttribute($nameFile, 'FILE_GET');
                $outputs = [];
                foreach ($file as $key => $value) {
                    $outputs[] = $key . " :";
                    foreach ($value as $key => $val) {
                        $outputs[] = $val;
                    }
                    $io->text($outputs);
                }
                break;
            default:
                return Command::FAILURE;
                break;
        }

        return Command::SUCCESS;
    }
}
