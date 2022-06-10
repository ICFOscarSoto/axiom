<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleHistories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleHistories|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleHistories|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleHistories[]    findAll()
 * @method GlobaleHistories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleHistoriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleHistories::class);
    }
    public function getHistories($entity, $entity_id, $company){
      $query="SELECT h.id, h.user_id, u.name as user_name, u.lastname as user_lastname, h.changes, h.dateadd, h.dateupd, 'history' as type from globale_histories h
              LEFT JOIN globale_users u ON u.id = h.user_id
              WHERE h.entity= :entity AND h.entity_id= :entity_id AND h.company_id = :company AND h.deleted=0 AND h.active=1
              ORDER BY dateadd DESC";

      $params=['entity' => $entity, 'entity_id' => $entity_id, 'company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

}
