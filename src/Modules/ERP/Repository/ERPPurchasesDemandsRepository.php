<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPPurchasesDemands;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPurchasesDemands|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPurchasesDemands|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPurchasesDemands[]    findAll()
 * @method ERPPurchasesDemands[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPurchasesDemandsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPurchasesDemands::class);
    }

    // /**
    //  * @return ERPPurchasesDemands[] Returns an array of ERPPurchasesDemands objects
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
    public function findOneBySomeField($value): ?ERPPurchasesDemands
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getReasons($product){
        $query='SELECT pdr.id as id, pdr.name as name
        FROM erpproducts_variants pv
        LEFT JOIN erppurchases_demands_reasons pdr
        WHERE pdr.active=1 AND pdr.deleted=0 ORDER BY name ASC';
        $params=['product' => $product];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
}
