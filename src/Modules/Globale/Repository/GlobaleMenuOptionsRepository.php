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
    $allowedRoutes=$this->getAllowedRoutes($userdata["id"]);
    $roles=$userdata["roles"];
			$parents=$this->getParents();
			foreach($parents as $key_parent=>$parent){
        if(!count(array_intersect ($parent->getRoles(), $roles))){ unset($parents[$key_parent]); continue;} //if user hasn't enough role for this module
        if($parent->getModule()!=null && !in_array($parent->getModule()->getId(), $modules)) {unset($parents[$key_parent]); continue;} //if module no active  for this company continue
        if(!in_array("ROLE_GLOBAL", $roles) && $parent->getRute() && !in_array($parent->getRute(), $allowedRoutes)) {unset($parents[$key_parent]); continue;}
        $childs=$this->getChilds($parent->getId());
				foreach($childs as $key_child=>$child){
          if(!count(array_intersect ($child->getRoles(), $roles))){ unset($childs[$key_child]); continue;} //if user hasn't enough role for this module
          if($child->getModule()!=null && !in_array($child->getModule()->getId(), $modules)) {unset($childs[$key_child]); continue;} //if module no active  for this company continue
          if(!in_array("ROLE_GLOBAL", $roles) && $child->getRute()!=null && !in_array($child->getRute(), $allowedRoutes)) {unset($childs[$key_child]); continue;}
          $childs[$key_child]->childs=$this->getChilds($child->getId());
          foreach($childs[$key_child]->childs as $sub_key_child=>$sub_child){
              if(!count(array_intersect ($sub_child->getRoles(), $roles))){ unset($childs[$key_child]->childs[$sub_key_child]); continue;} //if user hasn't enough role for this module
              if($sub_child->getModule()!=null && !in_array($sub_child->getModule()->getId(), $modules)) {unset($childs[$key_child]->childs[$sub_key_child]); continue;} //if module no active  for this company continue
              if(!in_array("ROLE_GLOBAL", $roles) && $sub_child->getRute()!=null && !in_array($sub_child->getRute(), $allowedRoutes)) {unset($childs[$key_child]->childs[$sub_key_child]); continue;}
              if($sub_child->getRute()==null) {unset($childs[$key_child]->childs[$sub_key_child]); continue; }
              $childs[$key_child]->childs[$sub_key_child]->params=json_decode($childs[$key_child]->childs[$sub_key_child]->getRouteparams(),true);
          }
          $childs[$key_child]->params=json_decode($childs[$key_child]->getRouteparams(),true);
				}
				$parents[$key_parent]->childs=$childs;
			}
			$options=array_merge($options,$parents);
    //Remove empty modules without route
    if(!in_array("ROLE_GLOBAL", $roles)){
      foreach($options as $key=>$option){
          if(empty($option->childs) && $option->getRute()==null){ unset($options[$key]); continue; }
      }
    }

		return $options;
	}




  public function getModules($company){

    $query="SELECT module_id FROM globale_companies_modules g WHERE companyown_id =:COMPANYID AND	g.active=1 AND g.deleted=0";
              $params=['COMPANYID' => $company];
    return array_column($this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll(),'module_id');
  }

  public function getAllowedRoutes($user){
    $query="SELECT r.name FROM globale_permissions_routes_users ru
              LEFT JOIN globale_permissions_routes r ON r.id=ru.permissionroute_id
              WHERE ru.allowaccess=1 AND ru.user_id=:val_user AND ru.active=1 AND ru.deleted=0 AND r.active=1 AND r.deleted=0
            UNION
            SELECT r.name FROM globale_permissions_routes_user_groups rg
              LEFT JOIN globale_permissions_routes r ON r.id=rg.permissionroute_id
              WHERE rg.allowaccess=1 AND rg.active=1 AND rg.deleted=0 AND r.active=1 AND r.deleted=0
                    AND r.id NOT IN (SELECT r.id FROM globale_permissions_routes_users ru
                                     LEFT JOIN globale_permissions_routes r ON r.id=ru.permissionroute_id
                                     WHERE ru.allowaccess=0 AND ru.user_id=:val_user)
                    AND rg.usergroup_id IN (SELECT g.id FROM globale_users_user_groups ug
                                            LEFT JOIN globale_user_groups g ON g.id=ug.usergroup_id
                                            WHERE ug.user_id=:val_user AND g.active=1 AND g.deleted=0 AND ug.active=1 AND ug.deleted=0)



    ";
    $params=['val_user' => $user];
    return array_column($this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll(),'name');

  }

	 /**
     * @return GlobalMenuOptions[] Returns an array of GlobalMenuOptions objects
    */
    public function getParents(){
  		return $this->createQueryBuilder('f')
  			      ->andWhere('f.parent IS NULL')
              ->orderBy('f.position', 'ASC')
              ->getQuery()
              ->getResult()
          ;

  	}
	/*public function getParents($role){
		return $this->createQueryBuilder('f')
            ->andWhere('f.roles LIKE :val_role')
			      ->andWhere('f.parent IS NULL')
            ->setParameter('val_role', '%'.$role.'%')
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;

	}*/


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
	public function getChilds($parent){
		$childs= $this->createQueryBuilder('f')
            //->andWhere('f.roles LIKE :val_role')
			      ->andWhere('f.parent = :val_parent')
            //->setParameter('val_role', '%'.$role.'%')
			      ->setParameter('val_parent', $parent)
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
      foreach($childs as $key=>$child){
        if(empty($child->childs) && $child->getRute()==null) unset($childs[$key]);

      }

      return $childs;

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
