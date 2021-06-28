<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersUsers[]    findAll()
 * @method ERPStoresManagersUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersUsers::class);
    }

    public function getElegibleUsers($manager, $user)
    {
      $query="SELECT w.id FROM globale_users w WHERE w.company_id=".$user->getCompany()->getId()." AND w.id NOT IN (
        SELECT s.user_id FROM erpstores_managers_users s WHERE s.active=1 AND s.deleted=0
      )
      AND w.active=1 AND w.deleted=0 ORDER by w.name,w.lastname";
      $params=[];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }

    // /**
    //  * @return ERPStoresManagersUsers[] Returns an array of ERPStoresManagersUsers objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersUsers
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
