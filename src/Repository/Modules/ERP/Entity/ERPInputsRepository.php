<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPInputs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPInputs|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPInputs|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPInputs[]    findAll()
 * @method ERPInputs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPInputsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPInputs::class);
    }

    public function findCleanCode($code, $supplier, $user)
    {
      $query="SELECT * FROM axiomdb_ferricam.erpinputs WHERE REGEXP_REPLACE(code, '[^A-Za-z0-9]', '') LIKE REGEXP_REPLACE('".$code."', '[^A-Za-z0-9]', '') and supplier_id=".$supplier->getId()." and company_id=".$user->getCompany()->getId()." and deleted=0";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetch();
      return $result;
    }

    public function findOurCleanCode($code, $user)
    {
      $query="SELECT * FROM axiomdb_ferricam.erpinputs WHERE REGEXP_REPLACE(ourcode, '[^A-Za-z0-9]', '') LIKE REGEXP_REPLACE('".$code."', '[^A-Za-z0-9]', '') and company_id=".$user->getCompany()->getId()." and deleted=0";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetch();
      return $result;
    }

    // /**
    //  * @return ERPInputs[] Returns an array of ERPInputs objects
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
    public function findOneBySomeField($value): ?ERPInputs
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
