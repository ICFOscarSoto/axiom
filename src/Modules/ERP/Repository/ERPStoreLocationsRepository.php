<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoreLocations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoreLocations|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoreLocations|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoreLocations[]    findAll()
 * @method ERPStoreLocations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoreLocationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoreLocations::class);
    }

    public function findInventoryStoreLocations($company, $store=0)
    {
        $query="SELECT stl.id as id,stl.name as name FROM erpstore_locations stl
          WHERE stl.company_id=:company AND stl.active=1 ";
        if($store!=0) $query.=" AND stl.store_id=".$store;
        $query.=" ORDER BY stl.name ASC";
        $params=['company' => $company->getId()];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
   }
}
