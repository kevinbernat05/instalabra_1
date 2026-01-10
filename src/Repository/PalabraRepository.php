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
    public function findTopByLikes(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.valoraciones', 'v')             // unir con valoraciones
            ->addSelect('COUNT(v.id) AS likesCount')     // contar valoraciones
            ->andWhere('v.likeActiva = true OR v.id IS NULL') // solo likes activos
            ->groupBy('p.id')
            ->orderBy('likesCount', 'DESC')              // de mayor a menor
            ->setMaxResults($limit)                      // limitar resultados
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
