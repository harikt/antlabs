<?php

namespace App\Repository;

use App\Entity\Task;
use App\Enum\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
    * @return Task[] Returns an array of Task objects
    */
    public function findAllTasks(
        ?Status $status = null,
        ?\DateTimeInterface $dueBefore = null,
        int $page = 1,
        int $limit = 20,
    ): Paginator
    {
        $query = $this->createQueryBuilder('t');

        if ($status) {
            $query
                ->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if ($dueBefore) {
            $query
                ->andWhere('t.dueDate < :dueDate')
                ->setParameter('dueDate', $dueBefore);
        }

        $query
            ->addOrderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        return new Paginator($query->getQuery(), fetchJoinCollection: false);
    }

}
