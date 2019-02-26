<?php
namespace App\Modules\Globale\Utils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\CallbackTransformer;

class GlobaleFormUtils extends Controller
{
  private $ignoredAttributes=array('id','deleted','dateadd','dateupd');
  private $user;
  private $obj;
  private $request;
  private $controller;
  private $doctrine;
  private $entityManager;
  private $excludedAttributes;
  private $includedAttributes;
  private $template;
  private $form;

  //OLD INIT, ONLY FOR COMPATIBILITY
  public function init($doctrine, $request){
    $this->doctrine=$doctrine;
    $this->request=$request;
    $this->entityManager=$this->doctrine->getManager();
  }

  public function initialize($user, $obj, $template, $request, $controller, $doctrine, $excludedAttributes=array(), $includedAttributes=array(), $encoder=null){
    $this->user=$user;
    $this->obj=$obj;
    $this->request=$request;
    $this->controller=$controller;
    $this->doctrine=$doctrine;
    $this->excludedAttributes=$excludedAttributes;
    $this->includedAttributes=$includedAttributes;
    $this->template=$template;
    $this->entityManager=$this->doctrine->getManager();
    $this->encoder=$encoder;
  }

/*NEW METHODS*/
 //name     = Name of the form
 //ajax     = if true form is considered an jquery form
 //id       = id of the entity element
 //class    = class of the entity element
 //route    = route of the save/read method in controller, generally dataEntity
  public function formatForm($name, $ajax=false, $id=null, $class=null, $route=null){
    if(!$id){
				$this->obj=new $class();
			} else{
					$repository = $this->doctrine->getRepository($class);
					$this->obj=$repository->find($id);
					if($this->obj===NULL) $this->obj=new $class();
			}

    $form=$this->createFromEntity2(!$ajax)->getForm();
    $caption=ucfirst($name);
    return ["id"=>$name, "name"=>$caption, "form" => $form->createView(), "post"=>$this->controller->generateUrl(($route!=null)?$route:$this->request->get('_route'),["id"=>$id, "action"=>"save"]), "template" => json_decode(file_get_contents ($this->template))];
  }

  public function createFromEntity2($includeSave=true){
    $this->ignoredAttributes=array_merge($this->ignoredAttributes, $this->excludedAttributes);
    $class=get_class($this->obj);
    $form = $this->controller->createFormBuilder($this->obj);
    //Get class attributes
    foreach($this->entityManager->getClassMetadata($class)->fieldMappings as $key=>$value){
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){
        switch($value['type']){
          case 'datetime':
            $form->add($value['fieldName'], DateTimeType::class, array('widget' => 'single_text', 'date_format' => 'dd-MM-yyyy HH:mm'));
          break;

          case 'json':
            $form->add($value['fieldName'], TextType::class, ['attr'=>['class' => 'tagsinput']]);
            $form->get($value['fieldName'])
                ->addModelTransformer(new CallbackTransformer(
                    function ($tagsAsArray) { return implode(',', $tagsAsArray);},
                    function ($tagsAsString) {return explode(',', $tagsAsString);}
                ));
          break;

          default:
            $form->add($value['fieldName']);
          break;
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
      if(!in_array($value['fieldName'],$this->ignoredAttributes))
        $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelation($value["targetEntity"], $this->obj->{'get'.ucfirst($value["fieldName"])}()));
    }
    if($includeSave) $form->add('save', SubmitType::class, ['attr' => ['class' => 'save'],]);

    $this->form=$form;
    return $form;
  }

  public function make($id, $class, $action, $name, $type="full", $render="@Globale/form.html.twig", $returnRoute=null, $utilsClass=null){
    if(!$id){
				$this->obj=new $class();
			} else{
					$repository = $this->doctrine->getRepository($class);
					$this->obj=$repository->find($id);
					if($this->obj===NULL) $this->obj=new $class();
			}
			$form=$this->createFromEntity2(false)->getForm();
			switch($action){
				 case 'save':
           //Buscar si existe un proccess dentro del utils de la clase
           if($utilsClass!=null && method_exists($utilsClass, 'proccess')){
             $utils=new $utilsClass();
					   $this->obj=$utils->proccess($form,$this->obj,$this->request,$this->entityManager,$this->encoder);
           }else{
           //Si no, ejecutamos el process estandar del formutils
					   $this->obj=$this->proccess2($form,$this->obj);
           }
           if(is_bool($this->obj)) $result=false;
            else if ($this->obj->getId()!==FALSE) $result=true;
					 //$result=((!is_bool($this->obj) && $this->obj->getId()!==FALSE)?true:false);
           if($returnRoute==null)$returnRoute=$this->request->get('_route');
					 return new JsonResponse(array('result' => $result, 'href' =>$result?(($id!=$this->obj->getId())?$this->controller->generateUrl($returnRoute,["id"=>$this->obj->getId()]):''):'', 'reload' =>$result?(($id!=$this->obj->getId())?true:false):'', 'id' => $result?$this->obj->getId():''));
				 break;
				 case 'read':
						 return $this->controller->render($render, array(
							'formConstructor' => ["name"=>$name, "form" => $form->createView(), "type" => $type, "post"=>$this->controller->generateUrl($this->request->get('_route'),["id"=>$id, "action"=>"save"]), "template" => json_decode(file_get_contents ($this->template),true)]
					    ));
				break;
			}
  }


  public function proccess2($form,$obj){
    $form->handleRequest($this->request);
    if(!$form->isSubmitted()) return false;
    if ($form->isSubmitted() && $form->isValid()) {
       $obj = $form->getData();
       if($obj->getId() == null){
         $obj->setDateadd(new \DateTime());
         $obj->setDeleted(false);
       }
       $obj->setDateupd(new \DateTime());
       try{
         $this->entityManager->persist($obj);
         $this->entityManager->flush();
         return $obj;
       }catch (Exception $e) {
         return false;
       }
    }
  }








/*   -----------------------------------------------------   */

  public function createFromEntity($obj,$controller,$excludedAttributes=array(),$includedAttributes=array(),$includeSave=true){
    $this->ignoredAttributes=array_merge($this->ignoredAttributes, $excludedAttributes);
    $class=get_class($obj);
    $form = $controller->createFormBuilder($obj);
    //Get class attributes
    foreach($this->entityManager->getClassMetadata($class)->fieldMappings as $key=>$value){
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){
        switch($value['type']){
          case 'datetime':
            $form->add($value['fieldName'], DateTimeType::class, array('widget' => 'single_text', 'date_format' => 'dd-MM-yyyy HH:mm'));
          break;

          case 'json':
            $form->add($value['fieldName'], TextType::class, ['attr'=>['class' => 'tagsinput']]);
            $form->get($value['fieldName'])
                ->addModelTransformer(new CallbackTransformer(
                    function ($tagsAsArray) { return implode(',', $tagsAsArray);},
                    function ($tagsAsString) {return explode(',', $tagsAsString);}
                ));
          break;

          default:
            $form->add($value['fieldName']);
          break;
        }
      }
    }
    //Add included attributes
    foreach ($includedAttributes as $key => $value) {
      $form->add($value[0], $value[1], $value[2]);
    }
    //Get class relations
    foreach($this->entityManager->getClassMetadata($class)->associationMappings as $key=>$value){
      if(!isset($value["joinColumns"])) continue;
      if(!in_array($value['fieldName'],$this->ignoredAttributes))
        $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelation($value["targetEntity"], $obj->{'get'.ucfirst($value["fieldName"])}()));
    }
    if($includeSave) $form->add('save', SubmitType::class, ['attr' => ['class' => 'save'],]);
    $this->form=$form;
    return $form;
  }

  public function choiceRelation($class, $data){
    $classname=explode('\\', $class);
    $result =  [
                  'attr' => ['class' => 'select2'],
                  'choices' => $this->doctrine->getRepository($class)->findBy(['active'=>true, 'deleted'=>false]),
                  //'choices' => $this->doctrine->getRepository($class)->findAll(),
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

  public function proccess($form,&$obj){
    $form->handleRequest($this->request);
    if(!$form->isSubmitted()) return false;
		if ($form->isSubmitted() && $form->isValid()) {
			 $obj = $form->getData();
       if($obj->getId() == null){
         $obj->setDateadd(new \DateTime());
         $obj->setDeleted(false);
       }
			 $obj->setDateupd(new \DateTime());
			 $this->entityManager->persist($obj);
			 $this->entityManager->flush();
       return true;
		}
  }



}
