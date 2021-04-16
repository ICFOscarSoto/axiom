<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSalesTickets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSalesTickets|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSalesTickets|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSalesTickets[]    findAll()
 * @method ERPSalesTickets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSalesTicketsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesTickets::class);
    }

    // /**
    //  * @return ERPSalesTickets[] Returns an array of ERPSalesTickets objects
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
    public function findOneBySomeField($value): ?ERPSalesTickets
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getLastID(){
      $query='SELECT max(id)
      FROM erpsales_tickets';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);

    }
}
