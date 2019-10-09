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
}
