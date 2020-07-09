<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPWarehouseLocations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPWarehouseLocations|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPWarehouseLocations|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPWarehouseLocations[]    findAll()
 * @method AERPWarehouseLocations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPWarehouseLocationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPWarehouseLocations::class);
    }

    private function arrayToObject(array $array, $className) {
      return unserialize(sprintf(
          'O:%d:"%s"%s',
          strlen($className),
          $className,
          strstr(serialize($array), ':')
      ));
    }

    /**
    * @return AERPWarehouseLocations[] Returns an array of AERPWarehouseLocations objects
    */

    public function findNotUsedByProduct($id, $user)
    {
      $query="SELECT wl.id FROM aerpwarehouse_locations wl
	      LEFT JOIN aerpwarehouses w ON wl.warehouse_id=w.id
	      LEFT JOIN aerpproducts_stocks s ON wl.id=s.location_id
	      LEFT JOIN aerpproducts p ON s.product_id=p.id
	      WHERE w.company_id=:company AND p.id=:product AND w.active=1 AND w.deleted=0 AND s.active=1 AND s.deleted=0";

        $params=['company' => $user->getCompany()->getId(), 'product' => $id];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
        $ids=[];
        foreach($result as $key=>$item){
          $ids[]=$item['id'];
        }
        $qb= $this->getEntityManager()->createQueryBuilder();
        $linked = $qb->select('wl')
                   ->from('\App\Modules\AERP\Entity\AERPWarehouseLocations', 'wl')
                   ->where($qb->expr()->notIn('wl.id', implode(',',$ids)).' AND wl.active=1 AND wl.deleted=0')
                   ->getQuery()
                   ->getResult();
        return $linked;
    }



    // /**
    //  * @return AERPWarehouseLocations[] Returns an array of AERPWarehouseLocations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AERPWarehouseLocations
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
