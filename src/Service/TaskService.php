<?php

namespace App\Service;

use App\Dto\CreateTaskInput;
use App\Dto\TaskListQuery;
use App\Dto\TaskPage;
use App\Entity\Task;
use App\Enum\Status;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\DatePoint;

final class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function list(?Status $status, ?\DateTimeInterface $dueBefore, int $page = 1, int $limit = 20): TaskPage
    {
        $paginator = $this->taskRepository->findAllTasks($status, $dueBefore, $page, $limit);
        $total = count($paginator);

        return new TaskPage(iterator_to_array($paginator), $page, $limit, $total);
    }

    public function create(CreateTaskInput $input): Task
    {
        $task = (new Task())
            ->setTitle($input->title)
            ->setDescription($input->description)
            ->setStatus(Status::from($input->status))
            ->setDueDate($input->dueDate);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    public function updateStatus(Task $task, Status $status): Task
    {
        $task->setStatus($status)
            ->setUpdatedAt(new DatePoint());

        $this->entityManager->flush();

        return $task;
    }

}
