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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\CallbackTransformer;
use App\Modules\Globale\Entity\GlobaleCompanies;

class GlobaleFormUtils extends Controller
{
  private $ignoredAttributes=array('id','deleted','dateadd','dateupd');
  private $user;
  private $company;
  private $obj;
  private $request;
  private $controller;
  private $doctrine;
  private $entityManager;
  private $excludedAttributes;
  private $includedAttributes;
  private $template;
  private $templateArray;
  private $transforms=array();
  private $form;
  private $values=array();
  private $routeParams=array();
  private $includePreTemplate=array();
  private $includePostTemplate=array();
  public $preParams=array();
  public $postParams=array();

  //OLD INIT, ONLY FOR COMPATIBILITY
  public function init($doctrine, $request){
    $this->doctrine=$doctrine;
    $this->request=$request;
    $this->entityManager=$this->doctrine->getManager();
  }

  public function initialize($user, $obj, $template, $request, $controller, $doctrine, $excludedAttributes=array(), $includedAttributes=array(), $encoder=null, $routeParams=[], $includePreTemplate=[], $includePostTemplate=[]){
    $this->user=$user;
    $this->obj=$obj;
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

    $form=$this->createFromEntity2(!$ajax)->getForm();
    $caption=ucfirst($name);
    $routeParams=array_merge($routeParams, ["id"=>$id, "action"=>"save"]);
    return ["id"=>$name, "id_object"=>!$this->obj->getId()?0:$this->obj->getId(), "name"=>$caption, "form" => $form->createView(), "post"=>$this->controller->generateUrl(($route!=null)?$route:$this->request->get('_route'),$routeParams), "template" => json_decode(file_get_contents ($this->template))];
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
              //Check if has a template types
              $field=$this->searchTemplateField($value['fieldName']);
              if(isset($field["type"])){
                if($field["type"]=="date")
                  $form->add($value['fieldName'], DateType::class, ['required' => !$value["nullable"], 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'attr' => ['class' => 'datepicker']]);
                if($field["type"]=="time")
                  $form->add($value['fieldName'], DateType::class, ['required' => !$value["nullable"], 'widget' => 'single_text', 'format' => 'HH:mm:ss', 'attr' => ['class' => 'timepicker']]);
              }else $form->add($value['fieldName'], DateTimeType::class, ['required' => !$value["nullable"], 'widget' => 'single_text', 'format' => 'dd/MM/yyyy kk:mm:ss', 'attr' => ['class' => 'datetimepicker']]);
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
              if(isset($field["transform"])){
                switch ($field["transform"]['type']){
                  case 'option':
                    $form->add($value['fieldName'], ChoiceType::class, [
                        'choices'  => $field["transform"]['options'],
                    ]);
                  break;
                  case 'button':
                    $form->add($field['name'], ButtonType::class, [
                        'attr' => ['class' => $field["transform"]['class'].' '.isset($field["class"])?$field["class"]:''],
                    ]);
                  break;
                }
              }else $form->add($value['fieldName'],null,['disabled' => isset($field["readonly"])?$field["readonly"]:false, 'attr'=>['readonly' => isset($field["readonly"])?$field["readonly"]:false,'class'=>isset($field["class"])?$field["class"]:'']]);
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
      if(!in_array($value['fieldName'],$this->ignoredAttributes))
        $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelation($value["targetEntity"], $this->obj->{'get'.ucfirst($value["fieldName"])}(),$nullable));
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
			$form=$this->createFromEntity2(false)->getForm();
			switch($action){
				 case 'save':
           //Buscar si existe un proccess dentro del utils de la clase
           if($utilsClass!=null && method_exists($utilsClass, 'proccess')){
             $utils=new $utilsClass();
					   $this->obj=$utils->proccess($form,$this->user,$this->obj,$this->request,$this->entityManager,$this->encoder);
           }else{
           //Si no, ejecutamos el process estandar del formutils
					   $this->obj=$this->proccess2($form,$this->obj);
           }
           if(is_bool($this->obj)) $result=false;
            else if ($this->obj->getId()!==FALSE) $result=true;
					 //$result=((!is_bool($this->obj) && $this->obj->getId()!==FALSE)?true:false);
           if($returnRoute==null)$returnRoute=$this->request->get('_route');
           $routeParams=array_merge($this->routeParams, ["id"=>$this->obj->getId()]);
           $route=$result?(($id!=$this->obj->getId())? $returnRoute=='none'?'':$this->controller->generateUrl($returnRoute,$routeParams) :''):'';
					 return new GlobaleJsonResponse(array('result' => $result, 'href' =>$route, 'reload' =>$result?(($id!=$this->obj->getId())?($returnRoute=='none'?false:true):false):'', 'id' => $result?$this->obj->getId():''));
           //return array('result' => $result, 'href' =>$route, 'reload' =>$result?(($id!=$this->obj->getId())?($returnRoute=='none'?false:true):false):'', 'id' => $result?$this->obj->getId():'');
				 break;
				 case 'read':
             $routeParams=array_merge($this->routeParams, ["id"=>$id, "action"=>"save"]);
             $route=$this->controller->generateUrl($this->request->get('_route'),$routeParams);
						 return $this->controller->render($render, array(
              'includes' => $includesArray,
              'include_pre_templates' => $this->includePreTemplate,
              'include_post_templates' => $this->includePostTemplate,
              'formConstructor' => ["id"=>$id, "id_object"=>$id, "name"=>$name, "form" => $form->createView(), "type" => $type, "post"=>$route, "template" => json_decode(file_get_contents ($this->template),true)]
					    ));
				break;
			}
  }

 public function proccess2($form,$obj){
    $form->handleRequest($this->request);
    if(!$form->isSubmitted()) return false;
    if ($form->isSubmitted() && $form->isValid()) {
       $obj = $form->getData();



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
         if(method_exists($obj,'preProccess')) $obj->{'preProccess'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->preParams);
         $this->entityManager->persist($obj);
         $this->entityManager->flush();
         if(method_exists($obj,'postProccess')) $obj->{'postProccess'}($this->controller->get('kernel'), $this->doctrine, $this->user, $this->postParams);
         return $obj;
       }catch (Exception $e) {
         return false;
       }
    }
  }

  public function choiceRelation($class, $data, $nullable){
    $classname=explode('\\', $class);
    //If class has attribute company aply filter
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
                  'expanded' => false,
                  'data' =>$data,
                  'placeholder' => 'Select '.strtolower(end($classname))
              ];

    return $result;
  }
}
