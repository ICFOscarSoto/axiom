<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyDeliveryNotesStates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyDeliveryNotesStates|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyDeliveryNotesStates|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyDeliveryNotesStates[]    findAll()
 * @method ERPBuyDeliveryNotesStates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyDeliveryNotesStatesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyDeliveryNotesStates::class);
    }

    // /**
    //  * @return ERPBuyDeliveryNotesStates[] Returns an array of ERPBuyDeliveryNotesStates objects
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
    public function findOneBySomeField($value): ?ERPBuyDeliveryNotesStates
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
