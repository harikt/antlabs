<?php

namespace App\Controller;

use App\Dto\TaskListQuery;
use App\Dto\CreateTaskInput;
use App\Dto\UpdateTaskStatusInput;
use App\Entity\Task;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
final class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(
        #[MapQueryString(validationFailedStatusCode: 422)] TaskListQuery $query = new TaskListQuery(),
    ): JsonResponse
    {
        $result = $this->taskService->list($query->status, $query->dueBefore, $query->page, $query->limit);

        return $this->json([
            'tasks' => $result->tasks,
            'meta' => ['page' => $result->page, 'limit' => $result->limit, 'total' => $result->total, 'pages' => $result->pages()]
        ]);
    }

    #[Route(name: 'app_task_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateTaskInput $input,
    ): JsonResponse
    {
        return $this->json($this->taskService->create($input), JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}/status', name: 'app_task_status', methods: ['PATCH'])]
    public function status(
        Task $task,
        #[MapRequestPayload] UpdateTaskStatusInput $input,
    ): JsonResponse
    {
        return $this->json($this->taskService->updateStatus($task, $input->status));
    }

}
