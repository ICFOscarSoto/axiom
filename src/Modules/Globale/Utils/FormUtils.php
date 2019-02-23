<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\CallbackTransformer;

class FormUtils extends Controller
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

  public function initialize($user, $obj, $template, $request, $controller, $doctrine, $excludedAttributes=array(), $includedAttributes=array()){
    $this->user=$user;
    $this->obj=$obj;
    $this->request=$request;
    $this->controller=$controller;
    $this->doctrine=$doctrine;
    $this->excludedAttributes=$excludedAttributes;
    $this->includedAttributes=$includedAttributes;
    $this->template=$template;
    $this->entityManager=$this->doctrine->getManager();
  }

/*NEW METHODS*/
  public function formatForm($name, $ajax=false){
    $form=$this->createFromEntity2(!$ajax)->getForm();
    //return $form;
    //return ["id"=>$name, "form" => $form->createView(), "template" => json_decode(file_get_contents ($this->template))];
    $proccess=$this->proccess($form,$this->obj);
    if($ajax){
      if($proccess===FALSE) return ["id"=>$name, "form" => $form->createView(), "template" => json_decode(file_get_contents ($this->template),true)];
        else return $proccess;
    }else return $form;
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
        $form->add($value['fieldName'], ChoiceType::class, $this->choiceRelation($value["targetEntity"], $obj->{'get'.ucfirst($value["fieldName"])}()));
    }
    if($includeSave) $form->add('save', SubmitType::class, ['attr' => ['class' => 'save'],]);
    $this->form=$form;
    return $form;
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

  public function proccess($form,$obj){
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
