<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleComments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleComments|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleComments|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleComments[]    findAll()
 * @method GlobaleComments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleCommentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleComments::class);
    }

    public function getComments($entity, $entity_id, $company){
      $query="SELECT c.id, c.user_id, u.name as user_name, u.lastname as user_lastname, c.comment, c.dateadd, c.dateupd, 'comment' as type from globale_comments c
              LEFT JOIN globale_users u ON u.id = c.user_id
              WHERE c.entity= :entity AND c.entity_id= :entity_id AND c.company_id = :company AND c.deleted=0 AND c.active=1
              ORDER BY dateadd DESC";

      $params=['entity' => $entity, 'entity_id' => $entity_id, 'company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
    // /**
    //  * @return GlobaleComments[] Returns an array of GlobaleComments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobaleComments
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
