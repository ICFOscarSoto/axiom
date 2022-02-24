<?php
namespace App\Modules\Globale\Reports\CustomReports;

class CustomReports_2{

  function Header($pdf,$orientation='P')
  {
    if ($orientation=='P') {
      $image_path = $pdf->image_path.$pdf->user->getCompany()->getId().'-medium.png';
      if(file_exists($image_path))
        $pdf->Image($image_path,10,10, null,15,'PNG','https://www.ferreteriacampollano.com/');
      $pdf->SetFont('Arial','B',15);
      if ($pdf->type=='Venta') $pdf->Image($pdf->image_path.'../resources/logo-bextok.png',150,1, null,30,'PNG');
      else $pdf->Image($pdf->image_path.'../resources/logo-aside.png',150,3, null,18,'PNG');
      $pdf->Image($pdf->image_path.'../resources/logo-iso.png',185,6, null,20,'PNG');
      $pdf->SetTextColor(44,132,194);
      $pdf->SetFont('Arial','B',8);
      $company=$pdf->user->getCompany();
      $pdf->Cell(190,4,strtoupper(utf8_decode($company->getSocialname())),0,'R','C');
      $pdf->Ln(4);
      $pdf->SetFont('Arial','',8);
      $pdf->Cell(200,4,utf8_decode($company->getAddress()),0,'R','C');
      $pdf->Ln(3);
      $pdf->Cell(200,4,utf8_decode('Tlfno: 967 240 112 - Fax: 967 213 207'),0,'R','C');
      $pdf->Ln(3);
      $pdf->Cell(200,4,utf8_decode($company->getPostcode()." - ".$company->getCity()." - ".$company->getState()),0,'R','C');
      $pdf->Ln(3);
      $pdf->Cell(200,4,utf8_decode("CIF: ".$company->getVat())." - Apdo Correos: 5140",0,'R','C');
      $pdf->Ln(10);
    }
    else {
      $image_path = $pdf->image_path.$pdf->user->getCompany()->getId().'-medium.png';
      if(file_exists($image_path))
        $pdf->Image($image_path,10,3,60,15,'PNG','https://www.ferreteriacampollano.com/');
      $pdf->SetFont('Arial','B',15);
      if ($pdf->type=='Venta') $pdf->Image($pdf->image_path.'../resources/logo-bextok.png',248,-2, null,26,'PNG');
      else $pdf->Image($pdf->image_path.'../resources/logo-aside.png',253,3, null,18,'PNG');
      $pdf->Image($pdf->image_path.'../resources/logo-iso.png',275,3, null,18,'PNG');
      // Framed title
      $pdf->SetTextColor(44,132,194);
      $pdf->SetFont('Arial','B',8);
      $company=$pdf->user->getCompany();
      $pdf->setXY(75, 5);
      $pdf->Cell(70,4,strtoupper(utf8_decode($company->getSocialname())),0,'L','C');
      $pdf->SetFont('Arial','',8);
      //$pdf->Cell(100);
      $pdf->Cell(100,4,utf8_decode($company->getAddress()),0,'R','C');
      $pdf->setXY(75, 8.5);
      $pdf->Cell(70,4,utf8_decode("CIF: ".$company->getVat())." - Apdo Correos: 5140",0,'L','C');
      $pdf->Cell(100,4,utf8_decode('Tlfno: 967 240 112 - Fax: 967 213 207'),0,'R','C');
      $pdf->setXY(145, 12);
      $pdf->Cell(100,4,utf8_decode($company->getPostcode()." - ".$company->getCity()." - ".$company->getState()),0,'R','C');
    }

  }


  function docHeader($pdf, $orientation='P', $nameLeft=null, $nameRight=null, $infoLeft=null, $infoRight=null, $infoCenter=null){
    if ($orientation=='P') {
      $pdf->SetDrawColor(44, 132, 194);
      $pdf->SetFillColor(44, 132, 194);
      $pdf->SetTextColor(44, 132, 194);
      $pdf->Rect(100, 34, 1.5, 28,'DF');
      $pdf->SetTextColor(44, 132, 194);
      $pdf->setXY(10, 20);
      $pdf->SetFont('Arial','',12);
      $pdf->Cell(105,30,utf8_decode($nameLeft),'',0,'L',false);
      $pdf->SetFont('Arial','',9);

      $x=10;
      $y=40;
      $pdf->setXY($x, $y);
      //$pdf->Cell(27,9,utf8_decode($nameLeft),'',0,'L',false);
      if ($infoLeft!=null) {
        foreach ($infoLeft as $left){
          $pdf->SetTextColor(44, 132, 194);
          $pdf->Cell(27,9,utf8_decode($left[0]),'',0,'L',false);
          $pdf->SetTextColor(0, 0, 0);
          $info=$left[1];
          $a=0;
          if (strlen($left[1])<40) {
            $pdf->Cell(60,9,ucwords(strtolower(ltrim(utf8_decode($left[1])))),'',0,'L',false);
            $y=$y+4;}
          else {
            $p=(ceil(strlen($info)/40))*3.7;
            $pdf->setXY($x+27, $y+2);
            $pdf->MultiCell(60,3.7,ucwords(strtolower(ltrim(utf8_decode($left[1])))),0,'L',false);
            $y=$y+$p;
          }
        $pdf->setXY($x, $y);
        }
      }
      $x=200;
      $y=24;
      $pdf->setXY($x, $y);
      if($infoRight!=null){
        foreach ($infoRight as $right){
          $pdf->SetTextColor(44, 132, 194);
          $pdf->Cell(27,9,utf8_decode($right[0]),'',0,'L',false);
          $pdf->SetTextColor(0, 0, 0);
          $info=$right[1];
          $a=0;
          if (strlen($right[1])<40) {
            $pdf->Cell(60,9,ucwords(strtolower(ltrim(utf8_decode($right[1])))),'',0,'L',false);
            $y=$y+4;}
          else {
            $p=(ceil(strlen($info)/40))*3.7;
            $pdf->setXY($x+27, $y+2);
            $pdf->MultiCell(60,3.7,ucwords(strtolower(ltrim(utf8_decode($right[1])))),0,'L',false);
            $y=$y+$p;
          }
        $pdf->setXY($x, $y);
        }
      }

    } else {
      $pdf->SetDrawColor(44, 132, 194);
      $pdf->SetFillColor(44, 132, 194);
      $pdf->SetTextColor(44, 132, 194);
      $pdf->Rect(95, 26, 1.5, 22,'DF');
      $pdf->Rect(195, 26, 1.5, 22,'DF');

      $pdf->SetTextColor(44, 132, 194);
      $pdf->setXY(10, 7);
      $pdf->SetFont('Arial','',12);
      $pdf->Cell(105,30,utf8_decode($nameLeft),'',0,'L',false);
      if($nameRight!=null){
        $pdf->SetFont('Arial','',12);
        $pdf->setXY(200, 7);
        $pdf->Cell(105,30,utf8_decode($nameRight),'',0,'L',false);
      }


      $pdf->SetFont('Arial','',9);
      $x=10;
      $y=24;
      $pdf->setXY($x, $y);
      if ($infoLeft!=null) {
        foreach ($infoLeft as $left){
          $pdf->SetTextColor(44, 132, 194);
          $pdf->Cell(27,9,utf8_decode($left[0]),'',0,'L',false);
          $pdf->SetTextColor(0, 0, 0);
          $info=$left[1];
          $a=0;
          if (strlen($left[1])<40) {
            $pdf->Cell(60,9,ucwords(strtolower(ltrim(utf8_decode($left[1])))),'',0,'L',false);
            $y=$y+4;}
          else {
            $p=(ceil(strlen($info)/40))*3.7;
            $pdf->setXY($x+27, $y+2);
            $pdf->MultiCell(60,3.7,ucwords(strtolower(ltrim(utf8_decode($left[1])))),0,'L',false);
            $y=$y+$p;
          }
        $pdf->setXY($x, $y);
        }
      }
      $x=100;
      $y=24;
      $pdf->setXY($x, $y);
      if($infoCenter!=null){
        foreach ($infoCenter as $center){
          $pdf->SetTextColor(44, 132, 194);
          $pdf->Cell(27,9,utf8_decode($center[0]),'',0,'L',false);
          $pdf->SetTextColor(0, 0, 0);
          $info=$center[1];
          $a=0;
          if (strlen($center[1])<40) {
            $pdf->Cell(60,9,ucwords(strtolower(ltrim(utf8_decode($center[1])))),'',0,'L',false);
            $y=$y+4;}
          else {
            $p=(ceil(strlen($info)/40))*3.7;
            $pdf->setXY($x+27, $y+2);
            $pdf->MultiCell(60,3.7,ucwords(strtolower(ltrim(utf8_decode($center[1])))),0,'L',false);
            $y=$y+$p;
          }
        $pdf->setXY($x, $y);
        }
      }
      $x=200;
      $y=24;
      $pdf->setXY($x, $y);
      if($infoRight!=null){
        foreach ($infoRight as $right){
          $pdf->SetTextColor(44, 132, 194);
          $pdf->Cell(27,9,utf8_decode($right[0]),'',0,'L',false);
          $pdf->SetTextColor(0, 0, 0);
          $info=$right[1];
          $a=0;
          if (strlen($right[1])<40) {
            $pdf->Cell(60,9,ucwords(strtolower(ltrim(utf8_decode($right[1])))),'',0,'L',false);
            $y=$y+4;}
          else {
            $p=(ceil(strlen($info)/40))*3.7;
            $pdf->setXY($x+27, $y+2);
            $pdf->MultiCell(60,3.7,ucwords(strtolower(ltrim(utf8_decode($right[1])))),0,'L',false);
            $y=$y+$p;
          }
        $pdf->setXY($x, $y);
        }
      }
    }
  }

  function docFooter($pdf, $orientation='P', $order, $columns=null,$data=null){
    if ($orientation=='P') {
    } else {
      $x=$pdf->getX();
      $y=$pdf->getY();

      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','B',10);
      $pdf->SetY(-48);
      $pdf->SetX(34);
      $pdf->SetFillColor(248, 250, 255);
      $pdf->SetDrawColor(248, 250, 255);
      if($pdf->type=='Compra')$pdf->Cell(170,4,utf8_decode('Rogamos que antes del envio del pedido nos manden confirmación referente a cantidad, precios, plazos y direccion de entrega del pedido'),'',0,'L',true);
        $pdf->SetFillColor(44, 132, 194);
        $pdf->SetDrawColor(44, 132, 194);
        $pdf->SetTextColor(255,255,255);
        $pdf->SetFont('Arial','',7);
        $pdf->SetY(-43);
        $pdf->SetX(31);
        for($i=0;$i<count($columns);$i++){
            $width=isset($columns[$i]["width"])?$columns[$i]["width"]:30;
            $pdf->Cell($width,4,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),'TBRL',0,'C',true);
        }
        $pdf->Ln(4.1);
        $pdf->SetX(31);
        $pdf->setTextColor(0,0,0);
        $pdf->SetFont('Arial','',9);
        $pdf->SetFillColor(248, 250, 255);
        $pdf->SetDrawColor(248, 250, 255);
        for($i=0;$i<count($columns);$i++){
          $width=isset($columns[$i]["width"])?$columns[$i]["width"]:30;
          $align=isset($columns[$i]["align"])?$columns[$i]["align"]:'C';
          $bold=isset($columns[$i]["bold"])?$columns[$i]["bold"]:'';
          $pdf->SetFont('Arial',$bold,9);
          $pdf->Cell($width,5, utf8_decode($data[$i]), '',0,$align,true);
        }

      $pdf->setTextColor(0,0,0);
      $pdf->SetFont('Arial','',7.5);
      $pdf->Ln(12);
      $pdf->SetX(10);
      $pdf->Cell(40,3,utf8_decode('* Cualquier material no enviado a la dirección solicitada será devuelto sin hacernos cargo de ningún importe.'),'B',0,'L',true);
      $pdf->Ln(3);
      $pdf->SetX(10);
      $pdf->Cell(290,3,utf8_decode('* Le informamos que nuestro personal supervisa los pedidos que nos envían para valorar el grado de cumplimiento con nuestros requisitos de calidad. Esta revisión será utilizada para la evaluación periódica como proveedor homologado.'),'',0,'L',true);
      $pdf->Ln(3);
      $pdf->SetX(10);
      $pdf->Cell(40,3,utf8_decode('* No nos haremos cargo de aquellos artículos cuya caducidad sea inferior al 50% de su vida útil.'),'B',0,'L',true);
      $pdf->Ln(6);
      $pdf->SetFont('Arial','',6.5);
      $pdf->SetTextColor(75,75,75);
      $pdf->SetX(10);
      $pdf->MultiCell(260, 3, utf8_decode('Los datos personales facilitados pasarán a formar parte de un fichero de clientes, para la gestión y facturación de sus pedidos. De acuerdo con la Ley Orgánica 15/1999 del 13 de diciembre, esta sociedad guarda las medidas de confidencialidad establecidas en dicha Ley. Usted podrá ejercer sus derechos de acceso, rectificación y cancelación en la dirección de esta Sociedad.'),0,'C', false);

      $pdf->SetXY(270, 198);
      $pdf->SetTextColor(44, 132, 194);
      $pdf->SetFont('Arial','',9);
      $pdf->Cell(15,9,utf8_decode('Página'),'',0,'L',false);
      $pdf->SetTextColor(0, 0, 0);
      $pdf->Cell(20,9,utf8_decode($pdf->GroupPageNo().'/'.$pdf->PageGroupAlias()),'',0,'L',false);
      $pdf->SetTextColor(0, 0, 0);


      $pdf->SetFont('Arial','',6.5);
      $pdf->SetXY(0,0);
      $pdf->TextWithRotation(6,192,utf8_decode('Industrial Campollano Ferretería S.L. inscrita en el Registro Mercantil de Albacete, Tomo 669, Libro 453, Folio 208, Sección 8ª, Hoja AB-9552, Inscripcción 1ª. CIF B02290443'), 90);
      $pdf->setXY($x, $y);
    }

  }

  function Table($pdf, $data, $columns, $associative=false, $sizeFont=8, $orientation='P'){
      // Header
      $pdf->SetFont('Arial','',7);
      //SetDrawColor(int r [, int g, int b]);
      $pdf->SetFillColor(44, 132, 194);
      $pdf->SetDrawColor(44, 132, 194);
      $pdf->SetTextColor(255,255,255);
      $widthTable=0;
      for($i=0;$i<count($columns);$i++){
        $width=isset($columns[$i]["width"])?$columns[$i]["width"]:30;
        $widthTable=$widthTable+$width;
      }
      if ($orientation=='P') {
        $x=(205-$widthTable)/2;
        if ($x<10) $x=10;
        $pdf->SetXY($x, 75);
      }
      else $pdf->SetXY(10,52);
      for($i=0;$i<count($columns);$i++){
          $width=isset($columns[$i]["width"])?$columns[$i]["width"]:30;
          $pdf->Cell($columns[$i]["width"],4,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),'TBRL',0,'C',true);
      }
      $pdf->Ln();
      // Data
      $pdf->SetTextColor(0,0,0);
      if ($orientation=='P') $sizeLines=232;
      else $sizeLines=150;
      foreach($data as $key=>$row)
      {
        if ($orientation=='P') $pdf->SetX($x);
        else $pdf->SetX(10);
        for($i=0;$i<count($columns);$i++){
          $pdf->SetDrawColor(248, 250, 255);
          if($i==count($columns)-1) $binfoLeft='R'; else $binfoLeft='';
          $text=utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]);
          if(strpos($text,'#b#')===0){ $pdf->SetFont('Arial','b',8); $text=substr($text, 3);}else $pdf->SetFont('Arial','',$sizeFont);
          if($pdf->GetY()>=268){
            if($i%2) $pdf->SetFillColor(255, 255, 255);
            else $pdf->SetFillColor(248, 250, 255);//$pdf->SetFillColor(207, 225, 255); //$pdf->SetFillColor(234, 246, 255);
            $pdf->Cell($columns[$i]["width"],5, $text, $binfoLeft,0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L',true);
          }else{
            if($i%2) $pdf->SetFillColor(255, 255, 255);
            else $pdf->SetFillColor(248, 250, 255);
            $pdf->Cell($columns[$i]["width"],5, $text, $binfoLeft,0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L',true);
          }
        }
        array_shift ($data);
        $pdf->SetFillColor(44, 132, 194);
        if($pdf->GetY()>=$sizeLines){
          if ($orientation=='P') {
            $pdf->SetX(10);
            $pdf->AddPage();
          }
          else {
            $pdf->SetX(10);
            $pdf->AddPage('L');
          }
          return $data;
        }else $pdf->Ln(4);
      }

      while($pdf->GetY()<$sizeLines){
        $pdf->SetX($x);
        for($i=0;$i<count($columns);$i++){
          if($i%2) $pdf->SetFillColor(255, 255, 255);
          else $pdf->SetFillColor(248, 250, 255);
          $pdf->Cell($columns[$i]["width"],2, "", "",0,'L',true);

        }
        $pdf->Ln(2);
      }

      return $data;
  }

  function Footer($pdf)
  {}

  function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
  {
      $font_angle+=90+$txt_angle;
      $txt_angle*=M_PI/180;
      $font_angle*=M_PI/180;

      $txt_dx=cos($txt_angle);
      $txt_dy=sin($txt_angle);
      $font_dx=cos($font_angle);
      $font_dy=sin($font_angle);

      $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$k,($h-$y)*$k,$_escape($txt));
      if ($ColorFlag)
          $s='q '.$TextColor.' '.$s.' Q';
      $_out($s);
  }

}
?>
