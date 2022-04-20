<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyOrders[]    findAll()
 * @method ERPBuyOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyOrdersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyOrders::class);
    }

    // /**
    //  * @return ERPBuyOrders[] Returns an array of ERPBuyOrders objects
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
    public function findOneBySomeField($value): ?ERPBuyOrders
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    // Último código utilizado
    public function getLastID(){
      $query='SELECT max(id) FROM erpbuy_orders';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);
    }

    // Obtiene el siguiente código a utilizar en la creación de un pedido de compra
    public function getNextCode(){
      $query='SELECT CAST(SUBSTRING(max(CODE),5) AS UNSIGNED) AS result
      FROM erpbuy_orders WHERE SUBSTRING(CODE,1,2)=SUBSTRING(year(NOW()),3,2)';
      $id = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);
      if ($id==null)
        $id = 0;
      $id++;
      return date('y').'PC'.str_pad($id,5,'0',STR_PAD_LEFT);
    }
}
