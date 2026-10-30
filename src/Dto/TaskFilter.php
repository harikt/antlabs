<?php

namespace App\Dto;

use App\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

final class TaskFilter
{
    public function __construct(
        #[Assert\Choice(callback: [Status::class, 'values'], message: 'Status must be one of: todo, in_progress, done.')]
        public readonly ?string $status = null,

        public readonly ?\DateTimeImmutable $dueBefore = null,
    ) {
    }
}
