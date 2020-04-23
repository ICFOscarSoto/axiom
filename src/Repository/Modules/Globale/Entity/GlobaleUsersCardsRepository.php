<?php

namespace App\Repository\Modules\Globale\Entity;

use App\Modules\Globale\Entity\GlobaleUsersCards;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleUsersCards|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleUsersCards|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleUsersCards[]    findAll()
 * @method GlobaleUsersCards[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUsersCardsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUsersCards::class);
    }

    // /**
    //  * @return GlobaleUsersCards[] Returns an array of GlobaleUsersCards objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobaleUsersCards
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
