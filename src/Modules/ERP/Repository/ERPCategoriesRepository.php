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

    public function getChildrens($id_category, $temp_childs)
    {

      $qb= $this->createQueryBuilder('e')
      ->andWhere('e.parentid=:val')
      ->setParameter('val', $id_category)
      ->getQuery()
      ->getResult()
      ;
      foreach($qb as $parent) {

        $child=["id"=>$parent->getId(),"name"=>$parent->getName(), "childrens"=>$this->getChildrens($parent->getId(),[])];
        array_push($temp_childs,$child);
      }
      //dump($childrens);
      return $temp_childs;

    }

    public function getTree()
    {
      $qb= $this->createQueryBuilder('e')
      ->andWhere('e.parentid is null')
      ->getQuery()
      ->getResult()
      ;

      $childrens=[];
      foreach($qb as $parent) {
      $child=[];
      $child=["id"=>$parent->getId(),"name"=>$parent->getName(), "childrens"=>$this->getChildrens($parent->getId(), $child)];
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
