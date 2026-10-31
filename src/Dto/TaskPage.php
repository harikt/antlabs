<?php

namespace App\Dto;

final class TaskPage
{
    /** @param Task[] $tasks */
    public function __construct(
        public readonly array $tasks,
        public readonly int $page,
        public readonly int $limit,
        public readonly int $total,
    ) {}

    public function pages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }
}
