<?php

namespace App\Repository\Modules\HR\Entity;

use App\Modules\HR\Entity\HRMeetingsSummoneds;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRMeetingsSummoneds|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRMeetingsSummoneds|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRMeetingsSummoneds[]    findAll()
 * @method HRMeetingsSummoneds[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRMeetingsSummonedsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRMeetingsSummoneds::class);
    }

    public function getElegibleSummoneds($meeting, $user)
    {
      $query="SELECT w.id FROM hrworkers w WHERE w.company_id=".$user->getCompany()->getId()." AND w.id NOT IN (
        SELECT s.worker_id FROM hrmeetings_summoneds s WHERE s.meeting_id=".$meeting->getId()." AND s.active=1 AND s.deleted=0
      )
      AND w.id<>".$meeting->getAuthor()->getId()." AND w.active=1 AND w.deleted=0 ORDER by w.lastname,w.name";
      $params=[];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }

}
