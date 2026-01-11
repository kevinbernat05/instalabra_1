<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }
    /**
     * @return array [0 => Usuario, 'followersCount' => int]
     */
    public function findTopUsersByFollowers(int $limit = 10, \DateTimeInterface $startDate = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.seguimientosQueRecibe', 's')
            ->addSelect('COUNT(s.id) AS followersCount')
            ->groupBy('u.id')
            ->orderBy('followersCount', 'DESC');

        if ($startDate) {
            $qb->andWhere('s.fechaSeguimiento >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Usuario[] Returns an array of Usuario objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Usuario
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
