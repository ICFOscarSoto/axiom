<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleCommentsEmails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleCommentsEmails|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleCommentsEmails|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleCommentsEmails[]    findAll()
 * @method GlobaleCommentsEmails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleCommentsEmailsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleCommentsEmails::class);
    }

    public function getEmails($entity, $entity_id, $company){
      $query="SELECT c.id, c.user_id, u.name as user_name, u.lastname as user_lastname, c.fromaddress, c.toaddress, c.subject, c.content, c.dateadd, c.dateupd, 'email' as type from globale_comments_emails c
              LEFT JOIN globale_users u ON u.id = c.user_id
              WHERE c.entity= :entity AND c.entity_id= :entity_id AND c.company_id = :company AND c.deleted=0 AND c.active=1
              ORDER BY dateadd DESC";

      $params=['entity' => $entity, 'entity_id' => $entity_id, 'company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
    // /**
    //  * @return GlobaleCommentsEmails[] Returns an array of GlobaleCommentsEmails objects
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
    public function findOneBySomeField($value): ?GlobaleCommentsEmails
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
