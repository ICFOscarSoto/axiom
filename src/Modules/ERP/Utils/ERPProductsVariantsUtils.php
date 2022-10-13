<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProducts;
class ERPProductsVariantsUtils
{
  private $module="ERP";
  private $name="ProductsVariants";
  public $parentClass="\App\Modules\ERP\Entity\ERPProducts";
  public $parentField="product";
  public function getExcludedForm($params){
   return ["product"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $product=$params["product"];
    $variant=$params["variant"];
    $productsRepository=$doctrine->getRepository(ERPProducts::class);

    $em=$doctrine->getManager();
    $ovariant=null;
    $ovarianttype=null;
    $variants=null;
    // Tipo de variante seleccionada
    if ($variant){
      $ovariant=$em->createQueryBuilder()->select('v')
        ->from('App\Modules\ERP\Entity\ERPVariants', 'v')
        ->where('v.id = :val_variant')
        ->setParameter('val_variant', $variant->getId())
        ->getQuery()
        ->getResult();
      if ($ovariant){
        $ovarianttype=$ovariant[0]->getVarianttype();
        $variants=$em->createQueryBuilder()->select('v')
          ->from('App\Modules\ERP\Entity\ERPVariants', 'v')
          ->where('v.varianttype = :val_varianttype')
          ->orderBy('v.name', 'ASC')
          ->setParameter('val_varianttype', $ovarianttype)
          ->getQuery()
          ->getResult();
      }
    }
    // Listado de tipos de variante
    $variantstypes=$em->createQueryBuilder()->select('vt')
      ->from('App\Modules\ERP\Entity\ERPVariantsTypes', 'vt')
      ->where('vt.active=1')
      ->andWhere('vt.deleted=0')
      ->andWhere('vt.company = :val_company')
      ->orderBy('vt.name', 'ASC')
      ->setParameter('val_company', $user->getCompany())
      ->getQuery()
      ->getResult();

    return
    [
      [ 'type',
        ChoiceType::class,
        [
          'required' => false,
          'mapped' => false,
          'data' => $ovarianttype?$ovarianttype:null,
          'attr' => ['class' => 'select2', 'attr-target' => 'formUser', 'attr-target-type' => 'full'],
          'choices' => $variantstypes,
          'placeholder' => 'Selecciona tipo de variante ...',
          'choice_label' => function($obj, $key, $index) {
            return $obj->getName();
          },
          'choice_attr' => function($obj, $key, $index) {
              return ['class' => $obj->getId()];
          },
          'choice_value' => function ($obj) {
              return $obj ? $obj->getId() : '';
          }
        ]
      ],
      [ 'variant',
        ChoiceType::class,
        [
          'required' => false,
          'mapped' => true,
          'data' => $ovariant?$ovariant[0]:null,
          'attr' => ['class' => 'select2', 'attr-target' => 'formUser', 'attr-target-type' => 'full'],
          'choices' => $variants,
          'placeholder' => 'Selecciona variante ...',
          'choice_label' => function($obj, $key, $index) {
            return $obj->getName();
          },
          'choice_attr' => function($obj, $key, $index) {
              return ['class' => $obj->getId()];
          },
          'choice_value' => function ($obj) {
              return $obj ? $obj->getId() : '';
          }
        ]
      ]
    ];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListByProduct($product){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $product,
                        "id" => $product,
                        "field" => "product",
                        "parentModule" => "ERP",
                        "parentName" => "Products"
                      ],
      'orderColumn' => 3,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }
}
