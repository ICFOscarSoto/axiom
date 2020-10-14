<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresUsers[]    findAll()
 * @method ERPStoresUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresUsers::class);
    }

    // /**
    //  * @return ERPStoresUsers[] Returns an array of ERPStoresUsers objects
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
    public function findOneBySomeField($value): ?ERPStoresUsers
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getStoreByUser($user){
      $query="SELECT s.name name,s.id id, su.preferential preferential
            FROM erpstores_users su
            LEFT JOIN erpstores s ON s.id=su.store_id
            WHERE su.user_id=:user AND s.active=1 AND s.deleted=0 AND su.active=1 AND su.deleted=0";
      $params=['user' => $user];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
}
