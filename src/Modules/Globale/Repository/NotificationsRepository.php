<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\Notifications;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Notifications|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notifications|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notifications[]    findAll()
 * @method Notifications[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Notifications::class);
    }

    // /**
    //  * @return Notifications[] Returns an array of Notifications objects
    //  */

    public function findNoReaded($userId)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :val')
			->andWhere('n.readed = 0')
            ->setParameter('val', $userId)
            ->orderBy('n.dateadd', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

	 public function findById($id, $userId)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
			->andWhere('n.id = :id')
            ->setParameter('userId', $userId)
			->setParameter('id', $id)
            ->orderBy('n.dateadd', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Notifications
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
