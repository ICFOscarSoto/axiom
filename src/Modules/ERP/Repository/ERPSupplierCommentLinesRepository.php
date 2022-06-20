<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSupplierCommentLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSupplierCommentLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSupplierCommentLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSupplierCommentLines[]    findAll()
 * @method ERPSupplierCommentLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSupplierCommentLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSupplierCommentLines::class);
    }

    // /**
    //  * @return ERPSupplierCommentLines[] Returns an array of ERPSupplierCommentLines objects
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
    public function findOneBySomeField($value): ?ERPSupplierCommentLines
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getCommentByBuyOrder($supplier){
      $result = [];
      $result['suppliercomment'] = '';
      $result['supplierbuyorder'] = '';
      $result['suppliershipping'] = '';
      $result['supplierpayment'] = '';
      $result['supplierspecial'] = '';
      $query="SELECT GROUP_CONCAT(concat(comment, if(start is null,'',concat(' - Desde ',date_format(start,'%d/%m/%Y'))), if(end is null,'',concat(' - Hasta ',date_format(end,'%d/%m/%Y')))) order by start, id separator '<br/>') as comment
              FROM erpsupplier_comment_lines
              WHERE supplier_id=:SUP AND active=TRUE and deleted=0 and (start is null or start<=now()) and (end is null or end>=now()) and type=0";
      $params=['SUP'  =>  $supplier->getId()];
      $resultq=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if ($resultq!=null)
       $result['suppliercomment'] = $resultq['comment'];
      $query="SELECT GROUP_CONCAT(concat(comment, if(start is null,'',concat(' - Desde ',date_format(start,'%d/%m/%Y'))), if(end is null,'',concat(' - Hasta ',date_format(end,'%d/%m/%Y')))) order by start, id separator '<br/>') as comment
              FROM erpsupplier_comment_lines
              WHERE supplier_id=:SUP AND active=TRUE and deleted=0 and (start is null or start<=now()) and (end is null or end>=now()) and type=1";
      $params=['SUP'  =>  $supplier->getId()];
      $resultq=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if ($resultq!=null)
       $result['supplierbuyorder'] = $resultq['comment'];
      $query="SELECT GROUP_CONCAT(concat(comment, if(start is null,'',concat(' - Desde ',date_format(start,'%d/%m/%Y'))), if(end is null,'',concat(' - Hasta ',date_format(end,'%d/%m/%Y')))) order by start, id separator '<br/>') as comment
              FROM erpsupplier_comment_lines
              WHERE supplier_id=:SUP AND active=TRUE and deleted=0 and (start is null or start<=now()) and (end is null or end>=now()) and type=4";
      $params=['SUP'  =>  $supplier->getId()];
      $resultq=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if ($resultq!=null)
       $result['suppliershipping'] = $resultq['comment'];
      $query="SELECT GROUP_CONCAT(concat(comment, if(start is null,'',concat(' - Desde ',date_format(start,'%d/%m/%Y'))), if(end is null,'',concat(' - Hasta ',date_format(end,'%d/%m/%Y')))) order by start, id separator '<br/>') as comment
              FROM erpsupplier_comment_lines
              WHERE supplier_id=:SUP AND active=TRUE and deleted=0 and (start is null or start<=now()) and (end is null or end>=now()) and type=5";
      $params=['SUP'  =>  $supplier->getId()];
      $resultq=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if ($resultq!=null)
       $result['supplierpayment'] = $resultq['comment'];
      $query="SELECT GROUP_CONCAT(concat(comment, if(start is null,'',concat(' - Desde ',date_format(start,'%d/%m/%Y'))), if(end is null,'',concat(' - Hasta ',date_format(end,'%d/%m/%Y')))) order by start, id separator '<br/>') as comment
              FROM erpsupplier_comment_lines
              WHERE supplier_id=:SUP AND active=TRUE and deleted=0 and (start is null or start<=now()) and (end is null or end>=now()) and type=6";
      $params=['SUP'  =>  $supplier->getId()];
      $resultq=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if ($resultq!=null)
       $result['supplierspecial'] = $resultq['comment'];
      return $result;

    }
}
