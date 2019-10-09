<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPCustomerGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPCustomerGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPCustomerGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPCustomerGroups[]    findAll()
 * @method AERPCustomerGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPCustomerGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPCustomerGroups::class);
    }
}
