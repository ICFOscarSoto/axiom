<?php

namespace App\Repository\Modules\HR\Entity;

use App\Modules\HR\Entity\HRWorkerEquipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorkerEquipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorkerEquipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorkerEquipment[]    findAll()
 * @method HRWorkerEquipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkerEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRWorkerEquipment::class);
    }

    // /**
    //  * @return HRWorkerEquipment[] Returns an array of HRWorkerEquipment objects
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
    public function findOneBySomeField($value): ?HRWorkerEquipment
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
