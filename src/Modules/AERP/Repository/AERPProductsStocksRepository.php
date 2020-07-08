<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPProductsStocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPProductsStocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPProductsStocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPProductsStocks[]    findAll()
 * @method AERPProductsStocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPProductsStocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPProductsStocks::class);
    }

    /**
    * @return AERPProductsStocks[] Returns an array of AERPProductsStocks objects
    */

    public function findWithStocks($product, $company)
    {
      $qb = $this->createQueryBuilder('e');
      $qb->where('e.stock > 0 AND e.product=:val_product AND e.company=:val_company AND e.active=1 AND e.deleted=0')
         ->setParameter('val_product', $product)
         ->setParameter('val_company', $company);

      return $qb->getQuery()->getResult();
    }


    /*
    public function findOneBySomeField($value): ?AERPProductsStocks
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
