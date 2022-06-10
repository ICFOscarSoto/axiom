<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleCommentsCalls;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleCommentsCalls|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleCommentsCalls|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleCommentsCalls[]    findAll()
 * @method GlobaleCommentsCalls[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleCommentsCallsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleCommentsCalls::class);
    }

    public function getCalls($entity, $entity_id, $company){
      $query="SELECT c.id, c.user_id, u.name as user_name, u.lastname as user_lastname, c.type as calltype, c.extension, c.remote, c.filename, c.dateadd, c.dateupd, 'call' as type from globale_comments_calls c
              LEFT JOIN globale_users u ON u.id = c.user_id
              WHERE c.entity= :entity AND c.entity_id= :entity_id AND c.company_id = :company AND c.deleted=0 AND c.active=1
              ORDER BY c.dateadd DESC";

      $params=['entity' => $entity, 'entity_id' => $entity_id, 'company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
    // /**
    //  * @return GlobaleCommentsCalls[] Returns an array of GlobaleCommentsCalls objects
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
    public function findOneBySomeField($value): ?GlobaleCommentsCalls
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
