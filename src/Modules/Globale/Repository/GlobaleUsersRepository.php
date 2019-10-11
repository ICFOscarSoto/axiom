<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUsers::class);
    }

    public function getShareables($user, $query=""){
      return $this->createQueryBuilder('u')
          ->andWhere('u.company = :company')
          ->andWhere('u.active = :active')
          ->andWhere('u.deleted = :deleted')
          ->andWhere('u.id <> :user')
          ->andWhere('u.id <> :user')
          ->andWhere('u.name LIKE :query OR u.firstname LIKE :query OR u.email LIKE :query')
          ->setParameter('company', $user->getCompany())
          ->setParameter('active', true)
          ->setParameter('deleted', false)
          ->setParameter('user', $user->getId())
          ->setParameter('query', '%'.$query.'%')
          ->orderBy('u.id', 'ASC')
          ->getQuery()
          ->getResult()
      ;
    }

    function getNoUsers($user){
      return $this->createQueryBuilder('h')
          ->from('worker', 'w')
          ->leftJoin('u.user', 'u')
          ->where('w.user = :userId')
          ->setParameters(array(':userId' => null))
          ->getQuery()
          ->getResult();
    }

    public function isAdmin($user){
      $query="SELECT IF(COUNT(*)=0,0,1) admin FROM globale_user_groups g
              LEFT JOIN globale_users_user_groups ug ON ug.usergroup_id=g.id
              LEFT JOIN globale_users u ON u.id=ug.user_id
              WHERE u.id=:val_user AND g.isadmin=1 AND g.active=1 AND g.deleted=0 AND ug.active=1 AND ug.deleted=0";
      $params=['val_user' => $user];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
    }

}
