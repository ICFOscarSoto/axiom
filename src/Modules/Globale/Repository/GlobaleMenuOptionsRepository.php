<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleMenuOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuOptions[]    findAll()
 * @method MenuOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleMenuOptionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleMenuOptions::class);
    }

    public function formatBreadcrumb($route, $module=null, $name=null){
		$path=array();

		$row=$this->findByRoute($route, $module, $name);
		if($row!=null){
			$item=array();
			$item["rute"]=$row->getRute();
      $item["routeParams"]=json_decode($row->getRouteparams(),true);
			$item["name"]=$row->getName();
			$item["icon"]=$row->getIcon();
			$path[]=$item;
			while($row->getParent()!=NULL ){

				$row=$this->findById($row->getParent());
				if($row!=null){
					$item=array();
					$item["rute"]=$row->getRute();
          $item["routeParams"]=json_decode($row->getRouteparams(),true);
					$item["name"]=$row->getName();
					$item["icon"]=$row->getIcon();
					$path[]=$item;
				}
			}
		}
		$item["rute"]='dashboard';
    $item["routeParams"]=[];
		$item["name"]='Dashboard';
		$item["icon"]='fa fa-dashboard';
		$path[]=$item;
		$path=array_reverse($path);
		return $path;
	}


	public function formatOptions($userdata){
		$options=array();
		$item = new GlobaleMenuOptions();
		$item->setRute('dashboard');
		$item->setName('Dashboard');
		$item->setIcon('fa fa-dashboard');
		$options[]=$item;
    $modules=array_unique(array_merge($this->getModules($userdata["companyId"]), [1])); //ensure module global allways active
    $roles=$userdata["roles"];
		foreach($roles as $role){
			$parents=$this->getParents($role);
			foreach($parents as $key_parent=>$parent){
        if($parent->getModule()!=null && !in_array($parent->getModule()->getId(), $modules)) {unset($parents[$key_parent]); continue;} //if module no active continue
				$childs=$this->getChilds($role, $parent->getId());
				foreach($childs as $key_child=>$child){
          if($child->getModule()!=null && !in_array($child->getModule()->getId(), $modules)) {unset($childs[$key_child]); continue;} //if module no active continue
        	$childs[$key_child]->childs=$this->getChilds($role, $child->getId());
          foreach($childs[$key_child]->childs as $sub_key_child=>$sub_child){
              if($sub_child->getModule()!=null && !in_array($sub_child->getModule()->getId(), $modules)) {unset($childs[$key_child]->childs[$sub_key_child]); continue;} //if module no active continue
              $childs[$key_child]->childs[$sub_key_child]->params=json_decode($childs[$key_child]->childs[$sub_key_child]->getRouteparams(),true);
          }
          $childs[$key_child]->params=json_decode($childs[$key_child]->getRouteparams(),true);
				}
				$parents[$key_parent]->childs=$childs;
			}
			$options=array_merge($options,$parents);
		}

		return $options;
	}

  public function getModules($company){

    $query="SELECT module_id FROM globale_companies_modules g WHERE companyown_id =:COMPANYID AND	g.active=1 AND g.deleted=0";
              $params=['COMPANYID' => $company];
    return array_column($this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll(),'module_id');
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


	public function findByRoute($route, $module=null, $name=null){

    if(!$module && !$name)
  		return $this->createQueryBuilder('f')
              ->andWhere('f.rute = :val_route')
              ->setParameter('val_route', $route)
              ->getQuery()
              ->getOneOrNullResult()
          ;
    else{
       $routeParams="{\"module\":\"".$module."\",\"name\":\"".$name."\"}";
       return $this->createQueryBuilder('f')
            ->andWhere('f.rute = :val_route')
            ->andWhere("replace(f.routeparams, ' ', '') = :val_routeParams")
            ->setParameter('val_route', $route)
            ->setParameter('val_routeParams', $routeParams)
            ->getQuery()
            ->getOneOrNullResult()
        ;
      }
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


}
