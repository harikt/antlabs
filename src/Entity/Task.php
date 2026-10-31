<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\TaskRepository;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 150)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(enumType: Status::class, options: ['default' => 'todo'])]
    private Status $status = Status::Todo;

    #[ORM\Column(type: 'date_point', nullable: true)]
    private ?DatePoint $dueDate = null;

    #[ORM\Column(type: 'date_point')]
    private DatePoint $createdAt;

    #[ORM\Column(type: 'date_point')]
    private DatePoint $updatedAt;

    public function __construct()
    {
        $now = new DatePoint();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDueDate(): ?DatePoint
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate ? DatePoint::createFromInterface($dueDate) : null;

        return $this;
    }

    public function getCreatedAt(): DatePoint
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DatePoint $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DatePoint
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DatePoint $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }
}
