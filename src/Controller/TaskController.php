<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
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

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
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

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){

                $task->updateTimestamps();

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                $this->addFlash("success", "The task gets updated successfully");
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

        $this->addFlash("success", "The task gets deleted successfully");
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->redirectToRoute("task_index");
    }
}
