<?php
namespace App\Modules\Globale\Utils;
use App\Modules\Globale\Entity\GlobaleCompanies;

class GlobaleListUtils
{
    public function getRecords($user,$repository,$request,$manager,$listFields,$classname,$filters = array()): array
    {
		$return=array();
		$query = $repository->createQueryBuilder('p')
			->setFirstResult($request->query->getInt('start', 0))
			->setMaxResults($request->query->getInt('length', 10));
		$queryFiltered = $repository->createQueryBuilder('p')->select('count(p.id)');
    $queryTotal = $repository->createQueryBuilder('p')->select('count(p.id)');

    //Detect if class has attribute companyId
    if(method_exists($classname, "getCompany")){
      $query->andWhere('p.company = :val_company');
      $query->setParameter('val_company', $user->getCompany());
      $queryFiltered->andWhere('p.company = :val_company');
      $queryFiltered->setParameter('val_company', $user->getCompany());
      $queryTotal->andWhere('p.company = :val_company');
      $queryTotal->setParameter('val_company', $user->getCompany());
    }

		//Formamos el filtro de busqueda global
		$searchValue=$request->query->get('search');
		$searchValue=$searchValue["value"];
		if($searchValue!=""){
			foreach($manager->getClassMetadata($classname)->getColumnNames() as $column){
					$query->orWhere('p.'.strtolower($column).' LIKE :val_'.strtolower($column));
					$query->setParameter('val_'.strtolower($column), '%'.$searchValue.'%');
					$queryFiltered->andWhere('p.'.strtolower($column).' LIKE :val_'.strtolower($column));
					$queryFiltered->setParameter('val_'.strtolower($column), '%'.$searchValue.'%');
			}
			//Añadimos los campos de las relaciones
			foreach($listFields as $field){
				$path=explode('__', $field["name"]);
				if(count($path)>1){
					$query->orWhere($path[0].'.'.$path[1].' LIKE :val_'.$path[0].'_'.$path[1]);
					$query->setParameter('val_'.$path[0].'_'.$path[1], '%'.$searchValue.'%');
					$query->orWhere($path[0].'.'.$path[1].' LIKE :val_'.$path[0].'_'.$path[1]);
					$query->setParameter('val_'.$path[0].'_'.$path[1], '%'.$searchValue.'%');
				}
			}
		}

		//Formamos los filtros de busqueda por columna
		foreach($listFields as $key => $field){
				$searchValue=$request->query->get('columns');
				$searchValue=$searchValue[$key+1]['search']['value'];
				if($searchValue!=""){
					$path=explode('__', $field["name"]);
					if(count($path)>1){
						$query->andWhere($path[0].'.'.$path[1].' LIKE :val_'.$path[0].'_'.$path[1]);
						$query->setParameter('val_'.$path[0].'_'.$path[1], '%'.$searchValue.'%');
						$query->andWhere($path[0].'.'.$path[1].' LIKE :val_'.$path[0].'_'.$path[1]);
						$query->setParameter('val_'.$path[0].'_'.$path[1], '%'.$searchValue.'%');
					}else{
						$query->andWhere('p.'.$field["name"].' LIKE :val_'.$field["name"]);
						$query->setParameter('val_'.$field["name"], '%'.$searchValue.'%');
						$queryFiltered->andWhere('p.'.$field["name"].' LIKE :val_'.$field["name"]);
						$queryFiltered->setParameter('val_'.$field["name"], '%'.$searchValue.'%');
					}
				}
		}
		//Excluimos los elementos borrados
			$query->andWhere('p.deleted = :valDeleted');
            $query->setParameter('valDeleted', 0);
			$queryFiltered->andWhere('p.deleted = :valDeleted');
            $queryFiltered->setParameter('valDeleted', 0);

		//Formamos el orden de los datos
		if($request->query->has('order')){
			 $order=$request->query->get('order');
  			 $path=explode('__', $listFields[(($order[0]['column'])-1)*1]["name"]);
			 if(count($path)>1){
				$query->addOrderBy($path[0].'.'.$path[1], $order[0]['dir']);
			 }else{
				$query->addOrderBy('p.'.strtolower($listFields[(($order[0]['column'])-1)*1]["name"]), $order[0]['dir']);
			}
		}

		//Generamos los LEFT JOIN de la consulta
    $definedLeftJoin=[];
		foreach($listFields as $field){
		$path=explode('__', $field["name"]);
		if(count($path)>1){
        if(array_search($path[0], $definedLeftJoin)===FALSE){
				      $query->leftJoin('p.'.$path[0], $path[0]);
				      $queryFiltered->leftJoin('p.'.$path[0], $path[0]);
              $definedLeftJoin[]=$path[0];
        }
			}
		}

    //Añadimos los filtros pasados por parametros desde los controladores
    foreach($filters as $filter){
      //Creamos los leftJoin de las relaciones
      $path=explode('.', $filter["column"]);
      $column='';
      if(count($path)>1){
          $query->leftJoin('p.'.$path[0], $path[1], 'WITH', $path[1].'.'.$path[1].' = :val_'.$path[0].'0');
          $queryFiltered->leftJoin('p.'.$path[0], $path[1], 'WITH', $path[1].'.'.$path[1].' = :val_'.$path[0].'0');
          $queryTotal->leftJoin('p.'.$path[0], $path[1], 'WITH', $path[1].'.'.$path[1].' = :val_'.$path[0].'0');
          $column=$path[1].'.'.$path[1];
      }else{
          $column="p.".$filter["column"];
      }


      /*
      $query->leftJoin('p.user', 'c', 'WITH', 'c.company = :val_company');
      $query->andWhere('c.company = :val_company');
      $query->setParameter('val_company',$filter["value"]);
      */
      if($filter["type"]="and"){
        $query->andWhere($column.' = :val_'.$path[0].'0');
        $query->setParameter('val_'.$path[0].'0', $filter["value"]);
        $queryFiltered->andWhere($column.' = :val_'.$path[0].'0');
        $queryFiltered->setParameter('val_'.$path[0].'0', $filter["value"]);
        $queryTotal->andWhere($column.' = :val_'.$path[0].'0');
        $queryTotal->setParameter('val_'.$path[0].'0', $filter["value"]);
      }
    }
    //dump($query->getQuery()->getSql());
		$queryPaginator = $query->getQuery();
		//dump($queryPaginator->getSql());
		$records=$queryPaginator->getResult();

		$records=$queryPaginator->getResult();
		$return=array();
		$return["recordsTotal"]=$queryTotal->getQuery()->getSingleScalarResult();
		$return["recordsFiltered"]=$queryFiltered->getQuery()->getSingleScalarResult();
		$return["data"]=array();

    //dump($listFields);
    //dump($records);
		//Obtenemos los datos desde la persistencia
		foreach($records as $record){
			$data_ob=Array();
			foreach($listFields as $field){
          //Si el campo es una propiedad de un objeto hijo buscamos su valor
          $name=$field["name"];
          //if(!isset($field["origins"]) || $field["origins"]=="") $origin=$field["name"];
          $origin=$field["name"];


          $value='';
          $path=explode('__', $origin);
  				$obj=$record;
  				foreach($path as $step){
  					if(method_exists($obj, "get".ucfirst($step))){
  						$obj=$obj->{"get".ucfirst($step)}();
  					}
  				}
  				if(!is_object($obj)) {$value= $obj;}
  					else {
              if(get_class($obj)=="DateTime"){
                $value=$obj->format('Y-m-d H:i:s');
              }else $value='';
          }
          $data_ob[$name]=$value;


  				//Aplicamos los replaces
  				if(isset($field["replace"])){
  						foreach($field["replace"] as $key=>$replace){
  							if($data_ob[$name]==$key){
  								$data_ob[$name]=array($data_ob[$name],$replace);
  							break;
  								}
  						}
  				}

  				/*$path=explode('__', $field["name"]);
  				$obj=$record;
  				foreach($path as $step){
  					if(method_exists($obj, "get".ucfirst($step))){
  						$obj=$obj->{"get".ucfirst($step)}();
  					}
  				}

  				if(!is_object($obj)) {$data_ob[$field["name"]]= $obj;}
  					else {
              if(get_class($obj)=="DateTime"){
                $data_ob[$field["name"]]=$obj->format('Y-m-d H:i:s');
              }else $data_ob[$field["name"]]='';
            }
  				//Aplicamos los replaces
  				if(isset($field["replace"])){
  						foreach($field["replace"] as $key=>$replace){
  							if($data_ob[$field["name"]]==$key){
  								$data_ob[$field["name"]]=array($data_ob[$field["name"]] ,$replace);
  							break;
  								}
  						}
  				}*/

			}

			//Tags
			$tags=array();
      if(method_exists($record, "getDateupd")){
        if((time()-$record->getDateupd()->getTimestamp())<$record->updatedSeconds && $record->getDateupd()!=$record->getDateadd()){
          $tag_ob=array("type" => "warning", "name" => "Modificado");
          $tags[]=$tag_ob;
        }else{
          if(method_exists($record, "getDateadd"))
  					if((time()-$record->getDateadd()->getTimestamp())<$record->newSeconds){
  						$tag_ob=array("type" => "success", "name" => "Nuevo");
  						$tags[]=$tag_ob;
					  }
        }
      }
			$data_ob["_tags"]=$tags;
			$return["data"][]=$data_ob;
		}
		return $return;
    }
}

?>
