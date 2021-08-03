<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSATWarranties;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSATWarranties|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSATWarranties|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSATWarranties[]    findAll()
 * @method ERPSATWarranties[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSATWarrantiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSATWarranties::class);
    }

    // /**
    //  * @return ERPSATWarranties[] Returns an array of ERPSATWarranties objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ERPSATWarranties
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getLastID(){
      $query='SELECT max(id)
      FROM erpsatwarranties';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);

    }
}
