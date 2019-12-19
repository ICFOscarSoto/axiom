<?php
namespace App\Modules\ERP\Reports;
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
use App\Modules\HR\Entity\HRVacations;
use App\Modules\HR\Entity\HRSickleaves;
use App\Modules\HR\Entity\HRHollidays;

use App\Modules\Globale\Reports\GlobaleReports;


class ERPPlazaVoucherReports
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


  private function WriteText($text)
  {
      $intPosIni = 0;
      $intPosFim = 0;
      if (strpos($text,'<')!==false && strpos($text,'[')!==false)
      {
          if (strpos($text,'<')<strpos($text,'['))
          {
              $this->pdf->Write(5,substr($text,0,strpos($text,'<')));
              $intPosIni = strpos($text,'<');
              $intPosFim = strpos($text,'>');
              $this->pdf->SetFont('','B');
              $this->pdf->Write(5,substr($text,$intPosIni+1,$intPosFim-$intPosIni-1));
              $this->pdf->SetFont('','');
              $this->WriteText(substr($text,$intPosFim+1,strlen($text)));
          }
          else
          {
              $this->pdf->Write(5,substr($text,0,strpos($text,'[')));
              $intPosIni = strpos($text,'[');
              $intPosFim = strpos($text,']');
              $w=$this->pdf->GetStringWidth('a')*($intPosFim-$intPosIni-1);
              $this->pdf->Cell($w,$this->pdf->FontSize+0.75,substr($text,$intPosIni+1,$intPosFim-$intPosIni-1),1,0,'');
              $this->WriteText(substr($text,$intPosFim+1,strlen($text)));
          }
      }
      else
      {
          if (strpos($text,'<')!==false)
          {
              $this->pdf->Write(5,substr($text,0,strpos($text,'<')));
              $intPosIni = strpos($text,'<');
              $intPosFim = strpos($text,'>');
              $this->pdf->SetFont('','B');
              $this->WriteText(substr($text,$intPosIni+1,$intPosFim-$intPosIni-1));
              $this->pdf->SetFont('','');
              $this->WriteText(substr($text,$intPosFim+1,strlen($text)));
          }
          elseif (strpos($text,'[')!==false)
          {
              $this->pdf->Write(5,substr($text,0,strpos($text,'[')));
              $intPosIni = strpos($text,'[');
              $intPosFim = strpos($text,']');
              $w=$this->pdf->GetStringWidth('a')*($intPosFim-$intPosIni-1);
              $this->pdf->Cell($w,$this->pdf->FontSize+0.75,substr($text,$intPosIni+1,$intPosFim-$intPosIni-1),1,0,'');
              $this->WriteText(substr($text,$intPosFim+1,strlen($text)));
          }
          else
          {
              $this->pdf->Write(5,$text);
          }

      }
  }


  private function docFooter($document){

  }

  private function docHeader($document){
    $this->pdf->SetDrawColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->SetFillColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    //$this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $x=$this->pdf->getX();
    $y=$this->pdf->getY();
    $this->pdf->setXY(0, 35);
    $this->pdf->SetFont('Arial','b',12);
    $this->pdf->Cell(110,9,utf8_decode('COMPRA EN PLAZA'),'',0,'C',false);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->setX(15);
    $this->pdf->Ln(7);
    $this->pdf->Cell(110,9,utf8_decode('Núm. Vale:  '.$document->getId()),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->Cell(110,9,utf8_decode('Fecha:  '.$document->getDateadd()->format("d/m/Y H:i:s")),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->Cell(110,9,utf8_decode("Dependiente:  ".$document->getUser()->getName().' '.$document->getUser()->getLastName()),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','b',10);
    $validDate=date('d/m/Y', strtotime('+2 weekday', $document->getDateadd()->getTimestamp()));
    $this->pdf->Cell(110,9,utf8_decode("Válido hasta:  ".$validDate),'',0,'L',false);


    $this->pdf->Ln(10);
    $this->pdf->Cell(30,9,utf8_decode("Recoger en:   "),'',0,'L',false);
    $this->pdf->SetFont('Arial','b',10);
    $this->pdf->Cell(150,9,utf8_decode($document->getSupplier()->getSocialName()),'',0,'L',false);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Ln(10);$this->pdf->setX(40);
    $this->pdf->MultiCell(150,4,utf8_decode($document->getSupplier()->getAddress()),0,'L',false);
    $this->pdf->setX(40);
    $this->pdf->MultiCell(150,4,utf8_decode($document->getSupplier()->getCity().' '.$document->getSupplier()->getPostcode().' '.($document->getSupplier()->getState()?$document->getSupplier()->getState()->getName():'')),0,'L',false);


    $this->pdf->setXY(115, 15);
    $this->pdf->SetFont('Arial','b',10);
    $this->pdf->Cell(90,9,utf8_decode($document->getSupplier()->getSocialName()),'',0,'L',false);
    $this->pdf->Ln(7);$this->pdf->setX(115);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->MultiCell(90,4,utf8_decode($document->getSupplier()->getAddress()),0,'L',false);
    $this->pdf->setX(115);
    $this->pdf->MultiCell(90,4,utf8_decode($document->getSupplier()->getCity().' '.$document->getSupplier()->getPostcode().' '.($document->getSupplier()->getState()?$document->getSupplier()->getState()->getName():'')),0,'L',false);

    $this->pdf->setXY(10, 85);
    //$this->pdf->MultiCell(180,4,utf8_decode('Por la presente les autorizo a hacer entrega de material a la empresa <b>'.$document->getCustomer()->getSocialName().'</b>' ),0,'L',false);
    $this->WriteText(utf8_decode('Por la presente les autorizo a hacer entrega de material a la empresa <'.$document->getCustomer()->getSocialName().'>. Retirará el material <'.$document->getPickupperson().'>. Ruego envien el albarán del material retirado a la atención de <'.$document->getUser()->getName().' '.$document->getUser()->getLastName().'> a la dirección de correo electrónico <'.$document->getUser()->getEmail().'>
    '));

    $this->pdf->Ln(2);
    $this->pdf->setX(115);
    $this->pdf->Cell(90,9,utf8_decode('En '.$this->user->getCompany()->getCity().' a '.$document->getDateadd()->format("d").' de '.strftime("%B",$document->getDateadd()->getTimestamp()).' de '.$document->getDateadd()->format("Y")),'',0,'L',false);
    $this->pdf->Ln(20);
    $this->pdf->setX(115);
    $this->pdf->Cell(90,9,utf8_decode('Fdo. '.$document->getUser()->getName().' '.$document->getUser()->getLastName()),'',0,'L',false);
    //$this->pdf->SetDrawColor(0, 0, 0);
    /*$this->pdf->Cell(22,9,utf8_decode('Nº Presupuesto'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($document->getCode()),'',0,'L',false);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->Ln(4);
    $this->pdf->Cell(22,9,utf8_decode('Fecha'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($document->getDate()->format("d/m/Y")),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->SetTextColor($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b);
    $this->pdf->Cell(22,9,utf8_decode('Válido hasta:'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($document->getDateofferend()->format("d/m/Y")),'',0,'L',false);
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
    $this->pdf->Cell(60,9,utf8_decode('PRESUPUESTO'),'',0,'L',false);
    $this->pdf->setXY($x, $y);*/
  }

  function create($params, $dest='I', $file=null){
    setlocale( LC_ALL, 'es_ES' );
    $this->pdf  = new \FPDF();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $document=$params["document"];
    $this->configuration=$params["configuration"];
    list($this->bgcolor_r, $this->bgcolor_g, $this->bgcolor_b) = sscanf($this->configuration->getBgcolor(), "#%02x%02x%02x");
    list($this->shadowcolor_r, $this->shadowcolor_g, $this->shadowcolor_b) = sscanf($this->configuration->getShadowcolor(), "#%02x%02x%02x");

      $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
      $this->pdf->user=$params["user"];
      $this->pdf->AddPage('L','A5');

      $this->docHeader($document);
      $this->docFooter($document);


    $this->pdf->Output($dest, $file==null?$document->getCode().".pdf":$file);

}
}
