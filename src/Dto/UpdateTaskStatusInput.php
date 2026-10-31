<?php

namespace App\Dto;

use App\Enum\Status;

final class UpdateTaskStatusInput
{
    public function __construct(
        public readonly Status $status,
    ) {}
}
