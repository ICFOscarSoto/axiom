<?php

namespace App\Modules\Email\Repository;

use App\Modules\Email\Entity\EmailSubjects;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EmailSubjects|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailSubjects|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailSubjects[]    findAll()
 * @method EmailSubjects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailSubjectsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailSubjects::class);
    }
    public function findByAccountAndUid($account, $uid)
    {
        $query=$this->createQueryBuilder('s')
            ->join('s.folder', 'f')
            ->join('f.emailAccount', 'a')
            ->andWhere('s.uid = :val')
            ->andWhere('f.id = :valAccount')
            ->setParameter('val', $uid)
            ->setParameter('valAccount', $account)
            ->setMaxResults(1)
            ->getQuery()
        ;
        return $query->getOneOrNullResult();
    }

    public function findByAccountAndMessageId($account, $messageId)
    {
        $query=$this->createQueryBuilder('s')
            ->join('s.folder', 'f')
            ->join('f.emailAccount', 'a')
            ->andWhere('s.messageId = :val')
            ->andWhere('a.id = :valAccount')
            ->setParameter('val', $messageId)
            ->setParameter('valAccount', $account)
            ->setMaxResults(1)
            ->getQuery()
        ;
        return $query->getOneOrNullResult();
    }

    public function deleteByFolder($folder)
    {
      $query="DELETE FROM email_subjects WHERE folder_id=:folder";
      $params=['folder' => $folder];
      $this->getEntityManager()->getConnection()->executeQuery($query, $params);
      return true;
    }


    // /**
    //  * @return EmailSubjects[] Returns an array of EmailSubjects objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmailSubjects
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
