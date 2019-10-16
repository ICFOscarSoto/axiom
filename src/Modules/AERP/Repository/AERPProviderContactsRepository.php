<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPProviderContacts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class AERPProviderContactsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPProviderContacts::class);
    }
}
