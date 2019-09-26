<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomersPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomersPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomersPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomersPrices[]    findAll()
 * @method ERPCustomersPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomersPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomersPrices::class);
    }
    
    public function findValid($product,$customer,$company,$date){
    
    $query="SELECT * FROM erpcustomers_prices e WHERE e.product_id=:PROD AND e.customer_id=:CUST AND e.company_id=:COMP AND (STR_TO_DATE(e.end, '%Y-%m-%d')>STR_TO_DATE(:DATE, '%Y-%m-%d') OR e.end=NULL) AND e.active=1 AND e.deleted=0";
    $params=['PROD' => $product->getId(),
             'CUST' => $customer->getId(),
             'COMP' => $company->getId(),
             'DATE' => $date];
    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    return $result;

    }
}
