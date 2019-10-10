<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPProviders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPProvider|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPProvider|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPProvider[]    findAll()
 * @method AERPProvider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPProvidersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPProviders::class);
    }
}
