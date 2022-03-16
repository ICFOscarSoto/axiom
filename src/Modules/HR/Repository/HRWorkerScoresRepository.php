<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRWorkerScores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorkerScores|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorkerScores|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorkerScores[]    findAll()
 * @method HRWorkerScores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkerScoresRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRWorkerScores::class);
    }

    public function getScoreMonth($worker, $year, $month){
      $whereSql='';
      $groupBySql='';
      if($worker!=null){
          $whereSql.=" AND hs.worker_id=:worker";
          $groupBySql.=',hs.worker_id';
      }
      if($month!=null){
          $whereSql.=" AND MONTH(hs.date)=:month";
          $groupBySql.=',MONTH(hs.date)';
      }

      $query="SELECT worker_id, MONTH(hs.date), YEAR(hs.date), avg(hs.score) as score FROM hrworker_scores hs
              WHERE hs.active = 1 AND hs.deleted = 0 AND YEAR(hs.date)=:year".$whereSql."
              GROUP BY YEAR(hs.date)".$groupBySql;
      $params=['worker' => $worker, 'month'=>$month, 'year'=>$year];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if($result===FALSE) return 3; //Normal value in scale
      return $result['score'];
    }

    public function getScoreTotal($worker){
      $whereSql='';
      $groupBySql='';
      if($worker!=null){
          $whereSql.=" AND hs.worker_id=:worker";
          $groupBySql.='GROUP BY hs.worker_id';
      }

      $query="SELECT worker_id, YEAR(hs.date), avg(hs.score) as score FROM hrworker_scores hs
              WHERE hs.active = 1 AND hs.deleted = 0".$whereSql."
              ".$groupBySql;
      $params=['worker' => $worker];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      if($result===FALSE) return 3; //Normal value in scale
      return $result['score'];
    }
}
