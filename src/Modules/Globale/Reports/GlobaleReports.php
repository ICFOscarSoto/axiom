<?php
namespace App\Modules\Globale\Reports;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class GlobaleReports extends \FPDF
{
  public $user;
  public $image_path;
  public $monthNames=["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];
  public $workcenters=null;

  function Header()
  {
      // Select Arial bold 15
      $image_path = $this->image_path.$this->user->getCompany()->getId().'-medium.png';
      if(file_exists($image_path))
        $this->Image($image_path,10,10, null,15,'PNG');
      $this->SetFont('Arial','B',15);
      // Move to the right
      //$this->Cell(100);
      // Framed title
      $this->SetTextColor(50,50,50);
      $this->SetFont('Arial','B',10);
      $company=$this->user->getCompany();
      $this->Cell(190,6,utf8_decode($this->workcenters==null?$company->getSocialname():$this->workcenters->getSocialname()),0,'R','R');
      $this->Ln(4);
      $this->SetFont('Arial','',8);
      //$this->Cell(100);
      $this->Cell(190,6,utf8_decode($this->workcenters==null?$company->getAddress():$this->workcenters->getAddress()),0,'R','R');
      $this->Ln(4);
      $this->Cell(190,6,utf8_decode(($this->workcenters==null?$company->getPostcode():$this->workcenters->getPostcode())." - ".($this->workcenters==null?$company->getCity():$this->workcenters->getCity())." - ".($this->workcenters==null?$company->getState():$this->workcenters->getState())),0,'R','R');
      $this->Ln(4);
      $this->Cell(190,6,utf8_decode("CIF: ".($this->workcenters==null?$company->getVat():$this->workcenters->getVat())." - TFNO: ".($this->workcenters==null?$company->getPhone():$this->workcenters->getPhone())),0,'R','R');
      $this->Ln(10);
  }
  function Footer()
  {
      // Position at 1.5 cm from bottom
      $this->SetY(-10);
      // Arial italic 8
      $this->SetFont('Arial','I',8);
      // Page number
      $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
  }

  function Table($data, $columns, $associative=false)
  {
      // Header
      $this->SetFont('Arial','B',10);
      //SetDrawColor(int r [, int g, int b]);
      $this->SetFillColor(210, 210, 210);
      $this->SetDrawColor(210, 210, 210);
      $this->SetTextColor(50,50,50);
      for($i=0;$i<count($columns);$i++)
          $this->Cell($columns[$i]["width"],5,utf8_decode(isset($columns[$i]["caption"])?$columns[$i]["caption"]:$columns[$i]["name"]),1,0,'C',true);
      $this->Ln();
      // Data
      $this->SetFont('Arial','',8);
      foreach($data as $key=>$row)
      {
        for($i=0;$i<count($columns);$i++){
          if($this->GetY()>=268){
            $this->Cell($columns[$i]["width"],5, utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]),isset($columns[$i]["border"])?$columns[$i]["border"]:'LRB',0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L');
          }else{
            $this->Cell($columns[$i]["width"],5, utf8_decode($associative?$row[$columns[$i]["name"]]:$row[$i]),isset($columns[$i]["border"])?$columns[$i]["border"]:'LR',0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L');
          }
        }
        array_shift ($data);
        if($this->GetY()>=268){
          $this->AddPage();
          return $data;
        }else $this->Ln(4);
      }
      // Closing line
      //$this->Cell(array_sum($w),0,'','T');
      return $data;
  }

  function TextWithDirection($x, $y, $txt, $direction='R')
  {
      if ($direction=='R')
          $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
      elseif ($direction=='L')
          $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
      elseif ($direction=='U')
          $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
      elseif ($direction=='D')
          $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
      else
          $s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
      if ($this->ColorFlag)
          $s='q '.$this->TextColor.' '.$s.' Q';
      $this->_out($s);
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
