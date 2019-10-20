<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPCustomers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPCustomer|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPCustomer|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPCustomer[]    findAll()
 * @method AERPCustomer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPCustomersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPCustomers::class);
    }

    public function getNextAccounting($company)
    {
      $query="SELECT IFNULL(MAX(accountingaccount)+1,43000001)accountingaccount FROM aerpcustomers WHERE company_id=:company";
      $params=['company' => $company];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
    }
}
