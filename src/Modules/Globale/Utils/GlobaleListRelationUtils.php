<?php
namespace App\Modules\Globale\Utils;
use App\Modules\Globale\Entity\GlobaleCompanies;

class GlobaleListRelationUtils
{
    private function searchColumns($array, $search){
      if(is_array($array))
        foreach ($array as $key => $val) {
         if ($val['name'] === $search) {
             return $key;
         }
        }
      return null;
    }

    public function getRecords($user,$doctrine,$request,$manager,$listFields,$relation,$joins,$masterPosition,$filters=[],$raw=[],$maxResults=null): array
    {
		$return=array();
    $query="SELECT r".$masterPosition.".* FROM ".$relation." T1 ";
    $queryFiltered = "COUNT(r".$masterPosition.") FROM ".$relation." T1 ";
    $queryTotal = "COUNT(r".$masterPosition.") FROM ".$relation." T1 ";

    $leftJoins="";

    foreach($joins as $key=>$join){
      $leftJoins.="LEFT JOIN ".$join." r".$key." ON T1.".$join."_id=r".$key.".id ";
    }

    $query.=$leftJoins;
    $numResults="";
    if($maxResults===NULL){
      $numResults=" LIMIT 0,20";
    }else if($maxResults>=0){
      $numResults=" LIMIT ".$maxResults.",20";
    }

    $query.=$numResults;

/*
		//Formamos el filtro de busqueda global
		$searchValue=$request->query->get('search');
		$searchValue=$searchValue["value"];
		if($searchValue!=""){
        $metadata=$manager->getClassMetadata($classname);
		  	foreach($metadata->getColumnNames() as $column){
					$query->orWhere('p.'.$metadata->getFieldName($column).' LIKE :val_'.$metadata->getFieldName($column));
					$query->setParameter('val_'.$metadata->getFieldName($column), '%'.$searchValue.'%');
					$queryFiltered->andWhere('p.'.$metadata->getFieldName($column).' LIKE :val_'.$metadata->getFieldName($column));
					$queryFiltered->setParameter('val_'.$metadata->getFieldName($column), '%'.$searchValue.'%');
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
        //Solo añadimos los campos de tipo data

       if(!isset($field["type"])||$field["type"]=="data"){
  				$searchValue=$request->query->get('columns');
          //Buscar el key del field["name"0] en las columnas pasados por parametro
          $keyColumn=$this->searchColumns($searchValue, $field["name"]);
          if(!$keyColumn) continue;
          //TODO Campos mapeados como las coordenadas y demas petan aqui

  				$searchValue=$searchValue[$keyColumn]['search']['value'];
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
		}
		//Excluimos los elementos borrados
			$query->andWhere('p.deleted = :valDeleted');
            $query->setParameter('valDeleted', 0);
			$queryFiltered->andWhere('p.deleted = :valDeleted');
            $queryFiltered->setParameter('valDeleted', 0);

      //Detect if class has attribute companyId
      if(method_exists($classname, "getCompany")){
        $query->andWhere('p.company = :val_company');
        $query->setParameter('val_company', $user->getCompany());
        $queryFiltered->andWhere('p.company = :val_company');
        $queryFiltered->setParameter('val_company', $user->getCompany());
        $queryTotal->andWhere('p.company = :val_company');
        $queryTotal->setParameter('val_company', $user->getCompany());
      }


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


      if($filter["type"]="and"){
        $query->andWhere($column.' = :val_'.$path[0].'0');
        $query->setParameter('val_'.$path[0].'0', $filter["value"]);
        $queryFiltered->andWhere($column.' = :val_'.$path[0].'0');
        $queryFiltered->setParameter('val_'.$path[0].'0', $filter["value"]);
        $queryTotal->andWhere($column.' = :val_'.$path[0].'0');
        $queryTotal->setParameter('val_'.$path[0].'0', $filter["value"]);
      }
    }
		$queryPaginator = $query->getQuery();

		$records=$queryPaginator->getResult();

		$records=$queryPaginator->getResult();
		$return=array();
		$return["recordsTotal"]=$queryTotal->getQuery()->getSingleScalarResult();
		$return["recordsFiltered"]=$queryFiltered->getQuery()->getSingleScalarResult();
		$return["data"]=array();

		//Obtenemos los datos desde la persistencia*/


    $params=[];
    $records=$doctrine->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();



		foreach($records as $record){
			$data_ob=Array();
			foreach($listFields as $field){

          //si no esta establecido el tipo de dato los procesamos genericamente
          if(!isset($field["type"])) $field["type"] = "data";

          switch($field["type"]){
                    case "raw":
                              //Si existe el campo en el parametro raw lo cargamos
                              if(isset($raw[$field["name"]]))
                                $data_ob[$field["name"]]=$raw[$field["name"]];
                                else $data_ob[$field["name"]]="";
                    break;
                    case "location":
                              //Si existe el parametro data_latitude y data_longitude
                              $valid_location=true;
                              if(isset($field["data"]["latitude"]) && isset($field["data"]["longitude"])) {
                                $val="<button id=\"button-location-".$record->getId()."\" type=\"button\" class=\"btn btn-default\"";
                                if(method_exists($record, "get".ucfirst($field["data"]["latitude"]))){
                                  if ($record->{"get".ucfirst($field["data"]["latitude"])}()==null) {
                                    $valid_location=false;
                                  }else $val.="attr-id=\"".$record->getId()."\" attr-latitude=\"".$record->{"get".ucfirst($field["data"]["latitude"])}()."\"";
                                }
                                $val.=", ";
                                if(method_exists($record, "get".ucfirst($field["data"]["longitude"]))){
                                  if ($record->{"get".ucfirst($field["data"]["longitude"])}()==null) {
                                    $valid_location=false;
                                  }else $val.=" attr-longitude=\"".$record->{"get".ucfirst($field["data"]["longitude"])}()."\"";
                                }
                                $val.="><i class=\"fa fa-map-o\"></i></button>";
                                $data_ob[$field["name"]]=($valid_location)?$val:"";
                              }else $data_ob[$field["name"]]="";
                    break;
                    default:  //tipo data y otros no establecidos
                              //Si el campo es una propiedad de un objeto hijo buscamos su valor
                              /*$name=$field["name"];
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
                              }*/

                              $data_ob[$field["name"]]=$record[$field["name"]];
                              //Aplicamos los replaces
                              /*if(isset($field["replace"])){
                                  foreach($field["replace"] as $key=>$replace){
                                    if($data_ob[$name]==$key){
                                      $data_ob[$name]=array($data_ob[$name],$replace);
                                    break;
                                      }
                                  }
                              }*/

                    break;
          }
        }
	       $return["data"][]=$data_ob;
			}
/*
			//Tags
			$tags=array();
      if(method_exists($record, "getDateupd")){
        $updatedSeconds=property_exists($record,'updatedSeconds')?$record->updatedSeconds:21600;
        $newSeconds=property_exists($record,'newSeconds')?$record->newSeconds:21600;
        if((time()-$record->getDateupd()->getTimestamp())<$updatedSeconds && $record->getDateupd()!=$record->getDateadd()){
          $tag_ob=array("type" => "warning", "name" => "Modificado");
          $tags[]=$tag_ob;
        }else{
          if(method_exists($record, "getDateadd"))
  					if((time()-$record->getDateadd()->getTimestamp())<$newSeconds){
  						$tag_ob=array("type" => "success", "name" => "Nuevo");
  						$tags[]=$tag_ob;
					  }
        }
      }
			$data_ob["_tags"]=$tags;
			$return["data"][]=$data_ob;
		}*/

    return $return;
  }
}

?>
