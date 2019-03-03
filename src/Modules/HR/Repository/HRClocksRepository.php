<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRClocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRClocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRClocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRClocks[]    findAll()
 * @method HRClocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRClocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRClocks::class);
    }

    public function findWorkersClocks($company){
      $query="select hrc.id, hrw.id workerid, hrw.name, hrw.lastname, hrc.start, hrc.end FROM hrclocks hrc
                                LEFT JOIN hrworkers hrw ON hrc.worker_id=hrw.id
                                WHERE hrc.id=(SELECT max(id) from hrclocks WHERE deleted=0 AND worker_id=hrc.worker_id)
                                AND hrw.company_id = :company AND hrc.deleted=0 AND hrw.deleted=0
                                group by (hrc.worker_id) ORDER BY name, lastname ASC";
      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();;
    }


    /*
    public function findOneBySomeField($value): ?HRClocks
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
