<?php

namespace App\Repository;

use App\Entity\Seguimiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seguimiento>
 */
class SeguimientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seguimiento::class);
    }

    //    /**
    //     * @return Seguimiento[] Returns an array of Seguimiento objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Seguimiento
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * @return array Returns an array of [0 => Usuario, 'followersCount' => int]
     */
    public function findTopUsersByFollowers(\DateTimeInterface $startDate = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('u as usuario', 'COUNT(s.id) as followersCount')
            ->join('s.seguido', 'u')
            ->groupBy('u.id')
            ->orderBy('followersCount', 'DESC');

        if ($startDate) {
            $qb->andWhere('s.fechaSeguimiento >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        return $qb->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function countFollowers(\App\Entity\Usuario $user): int
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.seguido = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countFollowing(\App\Entity\Usuario $user): int
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.seguidor = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isFollowing(\App\Entity\Usuario $follower, \App\Entity\Usuario $followed): bool
    {
        $result = $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.seguidor = :follower')
            ->andWhere('s.seguido = :followed')
            ->setParameter('follower', $follower)
            ->setParameter('followed', $followed)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}
