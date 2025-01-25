<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
use App\Service\TaskFileService;
use App\Service\TaskService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
final class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TaskService $taskService;
    private TaskFileService $taskFileService;

    public function __construct(EntityManagerInterface $entityManager, TaskService $taskService, TaskFileService $taskFileService) {
        $this->entityManager = $entityManager;
        $this->taskService = $taskService;
        $this->taskFileService = $taskFileService;
    }


    #[Route('/', name: 'task_index')]
    public function index(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        
        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
            'tasks' => $tasks
        ]);
    }

    #[Route('/view/{slug}', name: 'task_view')]
    public function view(String $slug, TaskRepository $taskRepository): Response
    {
        $task = $taskRepository->findOneBySlug($slug);
        if($task == null) {
            throw new NotFoundHttpException('An error occured : The task doesn\'t exist.');
        }

        if (!$this->isGranted(TaskVoter::VIEW, $task)) {
            $this->addFlash('danger', 'You don\'t have the permissions to consult this task');
            return $this->redirectToRoute('task_index');
        }
        
        return $this->render('task/view.html.twig', [
            'controller_name' => 'TaskController',
            'task' => $task
        ]);
    }

    #[Route('/create', name: 'task_create')]
    public function create(
    Request $request): Response
    {
        $task = new Task;
        $form = $this->createForm(TaskType::class,$task);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){

                $task->updateTimestamps();

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                if($this->taskFileService->FileServiceOnAttribute($task,'FILE_CREATE')) {
                    $this->addFlash("success", "The save file has been created successfully");
                } else {
                    $this->addFlash("warning", "An error occured during the creation of the save file.");
                }
                
                $this->addFlash("success", "The task gets created successfully");
                return $this->redirectToRoute("task_index");
            } else {
                $this->addFlash("error", "An Error occured during the taks's update");
            }
        }

        return $this->render('task/create.html.twig', [
            'controller_name' => 'TaskController',
            'form' => $form
        ]);
    }

    #[Route('/edit/{slug}', name: 'task_edit ')]
    public function edit (String $slug,
    TaskRepository $taskRepository,
    Request $request): Response
    {
        $task = $taskRepository->findOneBySlug($slug);
        if($task == null) {
            throw new NotFoundHttpException('An error occured : The task doesn\'t exist.');
        }

        if (!$this->isGranted(TaskVoter::EDIT, $task)) {
            $this->addFlash('danger', 'You don\'t have the permissions to edit this task');
            return $this->redirectToRoute('task_index');
        }

        if (!$this->taskService->canEdit($task)) {
            $this->addFlash('danger', 'This task can\'t being edited');
            return $this->redirectToRoute('task_index');
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){

                $task->updateTimestamps();

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                if($this->taskFileService->FileServiceOnAttribute($task,'FILE_UPDATE')) {
                    $this->addFlash("success", "The save file has been updated successfully");
                } else {
                    $this->addFlash("warning", "An error occured during the update of the save file.");
                }

                $this->addFlash("success", "The task gets updated successfully");
                return $this->redirectToRoute('task_index');
            } else {
                $this->addFlash("error", "An Error occured during the taks's update");
            }
        }

        return $this->render('task/edit.html.twig', [
            'controller_name' => 'TaskController',
            'form' => $form
        ]);
    }

    #[Route('/delete/{slug}', name: 'task_delete')]
    public function delete(
    String $slug,
    TaskRepository $taskRepository): RedirectResponse
    {
        $task = $taskRepository->findOneBySlug($slug);
        if($task == null) {
            throw new NotFoundHttpException('An error occured : The task doesn\'t exist.');
        }

        if (!$this->isGranted(TaskVoter::DELETE, $task)) {
            $this->addFlash('danger', 'You don\'t have the permissions to delete this task');
            return $this->redirectToRoute('task_index');
        }

        if($this->taskFileService->FileServiceOnAttribute($task,'FILE_DELETE')) {
            $this->addFlash("success", "The save file has been deleted successfully");
        } else {
            $this->addFlash("warning", "An error occured during the delete of the save file.");
        }
        
        $this->entityManager->remove($task);
        $this->entityManager->flush();
        $this->addFlash("success", "The task gets deleted successfully");


        return $this->redirectToRoute("task_index");
    }

    #[Route('/listeFile', name: 'task_listeFile')]
    public function listeFile(): Response
    {
        $listeFile = $this->taskFileService->listTasksFiles();
        return $this->render('task/listeFile.html.twig', [
            'controller_name' => 'TaskController',
            'tasks' => $listeFile
        ]);
    }

    #[Route('/viewFile/{nameFiles}', name: 'task_nameFiles')]
    public function viewFile(string $nameFiles): Response
    {
        $task = $this->taskFileService->viewTaskFiles($nameFiles);
        return $this->render('task/viewFile.html.twig', [
            'controller_name' => 'TaskController',
            'task' => $task
        ]);
    }
}
