<?php
namespace App\Modules\Globale\Utils;
use App\Modules\Globale\Entity\GlobaleCompanies;
use Symfony\Component\HttpFoundation\Session\Session;

class GlobaleListUtils
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

    private function clearData($data){
      if (is_null($data))
        $data = '';
      else
      if (is_array($data)){
        foreach ($data as $key => $value) {
          if (is_array($data[$key]))
            $data[$key] = $this->clearData($data[$key]);
          else
          if (is_null($value))
            $data[$key] = '';
        }
      }
      return $data;
    }

    public function getRecordsSQL($user,$repository,$request,$manager,$listFields,$classname,$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null): array{
      $listName=$request->attributes->get('name');
      $return=array();
      $start=0;
      $length=20;
      if($maxResults===NULL){
          $start=$request->query->getInt('start', 0);
          $length=$request->query->getInt('length', 20);
      }else if($maxResults>=0){
          $start=$request->query->getInt('start', 0);
          $length=$maxResults;
      } else if ($maxResults<0) $length=-1;
      //Formamos el orden de los datos
  		if($request->query->has('order')){
  			 $order=$request->query->get('order');
         $orderDir=$order[0]['dir'];
         $order=$order[0]['column'];
  		}else{
        $order=$orderBy;
        $orderDir='ASC';
      }
      //Filtros por columnas
      $searchValue=$request->query->get('columns');

      $sql_records="SELECT ";
      $sql_total="SELECT COUNT";
      $sql_filter="SELECT COUNT";
      foreach($select_fields as $field=>$as){
        $sql_records.=$field.' AS '.$as.',';
        if($sql_total=='SELECT COUNT') $sql_total.='('.$field.') total';
        if($sql_filter=='SELECT COUNT') $sql_filter.='('.$field.') total';
      }
      $sql_records=rtrim($sql_records,',');
      $sql_records.=' FROM '.$from;
      $sql_total.=' FROM '.$from;
      $sql_filter.=' FROM '.$from;

      $filter_where=$where;

      //Formamos el filtro de busqueda global
      //--------------------------------------------------------
      $global_filter="";
      $searchValue=$request->query->get('search');
      $searchValue=$searchValue["value"];
      if($searchValue!=""){
          $metadata=$manager->getClassMetadata($classname);
          foreach($metadata->getColumnNames() as $column){
            $tokensSearchValue=explode('*',$searchValue);
            foreach($tokensSearchValue as $key=>$tokenSearch){
              if($tokenSearch!=''){
                //check if start with
                if($tokenSearch[0]=='^'){ $starWildcard =''; $tokenSearch=substr($tokenSearch, 1);} else $starWildcard ='%';
                if($tokenSearch[strlen($tokenSearch)-1]=='^'){ $endWildcard =''; $tokenSearch=substr($tokenSearch, 0, -1);}else $endWildcard ='%';
                $global_filter.=' OR '.'p.'.$metadata->getFieldName($column).' LIKE \''.$starWildcard.$tokenSearch.$endWildcard.'\'';
              }
            }

          }
          //Añadimos los campos de las relaciones
          foreach($listFields as $field){
            $database_field=null;
            $fieldNames=explode('_o_',$field["name"]); //explotamos los campos concatenados
            if(count($fieldNames)>1){ //Si hay que concatenar algo
              $database_field='concat_ws(\' \',';
              foreach($fieldNames as $fieldName){
                  $path=explode('__', $fieldName);  //explotamos las relaciones foraneas
                  if(count($path)>1){
                    $database_field.=$path[0].'.'.$path[1].',';  // si viene de otra tabla
                  }else{
                    $database_field.='p.'.$path[0].','; // si viene de la misma tabla
                  }
              }
              $database_field=rtrim($database_field,',');
              $database_field.=')';  //cerramos el concat_ws
            }else{ //No hay nada que concatenar es un campo simple
              $path=explode('__', $fieldNames[0]);
              if(count($path)>1){
                $database_field=$path[0].'.'.$path[1];
              }else{
                foreach($select_fields as $fieldas=>$as){
                  if ($field["name"]==$as)
                    $database_field=$fieldas;
                }
                if( $database_field==null)
                  $database_field='p.'.$field["name"];
              }
            }
            $tokensSearchValue=explode('*',$searchValue);
            foreach($tokensSearchValue as $key=>$tokenSearch){
              if($tokenSearch!=''){
                if($tokenSearch[0]=='^'){ $starWildcard =''; $tokenSearch=substr($tokenSearch, 1);} else $starWildcard ='%';
                if($tokenSearch[strlen($tokenSearch)-1]=='^'){ $endWildcard =''; $tokenSearch=substr($tokenSearch, 0, -1);}else $endWildcard ='%';
                $global_filter.=' OR '.$database_field.' LIKE \''.$starWildcard.$tokenSearch.$endWildcard.'\'';
              }
            }

          }
      }
      $global_filter=ltrim($global_filter, ' OR');
      if($global_filter!='')
        $global_filter="AND (".$global_filter.")";
      $filter_where.=$global_filter;
      //++++++++++++++++++++++++++++++++++++++++++++++++++++++++

      //Formamos los filtros de busqueda por columna
      //--------------------------------------------------------
  		foreach($listFields as $key => $field){

          //Solo añadimos los campos de tipo data
         if(!isset($field["type"])||$field["type"]=="data"){
              $fieldNames=explode('_o_',$field["name"]);
              $searchValue=$request->query->get('columns');

              //Buscar el key del field["name"] en las columnas pasados por parametro
              $keyColumn=$this->searchColumns($searchValue, $field["name"]);
              if(!$keyColumn) continue;

              //TODO Campos mapeados como las coordenadas y demas petan aqui
      				$searchValue=trim($searchValue[$keyColumn]['search']['value']);
      				if($searchValue!="" && $searchValue!="##ALL##"){ //Si hay algo que buscar
                $database_field=null;
                $fieldNames=explode('_o_',$field["name"]); //explotamos los campos concatenados
                if(count($fieldNames)>1){ //Si hay que concatenar algo
                  $database_field='concat_ws(\' \',';
                  foreach($fieldNames as $fieldName){
                      $path=explode('__', $fieldName);  //explotamos las relaciones foraneas
                      if(count($path)>1){
                        $database_field.=$path[0].'.'.$path[1].',';  // si viene de otra tabla
                      }else{
                        $database_field.='p.'.$path[0].','; // si viene de la misma tabla
                      }
                  }
                  $database_field=rtrim($database_field,',');
                  $database_field.=')';  //cerramos el concat_ws
                }else{ //No hay nada que concatenar es un campo simple
                  $path=explode('__', $fieldNames[0]);
                  if(count($path)>1){
                    $database_field=$path[0].'.'.$path[1];
                  }else{
                    foreach($select_fields as $fieldas=>$as){
                      if ($field["name"]==$as)
                        $database_field=$fieldas;
                    }
                    if( $database_field==null)
                      $database_field='p.'.$field["name"];
                  }
                }

                $tokensSearchValue=explode('*',$searchValue);
                foreach($tokensSearchValue as $key=>$tokenSearch){
                  if($tokenSearch!=''){
                      if($tokenSearch=='##NULL##'){
                        $filter_where.=" AND ".$database_field." IS NULL";
                      }else{
                        if($tokenSearch[0]=='^'){ $starWildcard =''; $tokenSearch=substr($tokenSearch, 1);} else $starWildcard ='%';
                        if($tokenSearch[strlen($tokenSearch)-1]=='^'){ $endWildcard =''; $tokenSearch=substr($tokenSearch, 0, -1);}else $endWildcard ='%';
                        $filter_where.=" AND ".$database_field." LIKE '".$starWildcard.$tokenSearch.$endWildcard."'";
                      }
                  }
                }
      				}else{
                //No hay nada que buscar miramos si tiene un valor por defecto
                if($searchValue!="##ALL##" && isset($field["replace"])){
                  foreach($field["replace"] as $key=>$replace){
                    if(isset($replace["default"]) && $replace["default"]==true){
                      $filter_where.=" AND ".$field["name"]." = '".$key."'";
                    }
                  }
                }
              }
          }else{
            //If field is datetime type or date
            if($field["type"]=="datetime" || $field["type"]=="date"){
              $searchValue=$request->query->get('columns');
              $keyColumn=$this->searchColumns($searchValue, $field["name"]);
              if(!$keyColumn) continue;
              $searchValue=$searchValue[$keyColumn]['search']['value'];
              if($searchValue!=""){
                $searchValue=explode("#", $searchValue);
                $date_from=$searchValue[0];
                $date_to=isset($searchValue[1])?$searchValue[1]:"2999-12-30 23:59:59";
                $database_field_date = $field["name"];
                foreach($select_fields as $fieldas=>$as){
                  if ($database_field_date==$as)
                    $database_field_date=$fieldas;
                }
                if($date_from!=''){
                  if (strpos($sql_records, "p.".$database_field_date)!==false)
                    $filter_where.=" AND p.".$database_field_date." >= '".$date_from."'";
                  else
                    $filter_where.=" AND ".$database_field_date." >= '".$date_from."'";
                }
                if($date_to!=''){
                  if (strpos($sql_records, "p.".$database_field_date)!==false)
                    $filter_where.=" AND p.".$database_field_date." <= '".$date_to."'";
                  else
                    $filter_where.=" AND ".$database_field_date." <= '".$date_to."'";
                }
              }
            }
          }
  		}
      //++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      $sql_total.=' WHERE '.$where;
      $sql_filter.=' WHERE '.$filter_where;
      $sql_records.=' WHERE '.$filter_where;

      if ($groupBy){
        $sql_total.=' GROUP BY '.$groupBy;
        $sql_filter.=' GROUP BY '.$groupBy;
        $sql_records.=' GROUP BY '.$groupBy;
      }

      $sql_records.=' ORDER BY '.$order.' '.$orderDir;

      if($length!=-1) $sql_records.=' LIMIT '.$start.', '.$length;
      $result=$manager->getConnection()->executeQuery($sql_records)->fetchAll();
      $result_total=$manager->getConnection()->executeQuery($sql_total)->fetchAll();
      $result_filter=$manager->getConnection()->executeQuery($sql_filter)->fetchAll();

      $tags=array();
      if (count($result_total)>1)
        $return['recordsTotal']=count($result_total);
      else
        $return['recordsTotal']=$result_total[0]["total"];

      if (count($result_filter)>1)
        $return['recordsFiltered']=count($result_filter);
      else
        $return['recordsFiltered']=$result_filter[0]["total"];

      $return['data']=[];
      foreach($result as $key=>$row){
        $return['data'][]=$row;
      }
//dump(json_encode($sql_records));
      $return["_tags"]=$tags;
      $return = $this->clearData($return);
      return $return;
      }


    public function getRecords($user,$repository,$request,$manager,$listFields,$classname,$filters=[],$raw=[],$maxResults=null,$orderBy="id",$doctrine=null): array
    {
    $listName=$request->attributes->get('name');
    //$session = new Session();

		$return=array();
		$query = $repository->createQueryBuilder('p');

    if($maxResults===NULL){
        $query->setFirstResult($request->query->getInt('start', 0));
        if($request->query->getInt('length')==null) $query->setMaxResults($request->query->getInt('length', 50));
          else if($request->query->getInt('length')!==-1) $query->setMaxResults($request->query->getInt('length'));

        //$session->set('list'.$listName.'-start', $request->query->getInt('start'));
        //$session->set('list'.$listName.'-length', $request->query->getInt('length'));

    }else if($maxResults>=0){
      $query->setFirstResult($request->query->getInt('start', 0));
      $query->setMaxResults($maxResults);
      //$session->set('list'.$listName.'-length', $request->query->getInt('length', 20));
      //$session->set('list'.$listName.'-start', $request->query->getInt('start', 0));
    }


    //SESSION GLOBAL FILTERS CONTROLLER
    if($filters==[]){
      //$filters=$session->get('list'.$listName.'-filters',[]);
    }else if(count($filters)==1 && array_key_exists("company", $filters[0]) && $filters[0]["company"]==$user->getCompany()){
      //$filters=array_merge($session->get('list'.$listName.'-filters',[]),$filters);
      //$session->set('list'.$listName.'-filters', $filters);
    }else{
      //$session->set('list'.$listName.'-filters', $filters);
    }


    $queryFiltered = $repository->createQueryBuilder('p')->select('count(p.id)');
    $queryTotal = $repository->createQueryBuilder('p')->select('count(p.id)');

		//Formamos el filtro de busqueda global
		$searchValue=$request->query->get('search');
		$searchValue=$searchValue["value"];
		if($searchValue!=""){
        $metadata=$manager->getClassMetadata($classname);
		  	foreach($metadata->getColumnNames() as $column){
          if($column=='active') continue;
          $tokensSearchValue=explode('*',$searchValue);
          foreach($tokensSearchValue as $key=>$tokenSearch){
            if($tokenSearch!=''){
              //check if start with
              if($tokenSearch[0]=='^'){ $starWildcard =''; $tokenSearch=substr($tokenSearch, 1);} else $starWildcard ='%';
              if($tokenSearch[strlen($tokenSearch)-1]=='^'){ $endWildcard =''; $tokenSearch=substr($tokenSearch, 0, -1);}else $endWildcard ='%';
              $query->orWhere('p.'.$metadata->getFieldName($column).' LIKE :val_'.$metadata->getFieldName($column).'_'.$key);
              $query->setParameter('val_'.$metadata->getFieldName($column).'_'.$key, $starWildcard.$tokenSearch.$endWildcard);
              $queryFiltered->orWhere('p.'.$metadata->getFieldName($column).' LIKE :val_'.$metadata->getFieldName($column).'_'.$key);
              $queryFiltered->setParameter('val_'.$metadata->getFieldName($column).'_'.$key, $starWildcard.$tokenSearch.$endWildcard);
            }
          }
			  }
  			//Añadimos los campos de las relaciones
  			foreach($listFields as $field){
          //Quitamos los strings concatenados
          $fieldNames=explode('_o_',$field["name"]);
          $name='';
          foreach($fieldNames as $key=>$fieldName){
            if(!(strpos($fieldName, '\'')===0 && strpos($fieldName, '\'',1)==(strlen($fieldName)-1))){
              if($key==(count($fieldNames)-1)) $name.=$fieldName;
                else $name.=$fieldName.'_o_';
            }
            $field["name"]=$name;
          }


  				$path=explode('__', $field["name"]);

          //Solo buscamos en relaciones de 1 grado
          if(count($path)==2){
            $tokensSearchValue=explode('*',$searchValue);
            foreach($tokensSearchValue as $key=>$tokenSearch){
                  if($tokenSearch!=''){
                    if($tokenSearch[0]=='^'){ $starWildcard =''; $tokenSearch=substr($tokenSearch, 1);} else $starWildcard ='%';
                    if($tokenSearch[strlen($tokenSearch)-1]=='^'){ $endWildcard =''; $tokenSearch=substr($tokenSearch, 0, -1);}else $endWildcard ='%';
                    $query->orWhere($path[0].'.'.$path[1].' LIKE :val_'.$path[0].'_'.$path[1].'_'.$key);
          					$query->setParameter('val_'.$path[0].'_'.$path[1].'_'.$key, $starWildcard.$tokenSearch.$endWildcard);
          					$query->orWhere($path[0].'.'.$path[1].' LIKE :val_'.$path[0].'_'.$path[1].'_'.$key);
          					$query->setParameter('val_'.$path[0].'_'.$path[1].'_'.$key, $starWildcard.$tokenSearch.$endWildcard);
                  }
            }

  				}
  			}
		}

		//Formamos los filtros de busqueda por columna
		foreach($listFields as $key => $field){
        //Solo añadimos los campos de tipo data
       if(!isset($field["type"])||$field["type"]=="data"){
            $fieldNames=explode('_o_',$field["name"]);
            //Quitamos los strings concatenados
            $name='';
            foreach($fieldNames as $key=>$fieldName){
              if(!(strpos($fieldName, '\'')===0 && strpos($fieldName, '\'',1)==(strlen($fieldName)-1))){
                if($key==(count($fieldNames)-1)) $name.=$fieldName;
                  else $name.=$fieldName.'_o_';
              }
              $field["name"]=$name;
            }

    				$searchValue=$request->query->get('columns');
            //Buscar el key del field["name"] en las columnas pasados por parametro
            $keyColumn=$this->searchColumns($searchValue, $field["name"]);
            if(!$keyColumn) continue;

            //TODO Campos mapeados como las coordenadas y demas petan aqui
    				$searchValue=trim($searchValue[$keyColumn]['search']['value']);
    				if($searchValue!="" && $searchValue!="##ALL##"){ //Si hay algo que buscar
              $database_field=null;
              $fieldNames=explode('_o_',$field["name"]); //explotamos los campos concatenados
              if(count($fieldNames)>1){ //Si hay que concatenar algo
                $database_field='concat_ws(\' \',';
                foreach($fieldNames as $fieldName){
                    //Cadena fija de caracteres
                    if(strpos($fieldName, '\'')===0 && strpos($fieldName, '\'',1)==(strlen($fieldName)-1)){
                      $database_field.=$fieldName;
                    }else{
                      $path=explode('__', $fieldName);  //explotamos las relaciones foraneas
                      if(count($path)>1){
                        $database_field.=$path[0].'.'.$path[1].',';  // si viene de otra tabla
                      }else{
                        $database_field.='p.'.$path[0].','; // si viene de la misma tabla
                      }
                    }
                }
                $database_field=rtrim($database_field,',');
                $database_field.=')';  //cerramos el concat_ws
              }else{ //No hay nada que concatenar es un campo simple
                $path=explode('__', $fieldNames[0]);
                if(count($path)>1){
                  $database_field=$path[0].'.'.$path[1];
                }else{
                  $database_field='p.'.$field["name"];
                }
              }

              $tokensSearchValue=explode('*',$searchValue);
              foreach($tokensSearchValue as $key=>$tokenSearch){
                if($tokenSearch!=''){
                    if($tokenSearch=='##NULL##'){
                      $query->andWhere($database_field.' IS NULL');
                      $queryFiltered->andWhere($database_field.' IS NULL');
                    }else{
                      if($tokenSearch[0]=='^'){ $starWildcard =''; $tokenSearch=substr($tokenSearch, 1);} else $starWildcard ='%';
                      if($tokenSearch[strlen($tokenSearch)-1]=='^'){ $endWildcard =''; $tokenSearch=substr($tokenSearch, 0, -1);}else $endWildcard ='%';
                      $query->andWhere($database_field.' LIKE :val_'.$field["name"].'_'.$key);
                      $query->setParameter('val_'.$field["name"].'_'.$key, $starWildcard.$tokenSearch.$endWildcard);
                      $queryFiltered->andWhere($database_field.' LIKE :val_'.$field["name"].'_'.$key);
                      $queryFiltered->setParameter('val_'.$field["name"].'_'.$key, $starWildcard.$tokenSearch.$endWildcard);
                    }
                }
              }
    				}else{
              //No hay nada que buscar miramos si tiene un valor por defecto
              if($searchValue!="##ALL##" && isset($field["replace"])){
                foreach($field["replace"] as $key=>$replace){
                  if(isset($replace["default"]) && $replace["default"]==true){
                    $query->andWhere('p.'.$field["name"].' = :val_'.$field["name"]);
                    $query->setParameter('val_'.$field["name"], $key);
                    $queryFiltered->andWhere('p.'.$field["name"].' = :val_'.$field["name"]);
                    $queryFiltered->setParameter('val_'.$field["name"], $key);
                  }
                }
              }
            }
        }else{
          //If field is datetime type or date
          if($field["type"]=="datetime" || $field["type"]=="date"){
            $searchValue=$request->query->get('columns');
            $keyColumn=$this->searchColumns($searchValue, $field["name"]);
            if(!$keyColumn) continue;
            $searchValue=$searchValue[$keyColumn]['search']['value'];
            if($searchValue!=""){
              $searchValue=explode("#", $searchValue);
              $date_from=$searchValue[0];
              $date_to=isset($searchValue[1])?$searchValue[1]:"2999-12-30 23:59:59";
              if($date_from!=''){
                $query->andWhere('p.'.$field["name"].' >= :val_'.$field["name"].'_from');
    						$query->setParameter('val_'.$field["name"].'_from', $date_from);
    						$queryFiltered->andWhere('p.'.$field["name"].' >= :val_'.$field["name"].'_from');
    						$queryFiltered->setParameter('val_'.$field["name"].'_from', $date_from);
              }
              if($date_to!=''){
                $query->andWhere('p.'.$field["name"].' <= :val_'.$field["name"].'_to');
    						$query->setParameter('val_'.$field["name"].'_to', $date_to);
    						$queryFiltered->andWhere('p.'.$field["name"].' <= :val_'.$field["name"].'_to');
    						$queryFiltered->setParameter('val_'.$field["name"].'_to', $date_to);
              }
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
         $fields=explode('_o_', $listFields[(($order[0]['column'])-1)*1]["name"]);
         foreach($fields as $field){
           if(!(strpos($field, '\'')===0 && strpos($field, '\'',1)==(strlen($field)-1))){
        		 $path=explode('__', $field);
      			 if(count($path)>1){
      				$query->addOrderBy($path[0].'.'.$path[1], $order[0]['dir']);
      			 }else{
      				$query->addOrderBy('p.'.strtolower($field), $order[0]['dir']);
      			 }
           }
         }
		}else{
      $query->addOrderBy('p.'.$orderBy);
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
        $operator = '=';
        if (isset($filter['operator']) && $filter['operator']!=null && $filter['operator']!='')
          $operator = $filter['operator'];
        $query->andWhere($column.' '.$operator.' :val_'.$path[0].'0');
        $query->setParameter('val_'.$path[0].'0', $filter["value"]);
        $queryFiltered->andWhere($column.' '.$operator.' :val_'.$path[0].'0');
        $queryFiltered->setParameter('val_'.$path[0].'0', $filter["value"]);
        $queryTotal->andWhere($column.' '.$operator.' :val_'.$path[0].'0');
        $queryTotal->setParameter('val_'.$path[0].'0', $filter["value"]);
      }
    }
    $sql=$query->getQuery()->getSql();
    $params=$query->getQuery()->getParameters();
		$queryPaginator = $query->getQuery();
		$records=$queryPaginator->getResult();

		$records=$queryPaginator->getResult();
		$return=array();
		$return["recordsTotal"]=$queryTotal->getQuery()->getSingleScalarResult();
		$return["recordsFiltered"]=$queryFiltered->getQuery()->getSingleScalarResult();
		$return["data"]=array();

		//Obtenemos los datos desde la persistencia
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
                              $fieldNames=explode('_o_',$field["name"]);


                              $temp_val=null;
                              $name='';
                              //Quitamos los strings concatenados
                              foreach($fieldNames as $key=>$fieldName){
                                if(!(strpos($fieldName, '\'')===0 && strpos($fieldName, '\'',1)==(strlen($fieldName)-1))){
                                  if($key==(count($fieldNames)-1)) $name.=$fieldName;
                                    else $name.=$fieldName.'_o_';
                                }
                              }

                              foreach($fieldNames as $fieldName){
                                  $origin=$fieldName;
                                  $value='';
                                  $path=explode('__', $origin);
                                  $obj=$record;
                                  $obj_id=0;
                                  foreach($path as $step){
                                    if(method_exists($obj, "get".ucfirst($step))){
                                      $obj_id=$obj->getId();
                                      $obj=$obj->{"get".ucfirst($step)}($doctrine);

                                    }
                                  }
                                  if(!is_object($obj)) {$value= $obj;}
                                    else {
                                      if(get_class($obj)=="DateTime"){
                                        $value=$obj->format('Y-m-d H:i:s');
                                      }else $value='';
                                  }
                                  $temp_val=$value;
                                  if(strpos($name,'__')!==FALSE) $data_ob[$name."_id"]=$obj_id;
                                  //Aplicamos los replaces
                                  if(isset($field["replace"])){

                                      foreach($field["replace"] as $key=>$replace){
                                        if($temp_val==NULL) $temp_val=0;
                                        if(strval($temp_val)==strval($key)){
                                          $temp_val=array($temp_val,$replace["html"]);
                                          break;
                                        }
                                      }
                                  }
                                  if(strpos($fieldName, '\'')===0 && strpos($fieldName, '\'',1)==(strlen($fieldName)-1)){
                                    $temp_val=substr($fieldName,1,-1);
                                  }
                                  $data_ob[$name]=isset($data_ob[$name])?$data_ob[$name]." ".$temp_val:$temp_val;

                              }
                    break;
          }
			}
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
      //Fix for datatables error when single quote
      foreach($data_ob as $key_data=>$value_data){
        $data_ob[$key_data]=str_replace("'", "´", $data_ob[$key_data]);
      }
      $return["data"][]=$data_ob;
		}
    $return = $this->clearData($return);
		return $return;
    }

}

?>
