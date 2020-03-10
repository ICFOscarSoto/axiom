<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSupplierActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSupplierActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSupplierActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSupplierActivities[]    findAll()
 * @method ERPSupplierActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSupplierActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSupplierActivities::class);
    }

    // /**
    //  * @return ERPSupplierActivities[] Returns an array of ERPSupplierActivities objects
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
    public function findOneBySomeField($value): ?ERPSupplierActivities
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function getChildrens($id_workactivity, $temp_childs, $user)
    {

      $qb= $this->createQueryBuilder('e')
      ->andWhere('e.parentid=:val_workactivity')
      ->andWhere('e.active=:val_active')
      ->andWhere('e.deleted=:val_deleted')
      ->setParameter('val_workactivity', $id_workactivity)
      ->setParameter('val_active', TRUE)
      ->setParameter('val_deleted', FALSE)

      ->getQuery()
      ->getResult();
      foreach($qb as $parent) {
        $child=["id"=>$parent->getId(),"name"=>addslashes($parent->getName()), "childrens"=>$this->getChildrens($parent->getId(),[], $user)];
        array_push($temp_childs,$child);
      }
      return $temp_childs;

    }

    public function getTree($user)
    {
      $qb= $this->createQueryBuilder('e')
      ->andWhere('e.parentid is null')
      ->andWhere('e.active=:val_active')
      ->andWhere('e.deleted=:val_deleted')
      ->setParameter('val_active', TRUE)
      ->setParameter('val_deleted', FALSE)
      ->getQuery()
      ->getResult();

      $childrens=[];
      foreach($qb as $parent) {
      $child=[];
      $child=["id"=>$parent->getId(),"name"=>addslashes($parent->getName()), "childrens"=>$this->getChildrens($parent->getId(), $child, $user)];
      array_push($childrens,$child);
      }

      return $childrens;

    }
}
