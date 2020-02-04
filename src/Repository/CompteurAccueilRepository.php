<?php

namespace App\Repository;

use App\Entity\CompteurAccueil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CompteurAccueil|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompteurAccueil|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompteurAccueil[]    findAll()
 * @method CompteurAccueil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteurAccueilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompteurAccueil::class);
    }

    // /**
    //  * @return CompteurAccueil[] Returns an array of CompteurAccueil objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompteurAccueil
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
