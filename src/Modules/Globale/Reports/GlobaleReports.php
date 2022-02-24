<?php
namespace App\Modules\Globale\Reports;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class GlobaleReports extends \FPDF
{
  public $user;
  public $image_path;
  public $monthNames=["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];
  public $workcenters=null;
  public $type;

  protected $NewPageGroup = false;   // variable indicating whether a new group was requested
  protected $PageGroups = array();   // variable containing the number of pages of the groups
  protected $CurrPageGroup;          // variable containing the alias of the current page group

  // create a new page group; call this before calling AddPage()
      function StartPageGroup($type='Venta')
      {
          $this->NewPageGroup = true;
          $this->type=$type;
      }

      // current page in the group
      function GroupPageNo()
      {
          return $this->PageGroups[$this->CurrPageGroup];
      }

      function getSize()
      {
          return $this->PageGroups;
      }

      // alias of the current page group -- will be replaced by the total number of pages in this group
      function PageGroupAlias()
      {
          return $this->CurrPageGroup;
      }

      function _beginpage($orientation, $size, $rotation)
      {
          parent::_beginpage($orientation, $size, $rotation);
          if($this->NewPageGroup)
          {
              // start a new group
              $n = sizeof($this->PageGroups)+1;
              $alias = "{nb$n}";
              $this->PageGroups[$alias] = 1;
              $this->CurrPageGroup = $alias;
              $this->NewPageGroup = false;
          }
          elseif($this->CurrPageGroup)
              $this->PageGroups[$this->CurrPageGroup]++;
      }

      function _putpages()
      {
          $nb = $this->page;
          if (!empty($this->PageGroups))
          {
              // do page number replacement
              foreach ($this->PageGroups as $k => $v)
              {
                  for ($n = 1; $n <= $nb; $n++)
                  {
                      $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
                  }
              }
          }
          parent::_putpages();
      }

  function Header()
  {
      $this->SetMargins(5,10,5);
      // Select Arial bold 15
      if(class_exists('App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId())){
        $customReportClass='App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId();
        $customReport=new $customReportClass();
        $customReport->Header($this,$this->CurOrientation);
      }else{
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
  }

  function docHeader($nameLeft, $nameRight, $infoLeft=null, $infoRight=null, $infoCenter=null){
    if(class_exists('App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId())){
      $customReportClass='App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId();
      $customReport=new $customReportClass();
      $customReport->docHeader($this, $this->CurOrientation, $nameLeft, $nameRight,$infoLeft, $infoRight, $infoCenter);
    }else {

    }
  }

  function docFooter($order, $columns=null,$data=null){
    if(class_exists('App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId())){
      $customReportClass='App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId();
      $customReport=new $customReportClass();
      $customReport->docFooter($this, $this->CurOrientation, $order, $columns,$data);
    }else {

    }
  }

  function Footer()
  {
    if(class_exists('App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId())){
      $customReportClass='App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId();
      $customReport=new $customReportClass();
      $customReport->Footer($this);
    }else{
      // Position at 1.5 cm from bottom
      $this->SetY(-10);
      // Arial italic 8
      $this->SetFont('Arial','I',8);
      // Page number
      $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }
  }

  function Table($data, $columns, $associative=false, $sizeFont=8)
  {
    if(class_exists('App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId())){
      $customReportClass='App\Modules\Globale\Reports\CustomReports\CustomReports_'.$this->user->getCompany()->getId();
      $customReport=new $customReportClass();
      return $customReport->Table($this, $data, $columns, $associative=false, $sizeFont, $this->CurOrientation);
    }else{
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
      $this->SetFont('Arial','',$sizeFont);
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
