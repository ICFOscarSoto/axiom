<?php
namespace App\Modules\Globale\Reports;
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

class GlobaleSEPAReports
{
  private $pdf;
  private $user;
  private $company;
  private $type;

  private function secToH($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    return sprintf("%02d", $hours).":".sprintf("%02d", $minutes).":".sprintf("%02d", $seconds);
  }

  private function docCreditor(){
    $company=$this->user->getCompany();

    $this->pdf->SetFillColor(210, 210, 210);
    $this->pdf->SetDrawColor(210, 210, 210);
    $this->pdf->SetTextColor(50,50,50);
    $this->pdf->SetFont('Arial','B',11);
    $this->pdf->Cell(190,6,utf8_decode("Orden de domiciliación de adeudo directo SEPA"),'TRL','R','C');
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(190,6,utf8_decode("SEPA Direct Debit Mandate"),'BLR','R','C');
    $this->pdf->Ln(10);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','TL','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Referencia de la orden de domiciliación: "),'TL','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode(" "),'TR','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Mandate reference"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Identificador del acreedor: "),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($company->getBankaccount()->getCreditoridentifier()),'R','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Creditor identifier"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Nombre del acreedor: "),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($company->getSocialname()),'R','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Creditor's name"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Dirección: "),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($company->getAddress()),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Address"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Código postal - Población - Provincia: "),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($company->getPostcode()." - ".$company->getCity()." - ".$company->getState()),'R','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Postal code - City - Town"),'RL','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(5);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("País"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($company->getCountry()->getName()),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RLB','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Country"),'RLB','R','L');

    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->TextWithDirection(14,97,'A cumplimentar por el acreedor','U');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->TextWithDirection(17,93,'To be completed by the creditor','U');




  }

  private function docDebtor(){
    $company=$this->user->getCompany();
    $this->pdf->SetFillColor(210, 210, 210);
    $this->pdf->SetDrawColor(210, 210, 210);
    $this->pdf->SetTextColor(50,50,50);

    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','TL','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Nombre del deudor: "),'TL','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($this->company->getSocialname()),'TR','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Debtor's name"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Dirección del deudor: "),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($this->company->getAddress()),'R','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Address of the debtor"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Código postal - Población - Provincia:"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($this->company->getPostcode()." - ".$this->company->getCity()." - ".$this->company->getState()),'R','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Postal code - City - Town"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("País del deudor: "),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($this->company->getCountry()->getName()),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Country of the debtor"),'RL','R','L');
    $this->pdf->Ln(5);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Swift BIC (8 u 11 posiciones)"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($this->company->getBankaccount()->getSwiftcode()),'R','R','L');
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Swift BIC (up to 8 or 11 characters)"),'RL','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(5);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Número de cuenta - IBAN"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode($this->company->getBankaccount()->getIban()),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Account number - IBAN"),'RL','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(5);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Tipo de pago"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,6,utf8_decode('Pago recurrente'),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Type of payment"),'RL','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(5);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,6,utf8_decode("Fecha - Localidad"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    setlocale(LC_ALL,"es_ES");
    $date = new \DateTime();
    //echo strftime("%A",$date->getTimestamp());
    $this->pdf->Cell(110,6,utf8_decode('En Albacete a '.strftime("%A %d de ",$date->getTimestamp()).$this->pdf->monthNames[date('n')-1])." de ".date('Y'),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,6,'','RL','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,6,utf8_decode("Date - location in which you are signing"),'RL','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(5);
    $this->pdf->Cell(10,6,'','L','','',true);
    $this->pdf->Cell(70,36,utf8_decode("Firma del deudor"),'L','R','L');
    $this->pdf->SetFont('Arial','',10);
    $this->pdf->Cell(110,36,utf8_decode(''),'R','R','L');
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(10,36,'','RLB','','',true);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->Cell(180,36,utf8_decode("Signature of the debtor"),'RLB','R','L');


    $this->pdf->Ln(7);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->TextWithDirection(14,222,'A cumplimentar por el deudor','U');
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->TextWithDirection(17,220,'To be completed by the debtor','U');
  }

  function create($params){
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $doctrine=$params["doctrine"];
    $this->company=$params["company"];
    $this->type=$params["type"];
    $this->user=$params["user"];
    $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
    $this->pdf->user=$params["user"];
    $this->pdf->AddPage();
    $this->docCreditor();
    $this->pdf->Ln(11);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->MultiCell(190,3,utf8_decode('Mediante la firma de esta orden de domiciliación, el deudor autoriza (A) al acreedor a enviar instrucciones a la entidad del deudor para adeudar su cuenta y (B) a la entidad para efectuar los adeudos en su cuenta siguiendo las instrucciones del acreedor. Como parte de sus derechos, el deudor está legitimado al reembolso por su entidad en los términos y condiciones del contrato suscrito con la misma. La solicitud de reembolso deberá efectuarse dentro de las ocho semanas que siguen a la fecha de adeudo en cuenta. Puede obtener información adicional sobre sus derechos en su entidad financiera.'));
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->MultiCell(190,3,utf8_decode('By signing this mandate form, you authorise (A) the Creditor to send instructions to your bank to debit your account and (B) your bank to debit your account in accordance with the instructions from the Creditor. As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within eigth weeks starting from the date on which your account was debited. Your rights are explained in a statement that you can obtain from your bank.'));
    $this->pdf->Ln(8);
    $this->docDebtor();
    $this->pdf->Ln(35);
    $this->pdf->SetFont('Arial','B',9);
    $this->pdf->Cell(190,6,utf8_decode("TODOS LOS CAMPOS HAN DE SER CUMPLIMENTADOS OBLIGATORIAMENTE"),'','R','C');
    $this->pdf->Ln(4);
    $this->pdf->Cell(190,6,utf8_decode("UNA VEZ FIRMADA ESTA ORDEN DE DOMICILIACIÓN DEBE SER ENVIADA AL ACREEDOR PARA SU CUSTODIA"),'','R','C');
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(190,6,utf8_decode("ALL GAPS ARE MANDATORY. ONCE THIS MANDATE HAS BEEN SIGNED MUST BE SENT TO CREDITOR FOR STORAGE"),'','R','C');



    return $this->pdf->Output();
}
}
