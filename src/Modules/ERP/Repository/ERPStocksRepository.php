<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStocks[]    findAll()
 * @method ERPStocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStocks::class);
    }

    public function findByProduct($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.product_id = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
