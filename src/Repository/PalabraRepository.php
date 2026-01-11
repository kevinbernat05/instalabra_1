<?php

namespace App\Repository;

use App\Entity\Palabra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Palabra>
 */
class PalabraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Palabra::class);
    }
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.fechaCreacion', 'DESC') // DESC = más reciente primero
            ->getQuery()
            ->getResult();
    }
    public function findTopByLikes(int $limit = 10, \DateTimeInterface $startDate = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.valoraciones', 'v')
            ->addSelect('COUNT(v.id) AS likesCount')
            ->andWhere('v.likeActiva = true OR v.id IS NULL')
            ->groupBy('p.id')
            ->orderBy('likesCount', 'DESC');

        if ($startDate) {
            // WARNING: if I filter Valoration by date here, existing Palabras with NO likes after that date might disappear or have 0 likes.
            // If I use leftJoin, Palabras with NULL v.id count as 0.
            // If I put condition in WHERE, it acts like Inner Join.
            // I should put the date condition in the ON clause or use AND WHERE v.fecha >= ...
            // But if I use v.fecha >= ... and a palabra has no likes in that period, it will exclude the palabra entirely if I am not careful with LEFT JOIN + WHERE.
            // However, for "Top Words", we generally only want words that HAVE likes in that period?
            // Or at least, we count likes in that period.

            // If I use `andWhere('v.fechaCreacion >= :start')`, then Palabras with ZERO likes in that period will be excluded because `v` would be null (if no likes ever) or filtered out.
            // That is acceptable for "Top Words by Likes". We don't need to show words with 0 likes.
            $qb->andWhere('v.fechaCreacion >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Palabra[] Returns an array of Palabra objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Palabra
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
