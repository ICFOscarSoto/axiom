<?php
namespace App\Modules\Globale\Reports;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class GlobaleReports extends \FPDF
{
  public $user;
  public $image_path;


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
      $this->Cell(190,6,utf8_decode($company->getSocialname()),0,'R','R');
      $this->Ln(4);
      $this->SetFont('Arial','',8);
      //$this->Cell(100);
      $this->Cell(190,6,utf8_decode($company->getAddress()),0,'R','R');
      $this->Ln(4);
      $this->Cell(190,6,utf8_decode($company->getPostcode()." - ".$company->getCity()." - ".$company->getState()),0,'R','R');
      $this->Ln(4);
      $this->Cell(190,6,utf8_decode("CIF: ".$company->getVat()." - TFNO: ".$company->getPhone()),0,'R','R');
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

  function Table($data, $columns)
  {
      // Header
      $this->SetFont('Arial','B',10);
      //SetDrawColor(int r [, int g, int b]);
      $this->SetFillColor(210, 210, 210);
      $this->SetDrawColor(210, 210, 210);
      $this->SetTextColor(50,50,50);
      for($i=0;$i<count($columns);$i++)
          $this->Cell($columns[$i]["width"],5,$columns[$i]["name"],1,0,'C',true);
      $this->Ln();
      // Data
      $this->SetFont('Arial','',9);
      foreach($data as $key=>$row)
      {
        for($i=0;$i<count($columns);$i++){

          if($this->GetY()>=268){
            $this->Cell($columns[$i]["width"],5,  $row[$i],isset($columns[$i]["border"])?$columns[$i]["border"]:'LRB',0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L');
          }else{
            $this->Cell($columns[$i]["width"],5,   $row[$i],isset($columns[$i]["border"])?$columns[$i]["border"]:'LR',0,isset($columns[$i]["align"])?$columns[$i]["align"]:'L');

          }

        }
        array_shift ($data);
        if($this->GetY()>=268){
          $this->AddPage();
          return $data;
        }else $this->Ln();
      }
      // Closing line
      //$this->Cell(array_sum($w),0,'','T');
      return $data;
  }

}
