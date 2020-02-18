<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HREquipments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HREquipments|null find($id, $lockMode = null, $lockVersion = null)
 * @method HREquipments|null findOneBy(array $criteria, array $orderBy = null)
 * @method HREquipments[]    findAll()
 * @method HREquipments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HREquipmentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HREquipments::class);
    }

    // /**
    //  * @return HREquipments[] Returns an array of HREquipments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HREquipments
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
