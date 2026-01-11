<?php

namespace App\Repository;

use App\Entity\Valoracion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Valoracion>
 */
class ValoracionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Valoracion::class);
    }

    //    /**
    //     * @return Valoracion[] Returns an array of Valoracion objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Valoracion
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * @return array Returns an array of [0 => Palabra, 'likesCount' => int]
     */
    public function findTopWordsByLikes(\DateTimeInterface $startDate = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('p as palabra', 'COUNT(v.id) as likesCount')
            ->join('v.palabra', 'p')
            ->where('v.likeActiva = :active')
            ->setParameter('active', true)
            ->groupBy('p.id')
            ->orderBy('likesCount', 'DESC');

        if ($startDate) {
            $qb->andWhere('v.fechaCreacion >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        return $qb->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
