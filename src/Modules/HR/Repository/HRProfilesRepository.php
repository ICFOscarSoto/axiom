<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRProfiles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRProfiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRProfiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRProfiles[]    findAll()
 * @method HRProfiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRProfilesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRProfiles::class);
    }

    public function getProfiles($user){
      $query="SELECT id, parent_id as parentid, name from hrprofiles WHERE company_id=:company AND active=1 and deleted=0 ORDER by id, parent_id";
      $params=['company' => $user->getCompany()->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    // /**
    //  * @return HRProfiles[] Returns an array of HRProfiles objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HRProfiles
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
