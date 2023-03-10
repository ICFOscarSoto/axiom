<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPCustomerContacts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPContact[]    findAll()
 * @method AERPContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPCustomerContactsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPCustomerContacts::class);
    }
}
