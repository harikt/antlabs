<?php

namespace App\Dto;

use App\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateTaskStatusRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [Status::class, 'values'], message: 'Status must be one of: todo, in_progress, done.')]
        public readonly ?string $status = null,
    ) {
    }
}
