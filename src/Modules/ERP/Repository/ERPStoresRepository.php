<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStores|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStores|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStores[]    findAll()
 * @method ERPStores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStores::class);
    }

    // /**
    //  * @return ERPStores[] Returns an array of ERPStores objects
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
    public function findOneBySomeField($value): ?ERPStores
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getStores($product_id){
      $query='SELECT distinct id, code, name
              FROM erpstores
              WHERE active=1 AND deleted=0';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }

    public function getStoresInfo(){
      $query='SELECT id, name
              FROM erpstores
              WHERE active=1 AND deleted=0' ;
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }

    public function getInventoryStores(){
      $query='SELECT *
              FROM erpstores
              WHERE active=1 AND deleted=0 AND inventorymanager_id is not null
              ORDER BY code ASC';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }


    public function getStoresManagers($manager){
    $query='SELECT * FROM erpstores
            WHERE id IN (SELECT store_id FROM erpstores_users
					               WHERE managed=1 and user_id IN (SELECT user_id FROM erpstores_managers_users
											                                   WHERE manager_id IN (SELECT id FROM erpstores_managers
																		                                         WHERE NAME=:manager)))';
    $params=['manager' => $manager];
    $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    return $result;
    }


    public function getUnreceivedTransfers($manager){
    $query='SELECT name FROM navision_transfers
            WHERE destinationstore_id IN (SELECT id FROM erpstores
                        WHERE id IN (SELECT store_id FROM erpstores_users
            					               WHERE managed=1 and user_id IN (SELECT user_id FROM erpstores_managers_users
            											                                   WHERE manager_id IN (SELECT id FROM erpstores_managers
            																		                                         WHERE NAME=:manager))))
            AND received=0 AND ACTIVE=1 AND deleted=0
            AND dateadd<date_sub(CURTIME(), INTERVAL 4 DAY)
            GROUP BY NAME	';
    $params=['manager' => $manager];
    $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    return $result;
    }


}
