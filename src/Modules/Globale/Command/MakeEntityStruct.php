<?php
namespace App\Modules\Globale\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRAutoCloseClocks;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use \App\Helpers\HelperFiles;


class MakeEntityStruct extends ContainerAwareCommand
{
  private $doctrine;
  private $entityManager;
  protected function configure(){
        $this
            ->setName('make:entitystruct')
            ->setDescription('Make JSON files Lists and Forms for entity')
            ->addArgument('module', InputArgument::REQUIRED, '¿Modulo al que pertenece la entidad?')
            ->addArgument('class', InputArgument::REQUIRED, '¿Nombre de la entidad sin modulo?')
        ;
}

protected function execute(InputInterface $input, OutputInterface $output)
  {
      $this->doctrine = $this->getContainer()->get('doctrine');
      $this->entityManager = $this->doctrine->getManager();
      $module = $input->getArgument('module');
      $class = $input->getArgument('class');
      //$class = basename($classpath);
      $output->writeln('Módulo: '.$module);
      $output->writeln('Clase: '.$class);

      $output->writeln('Generating Class Utils');
      $fileUtils=file_get_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'axiom'.DIRECTORY_SEPARATOR.'classUtils.php');
      $fileUtils=str_replace('@@MODULE@@', $module, $fileUtils);
      $fileUtils=str_replace('@@CLASSNAME@@', $class, $fileUtils);
      file_put_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.$module.$class.'Utils.php',$fileUtils);

      $fileForms=["sections"=> [["name" => "data", "fields"=>[]]]];
      $fileLists=[];
      $output->writeln('Generating List and Form File');

      $active=null;
      //simple attributes
      foreach($this->entityManager->getClassMetadata('\App\Modules\\'.$module.'\Entity\\'.$module.$class)->fieldMappings as $key=>$value){
          if(!in_array($value['fieldName'],["id","deleted","dateadd","dateupd"])){
            $fieldForm=["name"=>$value['fieldName'], "cols"=>4];
            $fileForms["sections"][0]["fields"][]=$fieldForm;
          }

          if(!in_array($value['fieldName'],["deleted","dateadd","dateupd"])){
            if($value['fieldName']=='active'){
              $active=array ('name' => 'active','caption' => 'Estado','width' => '10%','class' => 'dt-center','replace' =>
                        array ( 1 => array ('text' => 'Activo','html' => '<div style="min-width: 75px;" class="label label-success">Activo</div>'),
                                0 => array ('text' => 'Desactivado','html' => '<div style="min-width: 75px;" class="label label-danger">Desactivado</div>')));
            }else{
                $field=["name"=>$value['fieldName'],"caption"=>$value['fieldName']];
                $fileLists[]=$field;
            }
          }
      }
      //relation attributes
      foreach($this->entityManager->getClassMetadata('\App\Modules\\'.$module.'\Entity\\'.$module.$class)->associationMappings as $key=>$value){
          if(!isset($value["joinColumns"])) continue;
          if(!in_array($value['fieldName'],["company"])){

            $fieldForm=["name"=>$value['fieldName'], "cols"=>4];
            $fileForms["sections"][0]["fields"][]=$fieldForm;
            if(property_exists($value["targetEntity"],'lastname')){
              $field=["name"=>$value['fieldName'].'__name_o_'.$value['fieldName'].'__lastname',"caption"=>$value['fieldName']];
              $fileLists[]=$field;
            }else
            if(property_exists($value["targetEntity"],'name')){
              $field=["name"=>$value['fieldName'].'__name',"caption"=>$value['fieldName']];
              $fileLists[]=$field;
            }
          }
      }

      if($active!=null) $fileLists[]=$active;
      file_put_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Forms'.DIRECTORY_SEPARATOR.$class.'.json','['.json_encode($fileForms,JSON_PRETTY_PRINT).']');
      file_put_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Lists'.DIRECTORY_SEPARATOR.$class.'.json',json_encode($fileLists,JSON_PRETTY_PRINT));

      $output->writeln('Generating List Top Buttons file');
      $fileUtils=file_get_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'axiom'.DIRECTORY_SEPARATOR.'listTopButtons.json');
      $fileUtils=str_replace('@@MODULE@@', $module, $fileUtils);
      $fileUtils=str_replace('@@CLASSNAME@@', $class, $fileUtils);
      file_put_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Lists'.DIRECTORY_SEPARATOR.$class.'TopButtons.json',$fileUtils);

      $output->writeln('Generating List Field Buttons file');
      $fileUtils=file_get_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'axiom'.DIRECTORY_SEPARATOR.'listFieldButtons.json');
      $fileUtils=str_replace('@@MODULE@@', $module, $fileUtils);
      $fileUtils=str_replace('@@CLASSNAME@@', $class, $fileUtils);
      file_put_contents($this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Lists'.DIRECTORY_SEPARATOR.$class.'FieldButtons.json',$fileUtils);


  }
}
