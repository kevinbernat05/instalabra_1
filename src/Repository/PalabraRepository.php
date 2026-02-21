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

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.usuario', 'u')
            ->andWhere('u.isBlocked = :blocked OR u.isBlocked IS NULL')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('blocked', false)
            ->orderBy('p.fechaCreacion', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.deletedAt IS NULL')
            ->orderBy('p.fechaCreacion', 'DESC') // DESC = más reciente primero
            ->getQuery()
            ->getResult();
    }
    public function findTopByLikes(int $limit = 5, \DateTimeInterface $startDate = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p AS palabraEntity, COUNT(v.id) AS likesCount') // Select entity and count
            ->where('p.deletedAt IS NULL')
            ->leftJoin('p.valoraciones', 'v', 'WITH', 'v.likeActiva = true' . ($startDate ? ' AND v.fechaCreacion >= :startDate' : ''))
            ->groupBy('p.id')
            ->orderBy('likesCount', 'DESC');

        if ($startDate) {
            $qb->setParameter('startDate', $startDate);
        }

        // Return array of [0 => Entity, 'likesCount' => count] which matches what we use in twig (p.0) if we select p instead of p.id?
        // Actually, if we select 'p AS palabraEntity', result is [['palabraEntity' => Object, 'likesCount' => 3], ...]
        // But previous template used `palabra.likesCount` which implies array access or `palabra.0`
        // Let's stick to the standard structure or adapt template. 
        // Providing 'p' as partial object is better.
        // Let's rely on standard result: it returns mixed array.

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult(); // Returns [[0 => Palabra, 'likesCount' => X], ...]
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
