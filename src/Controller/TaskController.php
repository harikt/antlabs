<?php

namespace App\Controller;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Enum\Status;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
final class TaskController extends AbstractController
{
    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->json(['tasks' => $taskRepository->findAllTasks(Status::Todo)]);
    }

    #[Route(name: 'app_task_new', methods: ['POST'])]
    public function new(
        #[MapRequestPayload] TaskDto $taskDto
        #[MapRequestPayload] TaskInput $input,
        , EntityManagerInterface $entityManager): Response
    {
        dump($taskDto);
//         $task = new Task();
//
//         $entityManager->persist($task);
//         $entityManager->flush();
//
//      $task = 'test';
        return $this->json(['task' => $task]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/status', name: 'app_task_status', methods: ['PATCH'])]
    public function status(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $task =
        $entityManager->flush();

        // return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
