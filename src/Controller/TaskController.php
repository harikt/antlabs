<?php

namespace App\Controller;

use App\Dto\CreateTaskRequest;
use App\Dto\TaskFilter;
use App\Dto\UpdateTaskStatusRequest;
use App\Entity\Task;
use App\Enum\Status;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks', format: 'json')]
final class TaskController extends AbstractController
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    #[Route('', name: 'api_task_list', methods: ['GET'])]
    public function list(#[MapQueryString] TaskFilter $filter = new TaskFilter()): JsonResponse
    {
        return $this->json($this->taskService->list($filter));
    }

    #[Route('', name: 'api_task_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateTaskRequest $request): JsonResponse
    {
        return $this->json($this->taskService->create($request), Response::HTTP_CREATED);
    }

    #[Route('/{id}/status', name: 'api_task_update_status', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateStatus(Task $task, #[MapRequestPayload] UpdateTaskStatusRequest $request): JsonResponse
    {
        return $this->json($this->taskService->changeStatus($task, Status::from($request->status)));
    }
}
