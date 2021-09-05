<?php

namespace App\Widgets\Repository;

use App\Widgets\Entity\WidgetsIotsensorth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WidgetsSearchengine|null find($id, $lockMode = null, $lockVersion = null)
 * @method WidgetsSearchengine|null findOneBy(array $criteria, array $orderBy = null)
 * @method WidgetsSearchengine[]    findAll()
 * @method WidgetsSearchengine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WidgetsIotsensorthRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WidgetsIotsensorth::class);
    }

}
