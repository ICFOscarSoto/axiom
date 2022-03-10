<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRWorkers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorker|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorker|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorker[]    findAll()
 * @method HRWorker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry){
        parent::__construct($registry, HRWorkers::class);
    }


    public function isWorking($worker){
      $conn = $this->getEntityManager()->getConnection();
      $query = "SELECT c.* FROM hrclocks c
              LEFT JOIN hrworkers h ON h.id = c.worker_id
              WHERE c.active = 1 AND c.deleted = 0 AND c.invalid <> 1
              AND c.start IS not null AND c.end IS NULL AND
              h.active = 1 AND h.deleted = 0 AND h.id = :worker_id";
      $params=['worker_id' => $worker->getId()];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      if(count($result)==0) return false; else return true;

    }

    function getNoUsers($user){
      return $this->createQueryBuilder('w')
          ->leftJoin('w.user', 'u')
          ->where('w.user = :userId')
          ->setParameters(array(':userId' => null))
          ->getQuery()
          ->getResult();
    }

    public function getWorkersByProfile($company, $profile){
      $query="SELECT id, name, lastname from hrworkers WHERE company_id=:company AND (profile_id=:profile OR profile2_id=:profile OR profile3_id=:profile OR profile4_id=:profile) AND active=1 AND deleted=0 ORDER BY name, lastname";
      $params=['company' => $company->getId(), 'profile'=>$profile];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
}
