<?php
namespace App\Modules\Globale\Reports\CustomReports;

class CustomReports_2{

  function Header($pdf)
  {
      // Select Arial bold 15
      $image_path = $pdf->image_path.$pdf->user->getCompany()->getId().'-medium.png';
      if(file_exists($image_path))
        $pdf->Image($image_path,10,10, null,15,'PNG');
      $pdf->SetFont('Arial','B',15);
      $pdf->Image($pdf->image_path.'../resources/logo-bextok.png',150,1, null,30,'PNG');
      $pdf->Image($pdf->image_path.'../resources/logo-iso.png',185,6, null,20,'PNG');
      // Move to the right
      //$pdf->Cell(100);
      // Framed title
      $pdf->SetTextColor(44,132,194);
      $pdf->SetFont('Arial','B',8);
      $company=$pdf->user->getCompany();
      $pdf->Cell(190,4,strtoupper(utf8_decode($company->getSocialname())),0,'R','C');
      $pdf->Ln(4);
      $pdf->SetFont('Arial','',8);
      //$pdf->Cell(100);
      $pdf->Cell(200,4,utf8_decode($company->getAddress()),0,'R','C');
      $pdf->Ln(3);
      $pdf->Cell(200,4,utf8_decode('Tlfno: 967 240 112 - Fax: 967 213 207'),0,'R','C');
      $pdf->Ln(3);
      $pdf->Cell(200,4,utf8_decode($company->getPostcode()." - ".$company->getCity()." - ".$company->getState()),0,'R','C');
      $pdf->Ln(3);
      $pdf->Cell(200,4,utf8_decode("CIF: ".$company->getVat())." - Apdo Correos: 5140",0,'R','C');
      $pdf->Ln(10);

      /*$pdf->SetDrawColor(44, 132, 194);
      $pdf->SetFillColor(44, 132, 194);
      $pdf->SetTextColor(44, 132, 194);
      $pdf->Rect(105, 41, 1.5, 29,'DF');
      $pdf->Rect(198, 41, 1.5, 29,'DF');
      $x=$pdf->getX();
      $y=$pdf->getY();
      $pdf->setXY(5, 39);
      $pdf->SetDrawColor(248, 250, 255);
      $pdf->Cell(60,9,utf8_decode('Nº Factura'),'',0,'L',false);
      $pdf->Ln(8);
      $pdf->Cell(60,9,utf8_decode('Fecha'),'',0,'L',false);

      $pdf->setXY(5, 31);
      $pdf->SetDrawColor(248, 250, 255);
      $pdf->SetFont('Arial','',14);
      //$pdf->setXY(10, 70);
      $pdf->Cell(60,9,utf8_decode('FACTURA'),'',0,'L',false);
      $pdf->setXY($x, $y);*/

  }


  /*function Table($pdf, $data, $columns, $associative=false){
      // Header
      $pdf->SetFont('Arial','',7);
      //SetDrawColor(int r [, int g, int b]);
      $pdf->SetFillColor(234, 246, 255);
      $pdf->SetDrawColor(44, 132, 194);
      $pdf->SetTextColor(44,132,194);
      for($i=0;$i<count($columns);$i++){
        if($i==0) $borders='TBL'; else
          if($i==(count($columns)-1)) {$borders='TBR';$pdf->SetFont('Arial','B',7);} else $borders='TB';
          $pdf->Cell($columns[$i]["width"],5,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),$borders,0,'C',true);
      }
      $pdf->Ln();
      // Data
      $pdf->SetFont('Arial','',8);
      foreach($data as $key=>$row)
      {
        for($i=0;$i<count($columns);$i++){
          if($pdf->GetY()>=268){
            $pdf->Cell($columns[$i]["width"],5, utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]),isset($columns[$i]["border"])?$columns[$i]["border"]:'LRB',0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L');
          }else{
            $pdf->Cell($columns[$i]["width"],5, utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]),isset($columns[$i]["border"])?$columns[$i]["border"]:'LR',0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L');
          }
        }
        array_shift ($data);
        if($pdf->GetY()>=268){
          $pdf->AddPage();
          return $data;
        }else $pdf->Ln(4);
      }
      // Closing line
      //$pdf->Cell(array_sum($w),0,'','T');
      return $data;
  }*/

  function Table($pdf, $data, $columns, $associative=false){
      // Header
      $pdf->SetFont('Arial','',7);
      //SetDrawColor(int r [, int g, int b]);
      $pdf->SetFillColor(44, 132, 194);
      $pdf->SetDrawColor(44, 132, 194);
      $pdf->SetTextColor(255,255,255);
      $pdf->SetX(10);
      for($i=0;$i<count($columns);$i++){

          $pdf->Cell($columns[$i]["width"],4,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),'TBRL',0,'C',true);
      }
      $pdf->Ln();
      // Data
      $pdf->SetTextColor(0,0,0);
      foreach($data as $key=>$row)
      {
        $pdf->SetX(10);
        for($i=0;$i<count($columns);$i++){
          $pdf->SetDrawColor(248, 250, 255);
          if($i==count($columns)-1) $border='R'; else $border='';
          $text=utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]);
          if(strpos($text,'#b#')===0){ $pdf->SetFont('Arial','b',8); $text=substr($text, 3);}else $pdf->SetFont('Arial','',8);
          if($pdf->GetY()>=268){

            if($i%2) $pdf->SetFillColor(255, 255, 255); else $pdf->SetFillColor(248, 250, 255);//$pdf->SetFillColor(207, 225, 255); //$pdf->SetFillColor(234, 246, 255);
            $pdf->Cell($columns[$i]["width"],5, $text, $border,0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L',true);
          }else{
            if($i%2) $pdf->SetFillColor(255, 255, 255); else $pdf->SetFillColor(248, 250, 255);
            $pdf->Cell($columns[$i]["width"],5, $text, $border,0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L',true);
          }
        }
        array_shift ($data);
        $pdf->SetFillColor(44, 132, 194);
        if($pdf->GetY()>=235){
          $pdf->SetX(10);
          //$pdf->Cell($columns[$i]["width"],1,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),'RL',0,'C',true);
          $pdf->AddPage();
          return $data;
        }else $pdf->Ln(4);
      }
      while($pdf->GetY()<237){
        $pdf->SetX(10);
        for($i=0;$i<count($columns);$i++){
          if($i%2) $pdf->SetFillColor(255, 255, 255); else $pdf->SetFillColor(248, 250, 255);
          $pdf->Cell($columns[$i]["width"],2, "", "",0,'L',true);

        }
        $pdf->Ln(2);
      }

      // Closing line
      //$pdf->Cell(array_sum($w),0,'','T');
      return $data;
  }

  function Footer($pdf)
  {
    /*  $pdf->SetTextColor(255,255,255);
      $pdf->SetY(-43);
      $pdf->SetX(10);
      $pdf->SetFont('Arial','',7);
      $pdf->Cell(26,4,utf8_decode('IMPORTE'),'',0,'C',true);
      $pdf->Cell(23,4,utf8_decode('DTO. P.P.'),'',0,'C',true);
      $pdf->Cell(34,4,utf8_decode('BASE IMPONIBLE'),'',0,'C',true);
      $pdf->Cell(14,4,utf8_decode('% IVA'),'',0,'C',true);
      $pdf->Cell(32,4,utf8_decode('CTA. IVA'),'',0,'C',true);
      $pdf->Cell(31,4,utf8_decode('REC. EQUIVALENCIA'),'',0,'C',true);
      $pdf->Cell(30,4,utf8_decode('TOTAL'),'',0,'C',true);
      $pdf->Ln(4.1);
      $pdf->SetX(10);
      //$pdf->SetFillColor(248, 250, 255);
      $pdf->SetFillColor(248, 250, 255);
      $pdf->SetDrawColor(248, 250, 255);
      $pdf->Cell(26,8,utf8_decode(''),'TB',0,'C',true);
      $pdf->Cell(23,8,utf8_decode(''),'TB',0,'C',false);
      $pdf->Cell(34,8,utf8_decode(''),'TB',0,'C',true);
      $pdf->Cell(14,8,utf8_decode(''),'TB',0,'C',false);
      $pdf->Cell(32,8,utf8_decode(''),'TB',0,'C',true);
      $pdf->Cell(31,8,utf8_decode(''),'TB',0,'C',false);
      $pdf->Cell(30,8,utf8_decode(''),'TB',0,'C',true);
      $pdf->SetY(-29);
      $pdf->SetX(10);
      $pdf->Cell(190,23,utf8_decode(''),'B',0,'C',true);
      $pdf->SetFillColor(44,132,194);
      $pdf->Ln(22);
      $pdf->SetX(10);
      $pdf->Cell(190,1.5,utf8_decode(''),'',0,'C',true);
      $pdf->SetY(-51);
      $pdf->SetX(10);
      $pdf->SetFont('Arial','',6);
      $pdf->SetTextColor(75,75,75);
      $pdf->MultiCell(190, 3, utf8_decode('Los datos personales facilitados pasarán a formar parte de un fichero de clientes, para la gestión y facturación de sus pedidos. De acuerdo con la Ley Orgánica 15/1999 del 13 de diciembre, esta sociedad guarda las medidas de confidencialidad establecidas en dicha Ley. Usted podrá ejercer sus derechos de acceso, rectificación y cancelación en la dirección de esta Sociedad.'),0,'C', false);
      $pdf->TextWithRotation(8,235,utf8_decode('Industrial Campollano Ferretería S.L. inscrita en el Registro Mercantil de Albacete, Tomo 669, Libro 453, Folio 208, Sección 8ª, Hoja AB-9552, Inscripcción 1ª. CIF B02290443'), 90);*/
  }

  function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
  {
      $font_angle+=90+$txt_angle;
      $txt_angle*=M_PI/180;
      $font_angle*=M_PI/180;

      $txt_dx=cos($txt_angle);
      $txt_dy=sin($txt_angle);
      $font_dx=cos($font_angle);
      $font_dy=sin($font_angle);

      $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
      if ($this->ColorFlag)
          $s='q '.$this->TextColor.' '.$s.' Q';
      $this->_out($s);
  }

}
?>
