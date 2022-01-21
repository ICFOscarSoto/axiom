<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRSchedulesWorkers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRSchedulesWorkers|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRSchedulesWorkers|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRSchedulesWorkers[]    findAll()
 * @method HRSchedulesWorkers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRSchedulesWorkersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRSchedulesWorkers::class);
    }

    public function workerSchedule($worker, $date){

      $query="SELECT schedule_id schedule from hrschedules_workers
              WHERE worker_id = :worker AND DATE(startdate) <= DATE(:date) AND (DATE(enddate) >=DATE(:date) or enddate IS NULL) AND deleted=0 AND active=1 ORDER BY startdate ASC ";

      $params=['worker' => $worker->getId(), 'date'=>$date];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
    }
}
