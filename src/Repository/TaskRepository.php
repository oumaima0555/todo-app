<?php

namespace App\Repository;

use App\Entity\Task;
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

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    // AJOUT : Recherche et Filtre
public function findBySearchAndStatus(
    \App\Entity\User $user,
    ?string $search,
    ?bool $status
): array {
    $qb = $this->createQueryBuilder('t')
        ->andWhere('t.user = :user')
        ->setParameter('user', $user);

    // 🔍 Recherche
    if ($search) {
        $qb->andWhere('t.title LIKE :search OR t.description LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    // 🔎 SI filtre statut → pas de tri intelligent
    if ($status !== null) {
        $qb->andWhere('t.status = :status')
           ->setParameter('status', $status)
           ->orderBy('t.deadline', 'ASC');

        return $qb->getQuery()->getResult();
    }

    // ✅ SINON : TRI PAR DÉFAUT INTELLIGENT
    $qb
        // 1️⃣ en cours d’abord, terminées en bas
        ->orderBy('t.status', 'ASC')

        // 2️⃣ dates NULL à la fin
        ->addSelect(
            "CASE WHEN t.deadline IS NULL THEN 1 ELSE 0 END AS HIDDEN deadlineSort"
        )
        ->addOrderBy('deadlineSort', 'ASC')

        // 3️⃣ tri par date
        ->addOrderBy('t.deadline', 'ASC');

    return $qb->getQuery()->getResult();
}
}
