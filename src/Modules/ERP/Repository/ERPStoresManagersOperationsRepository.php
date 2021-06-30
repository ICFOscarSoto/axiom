<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersOperations|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersOperations|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersOperations[]    findAll()
 * @method ERPStoresManagersOperations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersOperationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersOperations::class);
    }

    // /**
    //  * @return ERPStoresManagersOperations[] Returns an array of ERPStoresManagersOperations objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersOperations
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getOperationsByConsumer($manager, $start, $end, $store){
    $date_start=$start->format("Y-m-d");
    $date_end=$end->format("Y-m-d");

    if($store==null){
        $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, COUNT(c.id) total
        FROM erpstores_managers_operations o
        LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
        WHERE o.active=1 AND o.DATE >= :START AND o.DATE<=:END
        GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
        $params=['START' => $date_start,
                 'END' => $date_end
                 ];

    }
    else{

      $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, COUNT(c.id) total
      FROM erpstores_managers_operations o
      LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
      WHERE o.active=1 AND o.DATE >= :START AND o.DATE<=:END AND o.store_id=:STORE
      GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
      $params=['START' => $date_start,
               'END' => $date_end,
               'STORE' => $store
               ];


    }

    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
    return $result;

  }
}
