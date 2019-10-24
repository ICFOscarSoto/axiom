<?php

namespace App\Repository\Modules\AERP\Entity;

use App\Modules\AERP\Entity\AERPConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPConfiguration[]    findAll()
 * @method AERPConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPConfiguration::class);
    }

    // /**
    //  * @return AERPConfiguration[] Returns an array of AERPConfiguration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AERPConfiguration
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
