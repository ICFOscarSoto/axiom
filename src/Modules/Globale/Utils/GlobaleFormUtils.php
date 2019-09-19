<?php
namespace App\Modules\Globale\Utils;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Globale\Utils\GlobaleJsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

class GlobaleFormUtils extends Controller
{
  private $ignoredAttributes=array('id','deleted','dateadd','dateupd');
  private $user;
  private $company;
  private $obj;
  private $obj_old;
  private $request;
  private $controller;
  private $doctrine;
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
    $this->excludedAttributes=array('company'); //by default remove company field from views
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
    //Set active by default in new objects
    if($obj->getId()===null && method_exists($obj, 'setActive')){
      $obj->setActive(true);
    }
  }

/*NEW METHODS*/
 //name     = Name of the form
 //ajax     = if true form is considered an jquery form
 //id       = id of the entity element
 //class    = class of the entity element
 //route    = route of the save/read method in controller, generally dataEntity
  public function formatForm($name, $ajax=false, $id=null, $class=null, $route=null, $routeParams=[]){
    if(!$id){
				$this->obj=new $class();
			} else{
					$repository = $this->doctrine->getRepository($class);
					$this->obj=$repository->find($id);
					if($this->obj===NULL) $this->obj=new $class();

			}
    $this->obj_old=clone $this->obj;
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

              if(isset($field["type"])){
                if($field["type"]=="date")
                  $form->add($value['fieldName'], DateType::class, ['required' => !$value["nullable"], 'empty_data' => '', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'attr' => array_merge(['class' => 'datepicker' , 'defaultDate' => isset($field["defaultDate"])?$field["defaultDate"]:'' ],$attr)]);
                if($field["type"]=="time")
                  $form->add($value['fieldName'], TimeType::class, ['required' => !$value["nullable"], 'empty_data' => '', 'widget' => 'single_text', 'attr' => array_merge(['class' => 'timepicker'],$attr)]);
              }else $form->add($value['fieldName'], DateTimeType::class, ['required' => !$value["nullable"], 'empty_data' => '', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy kk:mm:ss', 'attr' => array_merge(['class' => 'datetimepicker', 'defaultDate' => isset($field["defaultDate"])?$field["defaultDate"]:'' ],$attr)]);
            break;
            case 'json':
              $form->add($value['fieldName'], TextType::class, ['required' => !$value["nullable"], 'attr'=>['class' => 'tagsinput']]);
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
                        'attr' => ['class' => $field["transform"]['class'].' '.isset($field["class"])?$field["class"]:''],
                    ]);
                  break;
                }
              }else{
                 //There isnt transformation, check types
                 if(isset($field["type"])){
                   switch ($field['type']){
                     case 'time':
                       $form->add($value['fieldName'], TextType::class, ['label'=>$label, 'required' => !$value["nullable"], 'attr' => ['class' => 'timepicker']]);
                     break;
                     case "dateshort":
                         $form->add($value['fieldName'], TextType::class, ['required' => !$value["nullable"], 'empty_data' => '', 'attr' => ['class' => 'dateshortpicker', 'defaultDate' => isset($field["defaultDate"])?$field["defaultDate"]:'' ]]);
                     break;
                   }
                 }else{
                   $form->add($value['fieldName'],null,['label'=>$label, 'disabled' => isset($field["readonly"])?$field["readonly"]:false, 'attr'=>['help'=>"prueba",'readonly' => isset($field["readonly"])?$field["readonly"]:false,'class'=>isset($field["class"])?$field["class"]:'']]);
                 }
              }
            }else $form->add($value['fieldName'],null,['disabled' => isset($field["readonly"])?$field["readonly"]:false,'attr'=>['readonly' => isset($field["readonly"])?$field["readonly"]:false,'class'=>isset($field["class"])?$field["class"]:'']]);
            break;
          }
        }
      }
    }
    //Add included attributes
    foreach ($this->includedAttributes as $key => $value) {
      $form->add($value[0], $value[1], $value[2]);
    }
    //Get class relations
    foreach($this->entityManager->getClassMetadata($class)->associationMappings as $key=>$value){
      if(!isset($value["joinColumns"])) continue;
      //check if is required field
      if(isset($value["joinColumns"][0]["nullable"]) && $value["joinColumns"][0]["nullable"] == false) $nullable=false; else $nullable=true;
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){
        $field=$this->searchTemplateField($value['fieldName']);
        if(!isset($field["trigger"])) {//If no trigger element, fill it
          $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelation($value["targetEntity"], $this->obj->{'get'.ucfirst($value["fieldName"])}(),$nullable));
        }else{
          //$form->add($value['fieldName'], TextType::class, ["attr"=>["attr-module"=>$field["module"],"attr-name"=>$field["nameClass"]]]);
          $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelationTrigger($field,$nullable));
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

  public function make($id, $class, $action, $name, $type="full", $render="@Globale/form.html.twig", $returnRoute=null, $utilsClass=null, $includesArray=[]){
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
      $this->obj_old=clone $this->obj;
			$form=$this->createFromEntity2(false)->getForm();
			switch($action){
				 case 'save':
           //Buscar si existe un proccess dentro del utils de la clase
           $validation=[];
           if($utilsClass!=null && method_exists($utilsClass, 'proccess')){
             $utils=new $utilsClass();
					   $this->obj=$utils->proccess($form,$this->user,$this->obj,$this->request,$this->entityManager,$this->encoder);
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
						 return $this->controller->render($render, array(
              'includes' => $includesArray,
              'include_pre_templates' => $this->includePreTemplate,
              'include_post_templates' => $this->includePostTemplate,
              'formConstructor' => ["id"=>$id, "id_object"=>$id, "name"=>$name, "form" => $formView, "type" => $type, "post"=>$route, "template" => json_decode(file_get_contents ($this->template),true)]
              ));
				break;
			}
  }

  private function conversionView($formView){
    foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
      foreach ($valueSection['fields'] as $keyField => $valueField) {
        if(isset($valueField['conversion']["view"])){
          if(method_exists(get_class($formView->vars["value"]), $valueField['conversion']["view"])){
            $formView->children[$valueField["name"]]->vars["value"]=$formView->vars["value"]->{$valueField['conversion']["view"]}($formView->vars["value"]->{'get'.lcfirst($valueField["name"])}());
            $formView->vars["value"]->{'set'.lcfirst($valueField["name"])}($formView->vars["value"]->{$valueField['conversion']["view"]}($formView->vars["value"]->{'get'.lcfirst($valueField["name"])}()));
          }
        }
      }
    }
    return $formView;
  }

 private function conversionController($obj){
   foreach ($this->templateArray[0]['sections'] as $keySection => $valueSection) {
     foreach ($valueSection['fields'] as $keyField => $valueField) {
       if(isset($valueField['conversion']["controller"]) && (!isset($valueField['readonly']) || $valueField['readonly']==false )){
         if(method_exists(get_class($obj), $valueField['conversion']["controller"])){
           $obj->{'set'.lcfirst($valueField["name"])}($obj->{$valueField['conversion']["controller"]}($obj->{'get'.lcfirst($valueField["name"])}()));
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
          if(method_exists($obj,'set'.lcfirst($key))) $obj->{'set'.lcfirst($key)}($val);
       }
       if($obj->getId() == null){
         $obj->setDateadd(new \DateTime());
         $obj->setDeleted(false);
         //If object has Company save with de user Company
         if(method_exists($obj,'setCompany')) $obj->setCompany($this->user->getCompany());
       }
       $obj->setDateupd(new \DateTime());
       try{
         //if object has a validation check it
         if(method_exists($obj,'formValidation')) $validation=$obj->{'formValidation'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->preParams);
         if($validation["valid"]==false) //Abort proccess
          return ["obj"=>false, "validation"=>$validation];
         //if object has a preproccess run it
         if(method_exists($obj,'preProccess')) $obj->{'preProccess'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->preParams);

         $this->entityManager->persist($obj);
         $this->entityManager->flush();
         if(method_exists($obj,'postProccess')) $obj->{'postProccess'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->postParams);
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
            if(method_exists($old,'get'.lcfirst($value['fieldName']))){
              if(($old->{'get'.lcfirst($value['fieldName'])}()!=NULL?$old->{'get'.lcfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL) != ($new->{'get'.lcfirst($value['fieldName'])}()!=NULL?$new->{'get'.lcfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL)){
                //Attribute changed store it
                $changes[]=["attribute"=>$value['fieldName'],"oldvalue"=>($old->{'get'.lcfirst($value['fieldName'])}()!=NULL?$old->{'get'.lcfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL),"newvalue"=>($new->{'get'.lcfirst($value['fieldName'])}()!=NULL?$new->{'get'.lcfirst($value['fieldName'])}()->format('Y-m-d H:i:s'):NULL)];
              }
            }
            break;
            default:
              if(method_exists($old,'get'.lcfirst($value['fieldName']))){
                if($old->{'get'.lcfirst($value['fieldName'])}() != $new->{'get'.lcfirst($value['fieldName'])}()){
                  //Attribute changed store it
                  $changes[]=["attribute"=>$value['fieldName'],"oldvalue"=>$old->{'get'.lcfirst($value['fieldName'])}(), "newvalue"=>$new->{'get'.lcfirst($value['fieldName'])}()];
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

  public function choiceRelation($class, $data, $nullable){
    $classname=explode('\\', $class);
    //If class has attribute company apply filter
    if(property_exists($class,'company')){
      $choices= $this->doctrine->getRepository($class)->findBy(['company'=>$this->user->getCompany(),'active'=>true, 'deleted'=>false]);
    }else{
      if(property_exists($class,'active')){
        $choices= $this->doctrine->getRepository($class)->findBy(['active'=>true, 'deleted'=>false]);
      }else $choices= $this->doctrine->getRepository($class)->findAll();
    }

    $result =  [
                  'attr' => ['class' => 'select2'],
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
                  'data' =>$data,
                  'placeholder' => 'Select '.strtolower(end($classname))
              ];

    return $result;
  }


public function choiceRelationTrigger($field,$nullable){
  $class="\App\Modules\\".$field["module"]."\\Entity\\".$field["nameClass"];
  $classTrigger="\App\Modules\\".$field["moduleTrigger"]."\\Entity\\".$field["nameClassTrigger"];
  $form=$this->request->request->get('form', null);
  $filter=[];
  if($form!=null){
    //select options of the trigger value selected
    //Check options for trigger FIELDS
    $triggerValue=$this->doctrine->getRepository($classTrigger)->findOneBy(['id'=>$form[$field["trigger"]]]);
    $filter=[$field["triggerParameter"]=>$triggerValue];
  }else{
    //get selected option or null options if not set
    if($this->obj!=null) {
      $triggerValue=$this->doctrine->getRepository($classTrigger)->findOneBy(['id'=>$this->obj->{'get'.ucfirst($field["triggerParameter"])}()]);
      $filter=[$field["triggerParameter"]=>$triggerValue];
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
  $result =  [
                'attr' => ['class' => 'select2'],
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
                'placeholder' => 'Select '.$field["nameClass"]
            ];

  return $result;
}
}
