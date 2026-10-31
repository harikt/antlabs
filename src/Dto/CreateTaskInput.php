<?php

namespace App\Dto;

use App\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTaskInput
{
    public function __construct(
        #[Assert\Length(min: 3, max: 150, normalizer: 'trim')]
        public readonly string $title,

        public readonly ?string $description = null,

        #[Assert\Choice(
            callback: [Status::class, 'values'],
            message: 'Invalid status. Allowed: {{ choices }}.'
        )]
        public readonly string $status = Status::Todo->value,

        #[Assert\GreaterThan('now', message: 'The due date must be in the future.')]
        public readonly ?\DateTimeImmutable $dueDate = null,
    ) {}
}
