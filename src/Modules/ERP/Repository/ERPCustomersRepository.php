<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomers[]    findAll()
 * @method ERPCustomers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomers::class);
    }

    // /**
    //  * @return ERPCustomers[] Returns an array of ERPCustomers objects
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
    public function findOneBySomeField($value): ?ERPCustomers
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findInsuredCustomers($company){
      $query="SELECT c.id as id, c.code as code, c.name as name, c.socialname as socialname, c.vat as vat, ct.supplement as supplement,ct.cescecode as cescecode, m.name as paymentmethod
      FROM erpcustomers c
        LEFT JOIN erppayment_methods m
        ON m.id=c.paymentmethod_id
        LEFT JOIN erpcustomer_commercial_terms ct
        ON ct.customer_id=c.id
        WHERE ct.insured=1 AND c.company_id=:company AND c.deleted=0 AND c.active=1 ";
      $query.=" ORDER BY c.vat ASC";
      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getAddresses($customer){
        $query='SELECT a.id as id, a.name as name, a.address as address, a.postcode as postcode, a.city as city, a.phone as phone, a.email as email
        FROM erpcustomers c
        LEFT JOIN erpaddresses a
        ON a.customer_id=c.id
        WHERE a.customer_id=:customer AND a.active=1 AND a.deleted=0 ORDER BY name ASC';
        $params=['customer' => $customer];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


}
