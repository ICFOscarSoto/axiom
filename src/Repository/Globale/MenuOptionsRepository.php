<?php

namespace App\Repository\Globale;

use App\Entity\Globale\MenuOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuOptions[]    formatOptions($roles)
 * @method MenuOptions[]    getParents($role)
 * @method MenuOptions[]    getChilds($role)
 * @method MenuOptions[]    findByRole($role)
findByRole
 */
class MenuOptionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MenuOptions::class);
    }

	public function formatBreadcrumb($route){
		$path=array();
	
		$row=$this->findByRoute($route);
		if($row!=null){
			$item=array();
			$item["rute"]=$row->getRute();
			$item["name"]=$row->getName();
			$item["icon"]=$row->getIcon();
			$path[]=$item;
			while($row->getParent()!=NULL ){
				
				$row=$this->findById($row->getParent());
				if($row!=null){
					$item=array();
					$item["rute"]=$row->getRute();
					$item["name"]=$row->getName();
					$item["icon"]=$row->getIcon();
					$path[]=$item;
				}
			}
		}
		$item["rute"]='dashboard';
		$item["name"]='Dashboard';
		$item["icon"]='fa fa-dashboard';
		$path[]=$item;
		$path=array_reverse($path);
		return $path;
	}
	
	
	public function formatOptions($roles){
		$options=array();
		$item = new MenuOptions();
		$item->setRute('dashboard');
		$item->setName('Dashboard');
		$item->setIcon('fa fa-dashboard');
		$options[]=$item;
		foreach($roles as $role){
			$parents=$this->getParents($role);
			foreach($parents as $key_parent=>$parent){
				
				$childs=$this->getChilds($role, $parent->getId());
				foreach($childs as $key_child=>$child){
					$childs[$key_child]->childs=$this->getChilds($role, $child->getId());			
				}		
				$parents[$key_parent]->childs=$childs;
			}
			$options=array_merge($options,$parents);
		}
		
		return $options;
	}
	
	 /**
     * @return GlobalMenuOptions[] Returns an array of GlobalMenuOptions objects
    */
	public function getParents($role){
		return $this->createQueryBuilder('f')
            ->andWhere('f.roles LIKE :val_role')
			->andWhere('f.parent IS NULL')
            ->setParameter('val_role', '%'.$role.'%')
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
		
	}
	public function findById($id){
		return $this->createQueryBuilder('f')
            ->andWhere('f.id = :val_id')
            ->setParameter('val_id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
		
	}
	
	
	public function findByRoute($route){
		return $this->createQueryBuilder('f')
            ->andWhere('f.rute = :val_route')
            ->setParameter('val_route', $route)
            ->getQuery()
            ->getOneOrNullResult()
        ;
		
	}
	

	/**
     * @return GlobalMenuOptions[] Returns an array of GlobalMenuOptions objects
    */
	public function getChilds($role, $parent){
		return $this->createQueryBuilder('f')
            ->andWhere('f.roles LIKE :val_role')
			->andWhere('f.parent = :val_parent')
            ->setParameter('val_role', '%'.$role.'%')
			->setParameter('val_parent', $parent)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
		
	}
	
    /**
     * @return GlobalMenuOptions[] Returns an array of GlobalMenuOptions objects
    */
    public function findByRole($role)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.roles LIKE :val')
            ->setParameter('val', '%'.$role.'%')
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
 

    /*
    public function findOneBySomeField($value): ?Famenuoptions
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
