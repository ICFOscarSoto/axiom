<?php
namespace App\Modules\Globale\Utils;
use App\Modules\Globale\Entity\Companies;

class ListUtils
{
    public function getRecords($repository,$request,$manager,$listFields,$classname): array
    {
		$return=array();

		$query = $repository->createQueryBuilder('p')
			->setFirstResult($request->query->getInt('start', 0))
			->setMaxResults($request->query->getInt('length', 10));
		$queryFiltered = $repository->createQueryBuilder('t')->select('count(t.id)');
		//Formamos el filtro de busqueda global
		$searchValue=$request->query->get('search');
		$searchValue=$searchValue["value"];
		if($searchValue!=""){
			foreach($manager->getClassMetadata($classname)->getColumnNames() as $column){
					$query->orWhere('p.'.strtolower($column).' LIKE :val_'.strtolower($column));
					$query->setParameter('val_'.strtolower($column), '%'.$searchValue.'%');
					$queryFiltered->andWhere('t.'.strtolower($column).' LIKE :val_'.strtolower($column));
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
						$queryFiltered->andWhere('t.'.$field["name"].' LIKE :val_'.$field["name"]);
						$queryFiltered->setParameter('val_'.$field["name"], '%'.$searchValue.'%');
					}
				}
		}
		//Excluimos los elementos borrados
			$query->andWhere('p.deleted = :valDeleted');
            $query->setParameter('valDeleted', 0);
			$queryFiltered->andWhere('t.deleted = :valDeleted');
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
		foreach($listFields as $field){
		$path=explode('__', $field["name"]);
		if(count($path)>1){
				$query->leftJoin('p.'.$path[0], $path[0]);
				$queryFiltered->leftJoin('t.'.$path[0], $path[0]);
			}
		}

		$queryPaginator = $query->getQuery();
		//dump($queryPaginator->getSql());
		$records=$queryPaginator->getResult();
		$queryTotal = $repository->createQueryBuilder('t')
			->select('count(t.id)');
		$records=$queryPaginator->getResult();
		$return=array();
		$return["recordsTotal"]=$queryTotal->getQuery()->getSingleScalarResult();
		$return["recordsFiltered"]=$queryFiltered->getQuery()->getSingleScalarResult();
		$return["data"]=array();

		//Obtenemos los datos desde la persistencia
		foreach($records as $record){
			$data_ob=Array();
			foreach($listFields as $field){


				$path=explode('__', $field["name"]);
				$obj=$record;
				foreach($path as $step){
					if(method_exists($obj, "get".ucfirst($step))){
						$obj=$obj->{"get".ucfirst($step)}();
					}
				}
				if(!is_object($obj)) $data_ob[$field["name"]]= $obj;
					else $data_ob[$field["name"]]='';

					//Aplicamos los replaces
				if(isset($field["replace"])){
						foreach($field["replace"] as $key=>$replace){
							if($data_ob[$field["name"]]==$key){
								$data_ob[$field["name"]]=array($data_ob[$field["name"]] ,$replace);
							break;
								}
						}
				}


			}

			//Tags
			$tags=array();
				if(method_exists($record, "getDateadd"))
					if((time()-$record->getDateadd()->getTimestamp())<$record->newSeconds){
						$tag_ob=array("type" => "success", "name" => "Nuevo");
						$tags[]=$tag_ob;
					}else{
						if(method_exists($record, "getDateupd"))
							if((time()-$record->getDateupd()->getTimestamp())<$record->updatedSeconds){
								$tag_ob=array("type" => "warning", "name" => "Modificado");
								$tags[]=$tag_ob;
							}
					}
			$data_ob["_tags"]=$tags;
			$return["data"][]=$data_ob;
		}
		return $return;
    }
}

?>
