<?php

namespace App\Modules\Cloud\Repository;

use App\Modules\Cloud\Entity\CloudFiles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CloudFiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method CloudFiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method CloudFiles[]    findAll()
 * @method CloudFiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CloudFilesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CloudFiles::class);
    }

    public function findRecentPdfs($lastsync){
      if($lastsync==null) $lastsync=new \Datetime("1900-01-01");
      $query="SELECT * FROM cloud_files WHERE SUBSTRING_INDEX(name, '.', -1)='pdf' and dateupd>='".$lastsync->format('Y-m-d H:i:s')."' and deleted=0";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    // /**
    //  * @return CloudFile[] Returns an array of CloudFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CloudFile
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
