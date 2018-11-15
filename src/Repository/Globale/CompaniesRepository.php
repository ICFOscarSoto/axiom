<?php

namespace App\Repository\Globale;

use App\Entity\Globale\Companies;
use App\Entity\Globale\Countries;
use App\Entity\Globale\Currencies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Companies|null find($id, $lockMode = null, $lockVersion = null)
 * @method Companies|null findOneBy(array $criteria, array $orderBy = null)
 * @method Companies[]    findAll()
 * @method Companies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompaniesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Companies::class);
    }

	   /**
     * @return GlobaCompanies[] Returns an array of Companies objects
     */

    public function findById($value)
    {
		$result=$this->createQueryBuilder('f')
            ->andWhere('f.id = :val')
            ->setParameter('val', $value)	
			->andWhere('f.deleted = :valDeleted')
            ->setParameter('valDeleted', 0)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getOneOrNullResult()
        ;
		/*$repository = $this->getEntityManager()->getRepository(Countries::class);
		$country=$repository->findById($result->getCountry()->getId());
		$repository = $this->getEntityManager()->getRepository(Currencies::class);
		$currency=$repository->findById($result->getCurrency()->getId());*/
		return $result;
	
    }
	
    // /**
    //  * @return Companies[] Returns an array of Companies objects
    //  */
	
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Companies
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
