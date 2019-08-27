<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCategories[]    findAll()
 * @method ERPCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCategories::class);
    }

    public function getChildrens($id_category, $temp_childs, $user)
    {

      $qb= $this->createQueryBuilder('e')
      ->andWhere('e.parentid=:val_category')
      ->andWhere('e.active=:val_active')
      ->andWhere('e.deleted=:val_deleted')
      ->andWhere('e.company=:val_company')
      ->setParameter('val_category', $id_category)
      ->setParameter('val_active', TRUE)
      ->setParameter('val_deleted', FALSE)
      ->setParameter('val_company', $user->getCompany())
      ->getQuery()
      ->getResult();
      foreach($qb as $parent) {
        $child=["id"=>$parent->getId(),"name"=>$parent->getName(), "childrens"=>$this->getChildrens($parent->getId(),[], $user)];
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
      ->andWhere('e.company=:val_company')
      ->setParameter('val_active', TRUE)
      ->setParameter('val_deleted', FALSE)
      ->setParameter('val_company', $user->getCompany())
      ->getQuery()
      ->getResult();

      $childrens=[];
      foreach($qb as $parent) {
      $child=[];
      $child=["id"=>$parent->getId(),"name"=>$parent->getName(), "childrens"=>$this->getChildrens($parent->getId(), $child, $user)];
      array_push($childrens,$child);
      }

      return $childrens;

    }




    // /**
    //  * @return ERPCategories[] Returns an array of ERPCategories objects
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
    public function findOneBySomeField($value): ?ERPCategories
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
