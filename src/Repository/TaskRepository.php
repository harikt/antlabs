<?php

namespace App\Repository;

use App\Entity\Task;
use App\Enum\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findByFilters(?Status $status = null, ?\DateTimeImmutable $dueBefore = null): array
    {
        $qb = $this->createQueryBuilder('t');

        if (null !== $status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if (null !== $dueBefore) {
            $qb->andWhere('t.dueDate < :dueBefore')
                ->setParameter('dueBefore', $dueBefore->setTimezone(new \DateTimeZone('UTC')));
        }

        return $qb->orderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
