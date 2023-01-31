<?php
namespace App\Modules\Globale\Utils;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Globale\Utils\GlobaleJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\CallbackTransformer;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Globale\Entity\GlobaleUsers;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GlobaleFormUtils extends Controller
{
  private $ignoredAttributes=array('id','deleted','dateadd','dateupd');
  private $user;
  private $name;
  private $company;
  private $obj;
  private $obj_old;
  private $request;
  private $controller;
  private $doctrine;
  private $permissions;
  private $entityManager;
  private $excludedAttributes;
  private $includedAttributes;
  private $template;
  public $templateArray;
  private $transforms=array();
  private $form;
  private $values=array();
  private $routeParams=array();
  private $includePreTemplate=array();
  private $includePostTemplate=array();
  private $history;
  private $ajax=false;
  public $preParams=array();
  public $postParams=array();


  //OLD INIT, ONLY FOR COMPATIBILITY
  public function init($doctrine, $request){
    $this->doctrine=$doctrine;
    $this->request=$request;
    $this->entityManager=$this->doctrine->getManager();
  }

  public function initialize($user, $obj, $template, $request, $controller, $doctrine, $excludedAttributes=array(), $includedAttributes=array(), $encoder=null, $routeParams=[], $includePreTemplate=[], $includePostTemplate=[], $history=false){
    $this->user=$user;
    $this->obj=$obj;
    $this->obj_old=$obj;
    $this->request=$request;
    $this->controller=$controller;
    $this->doctrine=$doctrine;
    $this->excludedAttributes=array('company','author'); //by default remove company and author field from views
    $this->excludedAttributes=array_merge($this->excludedAttributes,$excludedAttributes);
    //$this->excludedAttributes=$excludedAttributes;
    $this->includedAttributes=$includedAttributes;
    $this->template=$template;
    $this->templateArray=json_decode(file_get_contents ($this->template),true);
    $this->entityManager=$this->doctrine->getManager();
    $this->encoder=$encoder;
    $this->routeParams=$routeParams;
    $this->includePostTemplate=$includePostTemplate;
    $this->includePreTemplate=$includePreTemplate;
    $this->history=$history;

    $this->permissions=$this->user->getTemplateData($this->controller->get('kernel'), $this->doctrine);
    //Set active by default in new objects
    //$this->setDefaults();

  }

  public function setDefaults(){
    if($this->obj->getId()===null ){
      if(method_exists($this->obj, 'setActive')) $this->obj->setActive(true);
      if(method_exists($this->obj, "setCountry")){
        $repository=$this->doctrine->getRepository("\App\Modules\Globale\Entity\GlobaleCountries");
        $default=$repository->findOneBy(['name'=>"España"]);
        $this->obj->setCountry($default);
      }
      if(method_exists($this->obj, "setCurrency")){
        $repository=$this->doctrine->getRepository("\App\Modules\Globale\Entity\GlobaleCurrencies");
        $default=$repository->findOneBy(['name'=>"Euro"]);
        $this->obj->setCurrency($default);
      }
      if(method_exists($this->obj, "setAgentassign")) $this->obj->setAgentassign($this->user);
    }
  }

/*NEW METHODS*/
 //name     = Name of the form
 //ajax     = if true form is considered an jquery form
 //id       = id of the entity element
 //class    = class of the entity element
 //route    = route of the save/read method in controller, generally dataEntity
  public function formatForm($name, $ajax=false, $id=null, $class=null, $route=null, $routeParams=[]){
    $this->ajax=$ajax;
    if(!$id){
				$this->obj=new $class();
			} else{
					$repository = $this->doctrine->getRepository($class);
					$this->obj=$repository->find($id);
					if($this->obj===NULL) $this->obj=new $class();

			}
    $this->obj_old=clone $this->obj;
    foreach($this->values as $key => $val){
       if(method_exists($this->obj,'set'.ucfirst($key))) $this->obj->{'set'.ucfirst($key)}($val);
    }
    $form=$this->createFromEntity2(!$ajax)->getForm();
    $caption=ucfirst($name);
    $routeParams=array_merge($routeParams, ["id"=>$id, "action"=>"save"]);
    $formView=$form->createView();
    //Aply convesion functions from controller to view
    $formView=$this->conversionView($formView);

    return ["id"=>$name, "id_object"=>!$this->obj->getId()?0:$this->obj->getId(), "name"=>$caption, "form" => $formView, "post"=>$this->controller->generateUrl(($route!=null)?$route:$this->request->get('_route'),$routeParams), "template" => json_decode(file_get_contents ($this->template))];
  }

  public function createFromEntity2($includeSave=true){
    $this->ignoredAttributes=array_merge($this->ignoredAttributes, $this->excludedAttributes);
    $class=get_class($this->obj);

    $form = $this->controller->createFormBuilder($this->obj);

    //Get class attributes
    foreach($this->entityManager->getClassMetadata($class)->fieldMappings as $key=>$value){
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){ //Check if field is not excluded and not ignored
        if($this->searchTemplateField($value['fieldName'])!==false){ //Check if field is in template, otherwise skip it
          //$readonly=false;
          if(isset($this->permissions["permissions"][$this->name."_field_".$value['fieldName']]) && $this->permissions["permissions"][$this->name."_field_".$value['fieldName']]['allowaccess']==false) {
            $readonly=true;
          }else $readonly=false;
          switch($value['type']){
            case 'datetime':
            case 'date':
            case 'time':
            case 'dateshort':
              //Check if has a template types
              $field=$this->searchTemplateField($value['fieldName']);
              $attr=[];
              if(isset($field["minDate"])) $attr["minDate"]=$field["minDate"];
              if(isset($field["maxDate"])) $attr["maxDate"]=$field["maxDate"];

              if(isset($field["readonly"])){
                if($field["readonly"]===true || $field["readonly"]===false) $readonly=$field["readonly"];
                 else if($field["readonly"]=="noChange"){
                   if($this->obj->{'get'.ucfirst($field["name"])}()==null){
                     $readonly=false;
                   }else $readonly=true;
                 }
              }

              if(isset($field["type"])){
                if($field["type"]=="date")
                  $form->add($value['fieldName'], DateType::class, ['label'=>(isset($field["caption"])?$field["caption"]:$field["name"]), 'disabled' => $readonly, 'required' => !$value["nullable"], 'empty_data' => '', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'attr' => array_merge(['autocomplete' => 'off', 'class' => 'datepicker' , 'defaultDate' => isset($field["defaultDate"])?$field["defaultDate"]:'' ],$attr)]);
                if($field["type"]=="time")
                  $form->add($value['fieldName'], TimeType::class, ['label'=>(isset($field["caption"])?$field["caption"]:$field["name"]), 'disabled' => $readonly, 'required' => !$value["nullable"], 'empty_data' => '', 'widget' => 'single_text', 'attr' => array_merge(['autocomplete' => 'off', 'class' => 'timepicker'],$attr)]);
              }else $form->add($value['fieldName'], DateTimeType::class, ['disabled' => $readonly, 'required' => !$value["nullable"], 'empty_data' => '', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy kk:mm:ss', 'attr' => array_merge(['autocomplete' => 'off', 'class' => 'datetimepicker', 'defaultDate' => isset($field["defaultDate"])?$field["defaultDate"]:'' ],$attr)]);
            break;
            case 'json':
              $form->add($value['fieldName'], TextType::class, ['label'=>(isset($field["caption"])?$field["caption"]:$field["name"]), 'required' => !$value["nullable"], 'attr'=>['autocomplete' => 'off', 'class' => 'tagsinput']]);
              $form->get($value['fieldName'])
                  ->addModelTransformer(new CallbackTransformer(
                      function ($tagsAsArray) {return implode(',', $tagsAsArray);},
                      function ($tagsAsString) {return explode(',', $tagsAsString);}
                  ));
            break;

            default://Default types ints, varchars, etc.
              //First of all check if exist a transform
            if($field=$this->searchTemplateField($value['fieldName'])){
              $label=ucfirst((isset($field["caption"])?$field["caption"]:$value['fieldName']).(isset($field['unit'])?' ('.$field['unit'].')':''));
              if(isset($field["transform"])){
                switch ($field["transform"]['type']){

                  case 'option':
                    $form->add($value['fieldName'], ChoiceType::class, ['label'=>$label,
                        'choices'  => $field["transform"]['options'],
                    ]);
                  break;
                  case 'button':
                    $form->add($field['name'], ButtonType::class, [
                        'attr' => ['autocomplete' => 'off', 'readonly' => $readonly, 'class' => $field["transform"]['class'].' '.isset($field["class"])?$field["class"]:''],
                    ]);
                  break;
                }
              }else{
                 //There isnt transformation, check types

                 if(isset($field["readonly"]) && $readonly==false){
                   if($field["readonly"]===true || $field["readonly"]===false) $readonly=$field["readonly"];
                    else if($field["readonly"]=="noChange"){
                      if($this->obj->{'get'.ucfirst($field["name"])}()==null){
                        $readonly=false;
                      }else $readonly=true;
                    }
                 }

                 if(isset($field["type"])){
                   switch ($field['type']){
                     case 'time':
                       $form->add($value['fieldName'], TextType::class, ['label'=>$label, 'disabled' => $readonly, 'required' => !$value["nullable"], 'attr' => ['autocomplete' => 'off', 'readonly' => $readonly, 'class' => 'timepicker']]);
                     break;
                     case "dateshort":
                         $form->add($value['fieldName'], TextType::class, ['disabled' => $readonly, 'required' => !$value["nullable"], 'empty_data' => '', 'attr' => ['autocomplete' => 'off', 'readonly' => $readonly, 'class' => 'dateshortpicker', 'defaultDate' => isset($field["defaultDate"])?$field["defaultDate"]:'' ]]);
                     break;
                     default:
                      $form->add($value['fieldName'],null,['label'=>$label, 'disabled' => $readonly, 'attr'=>['autocomplete' => 'off', 'readonly' => $readonly,'class'=>isset($field["class"])?$field["class"]:'']]);
                   }
                  if ($field['type']=='searchable')
                    $form->add($value['fieldName'].'_id', HiddenType::class, ['mapped'=>false, 'required' => isset($field["nullable"])?!$field["nullable"]:'false', 'attr'=>['attr-attribute' => $value['fieldName'], 'class' => '']]);
                 }else{
                   $form->add($value['fieldName'],null,['label'=>$label, 'disabled' => $readonly, 'attr'=>['autocomplete' => 'off', 'readonly' => $readonly,'class'=>isset($field["class"])?$field["class"]:'']]);
                 }
              }
            }else $form->add($value['fieldName'],null,['disabled' => $readonly,'attr'=>['autocomplete' => 'off', 'readonly' => $readonly,'class'=>isset($field["class"])?$field["class"]:'']]);
            break;
          }
        }
      }
    }
    //Add included attributes
    foreach ($this->includedAttributes as $key => $value) {
      $form->add($value[0], $value[1], $value[2]);
      $field=$this->searchTemplateField($value[0]);
      if ($field && isset($field['type']) && $field['type']=='searchable')
        $form->add($value[0].'_id', HiddenType::class, ['mapped'=>false, 'required' => isset($field["nullable"])?!$field["nullable"]:'false', 'attr'=>['attr-attribute' => $value[0], 'class' => ''], 'data' =>(isset($value[2]['attr']['data_id'])?$value[2]['attr']['data_id']:null)]);
    }
    //Get class relations
    foreach($this->entityManager->getClassMetadata($class)->associationMappings as $key=>$value){
      if(!isset($value["joinColumns"])) continue;
      //check if is required field
      if(isset($value["joinColumns"][0]["nullable"]) && $value["joinColumns"][0]["nullable"] == false) $nullable=false; else $nullable=true;
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){
        $field=$this->searchTemplateField($value['fieldName']);
        //Check if view Entity (relation) is present
        $route=null;
        $routeType=null;
        if(isset($field["relation"]["route"])){
            //Check if user has permissions for this route
            $userRepository=$this->doctrine->getRepository(GlobaleUsers::class);
            if($userRepository->hasPermission($this->user->getId(),$field["relation"]["route"])){
              $route=true;
              $routeType=isset($field["relation"]["type"])?$field["relation"]["type"]:'full';
            }
        }
        if(!isset($field["trigger"])) {//If no trigger element, fill it
          if(!isset($field['type']) || $field['type']!="searchable")
            $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelation($field, $value['fieldName'], $value["targetEntity"], $this->obj->{'get'.ucfirst($value["fieldName"])}(),$nullable, $route, $routeType));
          else{
             //2021-11-26 - Added for searchables relationship fields
            $form->add($value['fieldName'], TextType::class, ['mapped'=>false, 'label'=>(isset($field["caption"])?$field["caption"]:ucfirst($field["name"])), 'required' => isset($field["nullable"])?!$field["nullable"]:'false', 'attr'=>['autocomplete' => 'off', 'readonly' =>true, 'class' => 'searchable-field']]);
            $form->add($value['fieldName'].'_id', HiddenType::class, ['mapped'=>false, 'required' => isset($field["nullable"])?!$field["nullable"]:'false', 'attr'=>['attr-attribute' => $value['fieldName'], 'class' => '']]);
          }
        }else{
          //$form->add($value['fieldName'], TextType::class, ["attr"=>["attr-module"=>$field["module"],"attr-name"=>$field["nameClass"]]]);
          $generic = true;
          if(isset($field["trigger"]['generic']))
            $generic = $field["trigger"]['generic'];
          $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelationTrigger($field, $value['fieldName'],$nullable, $route, $routeType,$generic));
        }
      }
    }
    if($includeSave) $form->add('save', SubmitType::class, ['attr' => ['class' => 'save'],]);

    $this->form=$form;
    return $form;
  }

  public function searchTemplateField($field){
    foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
      foreach ($valueSection['fields'] as $keyField => $valueField) {
        if($valueField['name']==$field){
          return $valueField;
        }
      }
    }
    return false;
  }

  public function values($values=array()){
    $this->values=$values;
  }

  public function make($id, $class, $action, $name, $type="full", $render="@Globale/form.html.twig", $returnRoute=null, $utilsClass=null, $includesArray=[], $custom_vars=[]){
    $this->name=$name;
     if(!($this->obj instanceof $class)){
				$this->obj=new $class();
			} else{
					$repository = $this->doctrine->getRepository($class);
          //Security separation of companies
          if($id!==null && $id!==0){ //If obj hasn't ID we asume that the obj is initzializated by us in the controller for set default values
            if(property_exists($class,'company')){
              $this->obj=$repository->findOneBy(['id'=>$id, 'company'=>$this->user->getCompany(), 'deleted'=>0]);

            }else{
              $this->obj=$repository->findOneBy(['id'=>$id, 'deleted'=>0]);
            }
          }

					if($this->obj===NULL) $this->obj=new $class();
			}
      $this->setDefaults();
      $this->obj_old=clone $this->obj;

      $builder = $this->createFromEntity2(false);
      $builder->addEventListener('form.bind', function ($event) {
        $form = $event->getForm();
        foreach($form->all() as $key => $val){
          $config = $val->getConfig();
          $attributes = $config->getAttributes();
          if (isset($attributes['data_collector/passed_options']) && isset($attributes['data_collector/passed_options']['attr'])
           && isset($attributes['data_collector/passed_options']['attr']['ajax']) && $attributes['data_collector/passed_options']['attr']['ajax'])
           $form->add($key, ChoiceType::class, ['choices'=>null,'attr'=>$attributes['data_collector/passed_options']['attr']]);
        }
      });

      $form=$builder->getForm();

			switch($action){
				 case 'save':
           //Buscar si existe un proccess dentro del utils de la clase
           $validation=[];
           if($utilsClass!=null && method_exists($utilsClass, 'proccess')){
             $utils=new $utilsClass();
					   $this->obj=$utils->proccess($form,$this->user,$this->obj,$this->request,$this->entityManager,$this->encoder,$this->doctrine);
           }else{
           //Si no, ejecutamos el process estandar del formutils
             $proccess=$this->proccess2($form,$this->obj);
             $validation=$proccess["validation"];
					   $this->obj=$proccess["obj"];
           }
           if(is_bool($this->obj)) $result=false;
            else{
              if ($this->obj->getId()!==FALSE) $result=true;
              $routeParams=array_merge($this->routeParams, ["id"=>$this->obj->getId()]);
            }
					 //$result=((!is_bool($this->obj) && $this->obj->getId()!==FALSE)?true:false);
           if($returnRoute==null)$returnRoute=$this->request->get('_route');
           //$routeParams=array_merge($this->routeParams, ["id"=>$this->obj->getId()]);
           $route=$result?(($id!=$this->obj->getId())? $returnRoute=='none'?'':$this->controller->generateUrl($returnRoute,$routeParams) :''):'';
					 return new GlobaleJsonResponse(array('result' => $result, 'validation'=>$validation, 'href' =>$route, 'reload' =>$result?(($id!=$this->obj->getId())?($returnRoute=='none'?false:true):false):'', 'id' => $result?$this->obj->getId():''));
           //return array('result' => $result, 'href' =>$route, 'reload' =>$result?(($id!=$this->obj->getId())?($returnRoute=='none'?false:true):false):'', 'id' => $result?$this->obj->getId():'');
				 break;
				 case 'read':
             $routeParams=array_merge($this->routeParams, ["id"=>$id, "action"=>"save"]);
             $route=$this->controller->generateUrl($this->request->get('_route'),$routeParams);
             $formView=$form->createView();
             //Aply convesion functions from controller to view
             $formView=$this->conversionView($formView);
             $formView=$this->conversionSearchable($formView); //2021-11-26 - Added for searchables relationship fields
             $searchables=$this->getSearchables($form); //2021-11-26 - Added for searchables relationship fields

						 return $this->controller->render($render, array_merge(array(
              'id' => $id,
              "userData"=>$this->permissions,
              'includes' => $includesArray,
              'include_pre_templates' => $this->includePreTemplate,
              'include_post_templates' => $this->includePostTemplate,
              'searchables' => $searchables,
              'formConstructor' => ["id"=>$id, "id_object"=>$id, "name"=>$name, "form" => $formView, "type" => $type, "post"=>$route, "template" => json_decode(file_get_contents ($this->template),true)]
            ),$custom_vars));
				break;
			}
  }

  private function conversionView($formView){
    foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
      foreach ($valueSection['fields'] as $keyField => $valueField) {
        if(isset($valueField['conversion']["view"])){
          if(method_exists(get_class($formView->vars["value"]), $valueField['conversion']["view"])){
            $formView->children[$valueField["name"]]->vars["value"]=$formView->vars["value"]->{$valueField['conversion']["view"]}($formView->vars["value"]->{'get'.ucfirst($valueField["name"])}());
            $formView->vars["value"]->{'set'.ucfirst($valueField["name"])}($formView->vars["value"]->{$valueField['conversion']["view"]}($formView->vars["value"]->{'get'.ucfirst($valueField["name"])}()));
          }
        }
      }
    }
    return $formView;
  }

  //2021-11-26 - Added for searchables relationship fields
  private function conversionSearchable($formView){
    foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
      foreach ($valueSection['fields'] as $keyField => $valueField) {
        if(isset($valueField['type']) && $valueField['type']=='searchable'){
            $class="\App\Modules\\".$valueField['typeParams']["module"]."\Entity\\".$valueField['typeParams']["module"].$valueField['typeParams']["name"];
            $obj = $formView->vars["value"];
            $method = 'get'.ucfirst($valueField["name"]);
            $value="";
            if (isset($valueField['parent']) && $valueField['parent']!=null && $valueField['parent']!='' &&
                method_exists($obj,'get'.ucfirst($valueField["parent"])))
              $obj = $obj->{'get'.ucfirst($valueField["parent"])}();

            if(method_exists($class, 'getCode') && method_exists($obj,$method) && $obj->{$method}()!==null) $value.='('.$obj->{$method}()->getCode().') ';
            if(method_exists($class, 'getName') && method_exists($obj,$method) && $obj->{$method}()!==null) $value=$value.$obj->{$method}()->getName();
            if(method_exists($class, 'getLastname') && method_exists($obj,$method) && $obj->{$method}()!==null) $value=$value.' '.$obj->{$method}()->getLastname();

            $formView->children[$valueField["name"]]->vars["value"]=$value;
            if(method_exists($obj,$method) && $obj->{$method}()!==null)
              $formView->children[$valueField["name"].'_id']->vars["value"]=$obj->{$method}()->getId();
/*            else
              $formView->children[$valueField["name"].'_id']->vars["value"]='';*/
        }
      }
    }
    return $formView;
  }

  private function getSearchables($form){
    $searchables=[];
    foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
      foreach ($valueSection['fields'] as $keyField => $valueField) {
        $searchable=[];
        if(isset($valueField['type']) && $valueField['type']=='searchable'){
          //if($form->getIterator()->offsetExists($valueField["name"])){
            $class="\App\Modules\\".$valueField['typeParams']["module"]."\Entity\\".$valueField['typeParams']["module"].$valueField['typeParams']["name"];
            $fields=[["name" =>"id", "caption" =>""]];
            $cols=0;
            if(method_exists($class,'getCode')){ $fields[]=["name" =>"code", "caption" =>""]; $cols++;}
            if(method_exists($class,'getLastname')){ $fields[]=["name" =>"name_o_lastname", "caption" =>""]; $cols++;}else if(method_exists($class,'getName')){ $fields[]=["name" =>"name", "caption" =>""]; $cols++;}
            if(method_exists($class,'getSocialname')){ $fields[]=["name" =>"socialname", "caption" =>""]; $cols++;}
            if(method_exists($class,'getEmail') && $cols<3){ $fields[]=["name" =>"email", "caption" =>""]; $cols++;}
            $list=[
              'id' => 'listSearch'.$valueField["name"],
              'route' => 'genericlist',
              'routeParams' => $valueField["typeParams"],
              'orderColumn' => 1,
              'orderDirection' => 'ASC',
              'tagColumn' => 1,
              'fields' => $fields,
              'fieldButtons' => [["id"=>"select", "type" => "success", "default"=>true, "icon" => "fas fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]],
              'topButtons' => []
            ];
          //}
          $searchables[$valueField["name"]]=$list;
        }
      }
    }
    return $searchables;
  }

 private function conversionController($obj){
   foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
     foreach ($valueSection['fields'] as $keyField => $valueField) {
       if(isset($valueField['conversion']["controller"]) && (!isset($valueField['readonly']) || $valueField['readonly']==false )){
         if(method_exists(get_class($obj), $valueField['conversion']["controller"])){
           $obj->{'set'.ucfirst($valueField["name"])}($obj->{$valueField['conversion']["controller"]}($obj->{'get'.ucfirst($valueField["name"])}()));
         }
       }
     }
   }
   return $obj;

 }

 public function proccess2($form,$obj){
    $form->handleRequest($this->request);
    $validation=["valid"=>true];
    if(!$form->isSubmitted()) return false;
    if ($form->isSubmitted() && $form->isValid()) {
       $obj = $form->getData();
       //Aply convesion functions from view to controller
       $obj = $this->conversionController($obj);
       //definimos los valores predefinidos
       foreach($this->values as $key => $val){
            if(method_exists($obj,'set'.ucfirst($key))) $obj->{'set'.ucfirst($key)}($val);
       }

       foreach($form->getIterator()->getIterator() as $key => $val){   //2021-11-26 - Added for searchables relationship fields
         $parentField = null;
         $childrenId = null;
         if(strpos($key,'_id')!==false){
           $parentField=str_replace('_id','',$key);
           $childrenId = $form->getIterator()->offsetGet($key)->getData();
         }else{
           $config = $val->getConfig();
           $attributes = $config->getAttributes();
           if (isset($attributes['data_collector/passed_options']) && isset($attributes['data_collector/passed_options']['attr'])
            && isset($attributes['data_collector/passed_options']['attr']['ajax']) && $attributes['data_collector/passed_options']['attr']['ajax']){
              $parentField=$key;
              $childrenId = $this->request->request->get("form")[$key];
           }
         }
         if ($parentField && method_exists($obj,'set'.ucfirst($parentField))){
           $targetEntity=$this->entityManager->getClassMetadata($form->getConfig()->getDataClass())->associationMappings[$parentField]['targetEntity'];
           $relationRepository=$this->doctrine->getRepository($targetEntity);
           $relationObj=$relationRepository->findOneBy(['id'=>$childrenId]);
           $obj->{'set'.ucfirst($parentField)}($relationObj);
         }
       }

       if($obj->getId() == null){
         $obj->setDateadd(new \DateTime());
         $obj->setDeleted(false);
         //If object has Company save with de user Company
         if(method_exists($obj,'setCompany')) $obj->setCompany($this->user->getCompany());
         if(method_exists($obj,'setAuthor')) $obj->setAuthor($this->user);
       }
       $obj->setDateupd(new \DateTime());
       try{
         //if object has a validation check it
         if(method_exists($obj,'formValidation')) $validation=$obj->{'formValidation'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->preParams);
         if($validation["valid"]==false) //Abort proccess
          return ["obj"=>false, "validation"=>$validation];

         //if object has a preproccess run it
         if(method_exists($obj,'preProccess')) $obj->{'preProccess'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->preParams, $this->obj_old);
         $this->entityManager->persist($obj);
         $this->entityManager->flush();
         if(method_exists($obj,'postProccess')) $obj->{'postProccess'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->postParams, $this->obj_old);
         $this->detectObjChanges($this->obj_old, $obj);
         return ["obj"=>$obj, "validation"=>$validation];
       }catch (Exception $e) {
         return ["obj"=>false, "validation"=>$validation];
       }
    }else return ["obj"=>false, "validation"=>$validation];
  }

  public function detectObjChanges($old, $new){
    if(!$this->history) return;
    if(!$old->getId()) return;
    //Compare obj new with old and save history
    $this->ignoredAttributes=array_merge($this->ignoredAttributes, $this->excludedAttributes);
    $class=get_class($old);
    $changes=[];
    foreach($this->entityManager->getClassMetadata($class)->fieldMappings as $key=>$value){ //Get simple attrubutes
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){ //Check if field is not excluded and not ignored
        if($this->searchTemplateField($value['fieldName'])!==false){ //Check if field is in template, only compare visible fields
          switch($value['type']){
            case 'datetime':
            if(method_exists($old,'get'.ucfirst($value['fieldName']))){
              if(($old->{'get'.ucfirst($value['fieldName'])}()!=NULL?$old->{'get'.ucfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL) != ($new->{'get'.ucfirst($value['fieldName'])}()!=NULL?$new->{'get'.ucfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL)){
                //Attribute changed store it
                $changes[]=["attribute"=>$value['fieldName'],"oldvalue"=>($old->{'get'.ucfirst($value['fieldName'])}()!=NULL?$old->{'get'.ucfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL),"newvalue"=>($new->{'get'.ucfirst($value['fieldName'])}()!=NULL?$new->{'get'.ucfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL)];
              }
            }
            break;
            default:
              if(method_exists($old,'get'.ucfirst($value['fieldName']))){
                if($old->{'get'.ucfirst($value['fieldName'])}() != $new->{'get'.ucfirst($value['fieldName'])}()){
                  //Attribute changed store it
                  $changes[]=["attribute"=>$value['fieldName'],"oldvalue"=>$old->{'get'.ucfirst($value['fieldName'])}(), "newvalue"=>$new->{'get'.ucfirst($value['fieldName'])}()];
                }
              }
            break;
          }
        }
      }
    }
    foreach($this->entityManager->getClassMetadata($class)->associationMappings as $key=>$value){ //Get relations
        //TODO: Exclude not showed relations and define how detect changes with relations
    }
    //TODO: Detect if the same user change other field in this object in last 10 minutes, if yes, merge it
    $history=new GlobaleHistories();
    $history->setEntity($class);
    $history->setEntityId($old->getId());
    $history->setCompany($this->user->getCompany());
    $history->setUser($this->user);
    $history->setDateadd(new \DateTime());
    $history->setDateupd(new \DateTime());
    $history->setChanges(json_encode($changes));
    $history->setActive(TRUE);
    $history->setDeleted(FALSE);

    $this->entityManager->persist($history);
    $this->entityManager->flush();

  }

  public function choiceRelation($field, $name, $class, $data, $nullable, $route=null, $routeType=null){
    $classname=explode('\\', $class);
    //If class has attribute company apply filter
    if(property_exists($class,'company')){
      $choices= (!$this->ajax || $this->obj->getId())?$this->doctrine->getRepository($class)->findBy(['company'=>$this->user->getCompany(),'active'=>true, 'deleted'=>false]):null;
    }else{
      if(property_exists($class,'active')){
        $choices= (!$this->ajax || $this->obj->getId())?$this->doctrine->getRepository($class)->findBy(['active'=>true, 'deleted'=>false]):null;
      }else $choices= (!$this->ajax || $this->obj->getId())?$this->doctrine->getRepository($class)->findAll():null;
    }

    if(isset($this->permissions["permissions"][$this->name."_field_".$name]) && $this->permissions["permissions"][$this->name."_field_".$name]['allowaccess']==false) {
      $readonly=true;
    }else $readonly=false;

    $result =  [
                  'attr' => ['class' => 'select2', 'disabled' => $readonly, 'attr-target' => $route, 'attr-target-type' => $routeType],
                  'required' => !$nullable,
                  'choices' => $choices,
                  //if class has attribute lastName concat it with name
                  'choice_label' => function($obj, $key, $index) {
                      if(method_exists($obj, "getLastname")){

                        return (method_exists($obj, "getCode")?('('.$obj->getCode().') - '):'').$obj->getLastname().", ".$obj->getName();
                      }else{
                         if(method_exists($obj, "getName")){
                           return (method_exists($obj, "getCode")?('('.$obj->getCode().') - '):'').$obj->getName();
                         }else{
                           if(method_exists($obj, "getCode")) return $obj->getCode();
                         }
                      }



                      return '';
                  },
                  'choice_attr' => function($obj, $key, $index) {
                      return ['class' => $obj->getId()];
                  },
                  'choice_value' => function ($obj) {
                      return $obj ? $obj->getId() : '';
                  },
                  'expanded' => false,
                  'data' =>$data,
                  'placeholder' => 'Select '.strtolower(end($classname))
              ];

    return $result;
  }


public function choiceRelationTrigger($field, $name, $nullable, $route=null, $routeType=null,$generic=true){
  $form=$this->request->request->get('form', null);
  $class="\App\Modules\\".$field["trigger"]["module"]."\\Entity\\".$field["trigger"]["class"];
  $choices=[];

  if ($generic){
    $classTrigger="\App\Modules\\".$field["trigger"]["moduleTrigger"]."\\Entity\\".$field["trigger"]["classTrigger"];
    $filter=[];
    if($form!=null){
      //select options of the trigger value selected
      //Check options for trigger FIELDS
      $triggerValue=$this->doctrine->getRepository($classTrigger)->findOneBy(['id'=>$form[$field["trigger"]["field"]]]);
      $filter=[$field["trigger"]["relationParameter"]=>$triggerValue];
    }else{
      //get selected option or null options if not set
      if($this->obj!=null) {
        $triggerValue=$this->doctrine->getRepository($classTrigger)->findOneBy(['id'=>$this->obj->{'get'.ucfirst($field["trigger"]["relationParameter"])}()]);
        $filter=[$field["trigger"]["relationParameter"]=>$triggerValue];
      }else{
        $filter=["id"=>0];
      }
    }
    if(property_exists($class,'company')){ //If class has attribute company apply filter
      $choices=$this->doctrine->getRepository($class)->findBy(array_merge($filter,['company'=>$this->user->getCompany(),'active'=>true, 'deleted'=>false]));
    }else{
      if(property_exists($class,'active')){
        $choices= $this->doctrine->getRepository($class)->findBy(array_merge($filter,['active'=>true, 'deleted'=>false]));
      }else $choices= $this->doctrine->getRepository($class)->findAll();
    }
  }else{
    $value = isset($form[$field["trigger"]["field"]])?$form[$field["trigger"]["field"]]:0;
    if ($value==0){
      $relationParameter = $field["trigger"]["relationParameter"];
      if ($relationParameter){
        $arelationParameter = explode('__',$relationParameter);
        $relation = $this->obj;
        $error = false;
        for($i=0; $i<count($arelationParameter) && !$error; $i++){
          if (method_exists($relation, 'get'.ucfirst($arelationParameter[$i])))
            $relation = $relation->{'get'.ucfirst($arelationParameter[$i])}();
          else
            $error=true;
        }
        if (!$error)
          $value = $relation;
      }
    }

    $params = http_build_query(["id"=>$value]);
    $opts = ["http" => [
                          "method" => "POST",
                          "header" => "Content-Type: application/x-www-form-urlencoded",
                          "content" => $params
                        ]
                      ];
    $context = stream_context_create($opts);
    $result = file_get_contents($this->controller->generateUrl($field["trigger"]["route"],[], UrlGeneratorInterface::ABSOLUTE_URL),false,$context);
    $result = json_decode($result, true);
    foreach ($result as $key => $value) {
      $choices[] = $this->doctrine->getRepository($class)->find($value['id']);
    }
  }

  if(isset($this->permissions["permissions"][$this->name."_field_".$name]) && $this->permissions["permissions"][$this->name."_field_".$name]['allowaccess']==false) {
    $readonly=true;
  }else $readonly=false;

  $result =  [
                'attr' => ['class' => 'select2', 'disabled' => $readonly, 'attr-target' => $route, 'attr-target-type' => $routeType],
                'required' => !$nullable,
                'choices' => $choices,
                //if class has attribute lastName concat it with name
                'choice_label' => function($obj, $key, $index) {
                    if(method_exists($obj, "getLastname"))
                      return $obj->getLastname().", ".$obj->getName();
                    else return $obj->getName();
                },
                'choice_attr' => function($obj, $key, $index) {
                    return ['class' => $obj->getId()];
                },
                'choice_value' => function ($obj) {
                    return $obj ? $obj->getId() : '';
                },
                'expanded' => false,
                'placeholder' => 'Select '.$field["trigger"]["class"]
            ];

  return $result;
}

}
