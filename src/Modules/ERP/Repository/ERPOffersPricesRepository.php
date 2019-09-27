<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPOffersPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPOffersPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPOffersPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPOffersPrices[]    findAll()
 * @method ERPOffersPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPOffersPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPOffersPrices::class);
    }
    
    public function findValid($product,$quantity,$company,$date){
    
    $query="SELECT * FROM erpoffers_prices e WHERE e.product_id=:PROD AND e.quantity=:QTY AND e.company_id=:COMP AND (STR_TO_DATE(e.end, '%Y-%m-%d')>STR_TO_DATE(:DATE, '%Y-%m-%d') OR e.end=NULL) AND e.active=1 AND e.deleted=0";
    $params=['PROD' => $product->getId(),
             'QTY' => $quantity,
             'COMP' => $company->getId(),
             'DATE' => $date];
    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    return $result;

    }

    // /**
    //  * @return ERPOffersPrices[] Returns an array of ERPOffersPrices objects
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
    public function findOneBySomeField($value): ?ERPOffersPrices
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
