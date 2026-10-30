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
    * @return Task[] Returns an array of Task objects
    */
   public function findAllTasks(Status $status, ?string $due_before = null)
   {
       $query = $this->createQueryBuilder('t')
           ->andWhere('t.status = :status')
           ->setParameter('status', $status);

        if ($due_before) {
            $query->andWhere('t.due_date < :due_date')
                ->setParameter('due_date', $due_before);
        }

        return $query->orderBy('t.due_date', 'ASC')
           ->orderBy('t.created_at', 'DESC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
