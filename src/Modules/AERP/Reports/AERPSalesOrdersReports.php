<?php
namespace App\Modules\AERP\Reports;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;

use App\Modules\Globale\Reports\GlobaleReports;


class AERPSalesOrdersReports
{
  private $pdf;
  private $user;

  private $configuration;
  private $bgcolor_r, $bgcolor_g, $bgcolor_b;
  private $shadowcolor_r, $shadowcolor_g, $shadowcolor_b;

  private $cursor;
  private $positions=[];

  private function secToH($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    return sprintf("%02d", $hours).":".sprintf("%02d", $minutes).":".sprintf("%02d", $seconds);
  }

  function Table($pdf, $data, $columns, $associative=false){
      // Header
      $pdf->SetFont('Arial','',7);
      //SetDrawColor(int r [, int g, int b]);
      $pdf->SetFillColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
      $pdf->SetDrawColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
      $pdf->SetTextColor(255,255,255);
      $pdf->SetX(10);
      $x=10;
      $this->positions=[];
      $wordwraps=[];
      for($i=0;$i<count($columns);$i++){
          $pdf->Cell($columns[$i]["width"],4,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),'TBRL',0,'C',true);
          $this->positions[$i]=$x;
          $x=$x+$columns[$i]["width"];
      }
      $pdf->Ln();
      // Data

      $y=$pdf->GetY();
      $pdf->SetTextColor(0,0,0);

      foreach($data as $key=>$row)
      {
        $wordwraps=[];
        for($i=0;$i<count($columns);$i++){

          $x=$i>0?$columns[$i-1]["width"]:0;
          $pdf->SetDrawColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
          if($i==count($columns)-1) $border='R'; else $border='';
          $text=utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]);
          if(strpos($text,'#b#')===0){ $pdf->SetFont('Arial','b',8); $text=substr($text, 3);}else $pdf->SetFont('Arial','',8);
          $wordwraps[$i]=ceil($pdf->GetStringWidth($text)/$columns[$i]["width"]);
          if($i%2) $pdf->SetFillColor(255, 255, 255); else $pdf->SetFillColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
          $pdf->SetXY($this->positions[$i],$y);
          $pdf->MultiCell($columns[$i]["width"],5, $text, $border,isset($columns[$i]["align"])?$columns[$i]["align"]:'L',true);
        }

        //Compensate wordwraps shadows
        for($i=0;$i<count($columns);$i++){
          for($j=$wordwraps[$i];$j<max($wordwraps);$j++){
            if($i%2) $pdf->SetFillColor(255, 255, 255); else $pdf->SetFillColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
            $pdf->SetX($this->positions[$i]);
            $pdf->Cell($columns[$i]["width"],5, "", "",0,'L',true);
          }
        }

        array_shift ($data);
        $pdf->SetFillColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
        if($pdf->GetY()>=235){
          $pdf->SetX(10);
          $pdf->AddPage();
          return $data;
        }
        $y=$y+(5*max($wordwraps));
      }
      $pdf->SetY($y);
      $this->cursor=$y;
      //paint shadows
      while($pdf->GetY()<237){
        $pdf->SetX(10);
        for($i=0;$i<count($columns);$i++){
          if($i%2) $pdf->SetFillColor(255, 255, 255); else $pdf->SetFillColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
          $pdf->Cell($columns[$i]["width"],2, "", "",0,'L',true);

        }
        $pdf->Ln(2);
      }
      return $data;
  }


  private function docFooter($document){
    $x=$this->pdf->getX();
    $y=$this->pdf->getY();
    $this->pdf->SetTextColor(255,255,255);
    $this->pdf->SetY(-55);
    $this->pdf->SetX(10);
    $this->pdf->Cell(190,1,'','',0,'C',true);
    $this->pdf->SetY(-43);
    $this->pdf->SetX(10);
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->Cell(30,4,utf8_decode('IMPORTE'),'',0,'C',true);
    $this->pdf->Cell(30,4,utf8_decode('DTO. P.P.'),'',0,'C',true);
    $this->pdf->Cell(30,4,utf8_decode('BASE IMPONIBLE'),'',0,'C',true);
    $this->pdf->Cell(30,4,utf8_decode('RE'),'',0,'C',true);
    $this->pdf->Cell(37,4,utf8_decode('IVA'),'',0,'C',true);
    $this->pdf->Cell(33,4,utf8_decode('TOTAL'),'',0,'C',true);
    $this->pdf->Ln(4.1);
    $this->pdf->SetX(10);
    $this->pdf->SetFillColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
    $this->pdf->SetDrawColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
    $this->pdf->setTextColor(0,0,0);
    $this->pdf->SetFont('Arial','b',9);
    $this->pdf->Cell(30,8,utf8_decode(number_format($document->getTotalnet(),2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $this->pdf->Cell(30,8,utf8_decode(number_format($document->getTotaldto(),2,',','.').json_decode('"\u0080"')),'TB',0,'C',false);
    $this->pdf->Cell(30,8,utf8_decode(number_format($document->getTotalbase(),2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $this->pdf->Cell(30,8,utf8_decode(number_format($document->getTotalSurcharge(),2,',','.').json_decode('"\u0080"')),'TB',0,'C',false);
    $this->pdf->Cell(37,8,utf8_decode(number_format($document->getTotaltax(),2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $this->pdf->Cell(33,8,utf8_decode(number_format($document->getTotal(),2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $this->pdf->Ln(12);
    $this->pdf->SetX(10);
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->SetFillColor($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b);
    $this->pdf->Cell(40,3,utf8_decode('Forma de pago'),'B',0,'L',true);
    $this->pdf->Cell(150,3,utf8_decode(""),'B',0,'L',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(40,3,utf8_decode('Datos bancarios'),'B',0,'L',true);
    $this->pdf->Cell(150,3,utf8_decode(""),'B',0,'L',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(40,3,utf8_decode('Vencimientos'),'B',0,'L',true);
    $shadow=40;
    /*foreach($invoice['expirations'] as $expiration){
      $this->pdf->Cell(20,3,utf8_decode(date("d/m/Y",strtotime($expiration["date"]["date"]))),'B',0,'R',true);
      $shadow+=20;
    }*/
    $this->pdf->Cell(190-$shadow,3,"",'B',0,'R',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(40,3,utf8_decode('Importe'),'B',0,'L',true);
    /*foreach($invoice['expirations'] as $expiration){
      $this->pdf->Cell(20,3,utf8_decode($expiration["amount"].json_decode('"\u0080"')),'B',0,'R',true);

    }*/
    $this->pdf->Cell(190-$shadow,3,"",'B',0,'R',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(190,3,utf8_decode(''),'B',0,'L',true);
    $this->pdf->SetXY(10,244);
    $this->pdf->SetFont('Arial','',6);
    $this->pdf->SetTextColor(75,75,75);
    $this->pdf->MultiCell(190, 3, utf8_decode($this->configuration->getLopd()),0,'C', false);
    $this->pdf->SetXY(0,0);
    $this->pdf->TextWithRotation(8,258,utf8_decode($this->configuration->getRegister()), 90);
    $this->pdf->setXY($x, $y);
  }

  private function docHeader($document){
    $this->pdf->SetDrawColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->SetFillColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->Rect(105, 41, 1.5, 29,'DF');
    $this->pdf->Rect(198, 41, 1.5, 29,'DF');
    $x=$this->pdf->getX();
    $y=$this->pdf->getY();
    $this->pdf->setXY(5, 39);
    //$this->pdf->SetDrawColor(0, 0, 0);
    $this->pdf->Cell(22,9,utf8_decode('Nº Pedido'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($document->getCode()),'',0,'L',false);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->Ln(4);
    $this->pdf->Cell(22,9,utf8_decode('Fecha'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($document->getDate()->format("d/m/Y")),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->Cell(22,9,utf8_decode('Cliente'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($document->getCustomer()->getCode()),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->Cell(22,9,utf8_decode('Página'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($this->pdf->PageNo().'/{nb}'),'',0,'L',false);
    $this->pdf->setXY(110, 40);
    $this->pdf->Cell(60,9,utf8_decode($document->getCustomername()),'',0,'L',false);
    $this->pdf->setXY(110, 44);
    $this->pdf->Cell(60,9,utf8_decode($document->getCustomeraddress()),'',0,'L',false);
    $this->pdf->setXY(110, 48);
    $this->pdf->Cell(60,9,utf8_decode($document->getCustomercity()." - ".$document->getCustomerpostcode()." - ".$document->getCustomerstate()),'',0,'L',false);
    $this->pdf->setXY(110, 52);
    $this->pdf->Cell(60,9,utf8_decode("NIF/NIE ".$document->getVat()),'',0,'L',false);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->setXY(5, 31);
    $this->pdf->SetDrawColor(248, 250, 255);
    $this->pdf->SetFont('Arial','',14);
    $this->pdf->Cell(60,9,utf8_decode('PEDIDO VENTA'),'',0,'L',false);
    $this->pdf->setXY($x, $y);
  }

  function create($params, $dest='I', $file=null){
    setlocale( LC_NUMERIC, 'es_ES' );
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $document=$params["document"];
    $lines=$params["lines"];
    $this->configuration=$params["configuration"];
    list($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b) = sscanf($this->configuration->getBgcolor(), "#%02x%02x%02x");
    list($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b) = sscanf($this->configuration->getShadowcolor(), "#%02x%02x%02x");

      $columns=[["name"=>"CÓDIGO","width"=>30, "align"=>"L"], //190
                ["name"=>"DESCRIPCIÓN","width"=>77,"align"=>"L"],
                ["name"=>"UNIDADES","width"=>15,"align"=>"C"],
                ["name"=>"PRECIO","width"=>20,"align"=>"R"],
                ["name"=>"DTO","width"=>13,"align"=>"C"],
                ["name"=>"IVA","width"=>13,"align"=>"C"],
                ["name"=>"IMPORTE","width"=>22,"align"=>"R"]
      ];

      $data=[];
      $last_shipping=null;
      foreach($lines as $line){
        $data[]=[$line->getCode(),$line->getName(),$line->getQuantity(),$line->getUnitprice().json_decode('"\u0080"'),$line->getDtoperc()."%",$line->getTaxperc()."%",$line->getTotal().json_decode('"\u0080"')];
      }

      $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
      $this->pdf->user=$params["user"];
      $this->pdf->AddPage();

      $result=0;
      while(count($data)){
        $this->docHeader($document);
        $this->docFooter($document);
        $this->pdf->SetY(80);
        $data=$this->Table($this->pdf,$data,$columns);
      }

      //Paint Comments
      $this->pdf->setXY($this->positions[1],$this->cursor+5);
      $this->pdf->MultiCell($columns[1]["width"],5, utf8_decode($document->getNotes()), 0,'L',false);

      return $this->pdf->Output($dest, $file==null?$document->getCode().".pdf":$file);

}
}
