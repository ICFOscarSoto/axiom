<?php
namespace App\Modules\Globale\Utils;
use App\Modules\Globale\Entity\GlobaleCompanies;

class GlobaleListApiUtils
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

    public function getRecords($user,$repository,$request,$manager,$classname,$filters=[],$raw=[],$excludedFields=[],$includedFields=[]): array
    {
		$return=array();
		$query = $repository->createQueryBuilder('p');
    $queryFiltered = $repository->createQueryBuilder('p')->select('count(p.id)');
    $queryTotal = $repository->createQueryBuilder('p')->select('count(p.id)');
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
		}


    //Incluimos el parametro from para sincronizacion de dispositivos moviles
    //Ojo el parametro from debe ser un unixtimestamp
    $fromParam=$request->query->get('fromdate');
    if($fromParam!=NULL && is_numeric($fromParam)){
      $query->andWhere('p.dateupd > :val_dateupd');
      $query->setParameter('val_dateupd', date("Y-m-d H:i:s", $fromParam));
    }


		  //Dejamos los elementos borrados para informar a los dispositivos moviles
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
			 /*$order=$request->query->get('order');
  			 $path=explode('__', $listFields[(($order[0]['column'])-1)*1]["name"]);
			 if(count($path)>1){
				$query->addOrderBy($path[0].'.'.$path[1], $order[0]['dir']);
			 }else{
				$query->addOrderBy('p.'.strtolower($listFields[(($order[0]['column'])-1)*1]["name"]), $order[0]['dir']);
			}*/
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
		$queryPaginator = $query->getQuery();
		$records=$queryPaginator->getResult();
		$records=$queryPaginator->getResult();
		$return=array();
		$return["recordsTotal"]=$queryTotal->getQuery()->getSingleScalarResult();
		$return["recordsFiltered"]=$queryFiltered->getQuery()->getSingleScalarResult();
		$return["data"]=array();

		//Obtenemos los datos desde la persistencia
		foreach($records as $record){
			$data_ob=[];
			foreach(get_class_methods($classname) as $method){
        if(substr($method,0,3)=="get"){

          $value=$record->$method();
          if(is_a($value, "\DateTimeInterface")){
              $row[strtolower(substr($method,3))]=["date"=>date_timestamp_get($value),"offset"=>date_offset_get($value)];
          }else{
            if(is_object($value)){
                $row[strtolower(substr($method,3))]=$value->getId();
            }else $row[strtolower(substr($method,3))]=$value;

          }
          //}
        }

			}
      //DELETE EXCLUDED FIELDS
      if(in_array("deleted", $includedFields)){
        $excludedFields=array_merge($excludedFields,["company"]);
      }else $excludedFields=array_merge($excludedFields,["company","deleted"]);
      foreach($excludedFields as $excluded){
        if(isset($row[$excluded])) unset($row[$excluded]);
      }
			$return["data"][]=$row;
		}
		return $return;
    }
}

?>
