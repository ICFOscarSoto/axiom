<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPStoresManagersConsumers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagedConsumers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagedConsumers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagedConsumers[]    findAll()
 * @method ERPStoresManagedConsumers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersConsumersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersConsumers::class);
    }


    public function search($search, $manager){
      $tokens=explode('*',$search);
      $string='';
      foreach($tokens as $token){
        $string.=" AND (id='".$token."' OR name LIKE '%".$token."%' OR lastname LIKE '%".$token."%' OR code2 LIKE '%".$token."%' OR idcard LIKE '%".$token."%' OR nfcid LIKE '%".$token."%')";
      }

      $query="SELECT id, name, lastname, code2, idcard, nfcid, active from erpstores_managers_consumers where manager_id=".$manager->getId()." AND deleted=0".$string." LIMIT 100";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }


    // /**
    //  * @return ERPStoresManagedConsumers[] Returns an array of ERPStoresManagedConsumers objects
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
    public function findOneBySomeField($value): ?ERPStoresManagedConsumers
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
