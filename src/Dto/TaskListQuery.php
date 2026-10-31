<?php

namespace App\Dto;

use App\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

final class TaskListQuery
{
    public function __construct(
        public readonly ?Status $status = null,
        public readonly ?\DateTimeImmutable $dueBefore = null,

        #[Assert\Positive]
        public readonly int $page = 1,

        #[Assert\Range(min: 1, max: 100)]
        public readonly int $limit = 20,
    ) {}
}
