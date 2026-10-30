<?php

namespace App\Service;

use App\Dto\CreateTaskRequest;
use App\Dto\TaskFilter;
use App\Entity\Task;
use App\Enum\Status;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskRepository $taskRepository,
    ) {
    }

    public function create(CreateTaskRequest $request): Task
    {
        $task = (new Task($request->title))
            ->setDescription($request->description)
            ->setDueDate($request->dueDate);

        if (null !== $request->status) {
            $task->setStatus(Status::from($request->status));
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    /**
     * @return Task[]
     */
    public function list(TaskFilter $filter): array
    {
        return $this->taskRepository->findByFilters(
            null !== $filter->status ? Status::from($filter->status) : null,
            $filter->dueBefore,
        );
    }

    public function changeStatus(Task $task, Status $status): Task
    {
        $task->setStatus($status);
        $this->entityManager->flush();

        return $task;
    }
}
