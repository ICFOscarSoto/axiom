<?php
namespace App\Modules\Navision\Repository;

use App\Modules\Navision\Entity\NavisionTransfers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NavisionTransfers|null find($id, $lockMode = null, $lockVersion = null)
 * @method NavisionTransfers|null findOneBy(array $criteria, array $orderBy = null)
 * @method NavisionTransfers[]    findAll()
 * @method NavisionTransfers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NavisionTransfersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NavisionTransfers::class);
    }

    // /**
    //  * @return NavisionTransfers[] Returns an array of NavisionTransfers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NavisionTransfers
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function recivedTransfer ($transfer){
      $query="UPDATE navision_transfers
              SET received=1
              WHERE name= :name";
      $params=['name' => $transfer];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params);
      return $result;
    }

    public function getTransfersByStore($storeId){
      $query='SELECT product_id, date(datesend) as send, quantity
              from navision_transfers
              where destinationstore_id= :storeId';
      $params=['storeId' => $storeId];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params);
      return $result;
    }

    public function getTransfersManageds(){
      $query='SELECT name, date(datesend) as send, destinationstore_id as store
              from navision_transfers
              where destinationstore_id in (SELECT id FROM erpstores
                                            WHERE code IN ("EXPEALICAN", "LISBOAEXPE", "SALAMAEXPE", "INAEGESALB", "GESTOR ALI", "SALAMGESTO", "LISBOAGEST"))
              group by name
              order by date(datesend) desc';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    public function getTransferLines($transfer){
      $query='SELECT product_id, quantity
              from navision_transfers
              where name= :transfer';
      $params=['transfer' => $transfer["name"]];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;
    }
}
