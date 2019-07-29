<?php
namespace App\Modules\Globale\Utils;

use App\Modules\Globale\Entity\GlobaleCompanies;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\Globale\Reports\GlobaleReports;


class GlobalePrintUtils
{
   private $template=null;
   public $title;
   private $pdf;
   private $user;

   public function applyFormats($array){
     $temp=$array;
     foreach($this->template as $key=>$field){
       if(isset($field["format"])){
          //Parse type fields
          switch($field["format"]){
            case "time":
              foreach($temp as $key=>$record){
                $temp[$key][$field["name"]]=gmdate("H:i:s", $temp[$key][$field["name"]]);
              }
            break;
          }
       }
     }
     return $temp;
   }

   private function docHeader(){
     $this->pdf->SetFillColor(210, 210, 210);
     $this->pdf->SetDrawColor(210, 210, 210);
     $this->pdf->SetTextColor(50,50,50);
     $this->pdf->SetFont('Arial','B',10);
     $this->pdf->Cell(190,6,utf8_decode($this->title),'TRLB','R','C');
     $this->pdf->Ln(10);
   }

   public function print($list, $template, $params){
     $this->template=$template;
     $array=$list["data"];
     //exclude tags column, last
     $key='_tags';
     array_walk($array, function (&$v) use ($key) {
      unset($v[$key]);
     });
     $array=$this->applyFormats($array);
     $this->pdf  = new GlobaleReports();
     $this->pdf->AliasNbPages();
     //$this->pdf->SetAutoPageBreak(false);
     $doctrine=$params["doctrine"];
     $this->user=$params["user"];

     $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
     $this->pdf->user=$params["user"];
     $this->pdf->AddPage();
     $result=0;
     while(count($array)){
       $this->docHeader();
       $array=$this->pdf->Table($array,$template, true);
     }
     $this->pdf->Ln(1);
     $this->pdf->Cell(190,6,"",'T',0,'L',false);
     return $this->pdf->Output();

   }



}
