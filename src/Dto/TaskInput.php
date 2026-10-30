<?php
namespace App\Dto;

use App\Entity\Task;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Task::class)]
class  TaskInput {

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public readonly string $title,

        public readonly string $description,

        public readonly string $status,

        public readonly string $due_date,
    ) {
    }
}
