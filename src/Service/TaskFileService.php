<?php

namespace App\Service;

use App\Entity\Task;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class TaskFileService {
    public const DELETE   = 'FILE_DELETE';
    public const UPDATE   = 'FILE_UPDATE';
    public const CREATE   = 'FILE_CREATE';
    public const LIST     = 'FILE_LIST';
    public const GET      = 'FILE_GET';
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;

    public function __construct(Filesystem $filesystem, ParameterBagInterface $parameterBag) {
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
    }

    public function FileServiceOnAttribute(mixed $option, string $attribute) : array|bool {
        if($option instanceof Task) {
            switch($attribute) {
                case SELF::DELETE:
                    return $this->deleteTask($option->getId());
                case SELF::CREATE:
                    return $this->createTask($option);
                case SELF::UPDATE:
                    return $this->updateTask($option);
                default:
                    return false;
            }
        }
        if (is_string($option) && $attribute == SELF::GET) {
            return $this->viewTaskFiles($option);
        }
        if ($attribute == SELF::LIST) {
            return $this->listTasksFiles();
        }
    } 

    private function createTask(Task $task) : bool {
        try {
            $pathFile = $this->createPathFile($task->getId());
            if ($this->filesystem->exists($pathFile)) {
                return false;
            }
    
            $content = $this->createFilesContent($task);
            $this->verifAndCreateFolerTasks();
    
            $this->filesystem->dumpFile($pathFile, $content);
            return true;
        } catch(IOException $e) {
            return false;
        }

    }

    private function updateTask(Task $task) : bool {
        try{
            $pathFile = $this->createPathFile($task->getId());
            $content = $this->createFilesContent($task);
            if (!$this->filesystem->exists($pathFile)) {
                $this->verifAndCreateFolerTasks();
            }

            $this->filesystem->dumpFile($pathFile, $content);
            return true;
        } catch(IOException $e) {
            return false;
        }
    }

    private function deleteTask(int $idTask) : bool {
        try{
            $pathFile = $this->createPathFile($idTask);

            if ($this->filesystem->exists($pathFile)) {
                $this->filesystem->remove($pathFile);
            }
            return true;
        } catch(IOException $e) {
            return false;
        }
    }

    public function listTasksFiles() : array {
        $taskFolder = $this->getPathTasksFolder();
        $listFiles = scandir($taskFolder);
        $filesInformation = [];
        foreach ($listFiles as $nameFiles) {
            if ($nameFiles === '.' || $nameFiles === '..' || is_dir($taskFolder . $nameFiles)) {
                continue;
            }
            $content = $this->filesystem->readFile($taskFolder . $nameFiles);
            $lines = preg_split('/\r\n|\n|\r/', $content);
            $lines = array_map('trim', $lines);

            $filesContent = [
                "id" => $lines[0]
            ];
            $file = [$nameFiles => $filesContent];
            $filesInformation[] = $file;
        }
        return $filesInformation;
    }

    public function viewTaskFiles(string $nameFiles) {
        $taskFolder = $this->getPathTasksFolder();
        $pathFile = $taskFolder . $nameFiles;

        $content = $this->filesystem->readFile($pathFile);
        $lines = preg_split('/\r\n|\n|\r/', $content);
        $lines = array_map('trim', $lines);

        $filesContent = [
            "id" => $lines[0]
            , "name" => $lines[1]
            , "desc" => $lines[2]
        ];
        return [$nameFiles => $filesContent];
    }

    private function generateUniqid(int $idTask) : string {
        return "task_".$idTask;
    }

    private function createPathFile(int $idTask) : string {
        $taskFolderPath = $this->getPathTasksFolder();
        $fileName = $this->generateUniqid($idTask);
        return $taskFolderPath . $fileName . ".txt";
    }

    private function createFilesContent(Task $task):string {
        $content = "Task ID: " . $task->getId() . "\n";
        $content .= "Title: " . $task->getName() . "\n";
        $content .= "Description: " . $task->getDesciption() . "\n";

        return $content;
    }

    private function verifAndCreateFolerTasks() {
        if (!is_dir('tasks')) {
            $this->filesystem->mkdir('tasks', 0777, true);
        }
    }

    private function getPathTasksFolder() : string {
        return $this->parameterBag->get('kernel.project_dir') . '/public/tasks/';
    }
}
