<?php

namespace App\Dto;

use App\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTaskRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 150)]
        public readonly string $title = '',

        public readonly ?string $description = null,

        #[Assert\Choice(callback: [Status::class, 'values'], message: 'Status must be one of: todo, in_progress, done.')]
        public readonly ?string $status = null,

        #[Assert\GreaterThan('now', message: 'Due date must be in the future.')]
        public readonly ?\DateTimeImmutable $dueDate = null,
    ) {
    }
}
