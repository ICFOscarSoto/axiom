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
  private $doctrine;
  private $request;
  private $entityManager;
  private $form;

  public function init($doctrine, $request){
    $this->doctrine=$doctrine;
    $this->request=$request;
    $this->entityManager=$this->doctrine->getManager();
  }

  public function createFromEntity($obj,$controller,$excludedAttributes=array(),$includedAttributes=array()){
    $this->ignoredAttributes=array_merge($this->ignoredAttributes, $excludedAttributes);
    $class=get_class($obj);
    $form = $controller->createFormBuilder($obj);
    //Get class attributes
    foreach($this->entityManager->getClassMetadata($class)->fieldMappings as $key=>$value){
      if(!in_array($value['fieldName'],$this->ignoredAttributes)){
        switch($value['type']){
          /*case 'json':
            $form->add($value['fieldName'], TextType::class);
            $form->get($value['fieldName'])
                ->addModelTransformer(new CallbackTransformer(
                    function ($tagsAsArray) { return implode(',', $tagsAsArray);},
                    function ($tagsAsString) {return explode(',', $tagsAsString);}
                ));
          break;*/
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
    $form->add('save', SubmitType::class, ['attr' => ['class' => 'save'],]);
    $this->form=$form;
    return $form;
  }

  public function choiceRelation($class, $data){
    $result =  [
                  'attr' => ['class' => 'select2'],
                  'choices' => $this->doctrine->getRepository($class)->findBy(['active'=>true, 'deleted'=>false]),
                  //'choices' => $this->doctrine->getRepository($class)->findAll(),
                  'choice_label' => function($obj, $key, $index) {
                      return $obj->getName();
                  },
                  'choice_attr' => function($obj, $key, $index) {
                      return ['class' => $obj->getId()];
                  },
                  'expanded' => false,
                  'data' =>$data
              ];

    return $result;
  }

  public function proccess($form,$obj){
    $form->handleRequest($this->request);
		if ($form->isSubmitted() && $form->isValid()) {
			 $obj = $form->getData();
       if($obj->getId() == null) $obj->setDateadd(new \DateTime());
			 $obj->setDateupd(new \DateTime());
			 $this->entityManager->persist($obj);
			 $this->entityManager->flush();
		}
  }

}
