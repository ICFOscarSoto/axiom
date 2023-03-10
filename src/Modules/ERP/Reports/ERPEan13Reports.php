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
use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;
use BigFish\PDF417\Renderers\SvgRenderer;

use \FPDF;
use App\Modules\Globale\Reports\GlobaleReports;

class PDF_EAN13 extends \FPDF
{
  function EAN13($x, $y, $barcode, $h=16, $w=.35, $noValidate){
	   $this->Barcode($x,$y,$barcode,$h,$w,13,$noValidate);
  }

  function UPC_A($x, $y, $barcode, $h=16, $w=.35){
  	$this->Barcode($x,$y,$barcode,$h,$w,12);
  }

  function GetCheckDigit($barcode){
  	//Compute the check digit
  	$sum=0;
  	for($i=1;$i<=11;$i+=2)
  		$sum+=3*$barcode[$i];
  	for($i=0;$i<=10;$i+=2)
  		$sum+=$barcode[$i];
  	$r=$sum%10;
  	if($r>0)
  		$r=10-$r;
  	return $r;
  }

  function TestCheckDigit($barcode){
  	//Test validity of check digit
  	$sum=0;
  	for($i=1;$i<=11;$i+=2)
  		$sum+=3*$barcode[$i];
  	for($i=0;$i<=10;$i+=2)
  		$sum+=$barcode[$i];
  	return ($sum+$barcode[12])%10==0;
  }

  function Barcode($x, $y, $barcode, $h, $w, $len, $noValidate){
  	//Padding
  	$barcode=str_pad($barcode,$len-1,'0',STR_PAD_LEFT);
  	if($len==12)
  		$barcode='0'.$barcode;
  	//Add or control the check digit
  	if(strlen($barcode)==12)
  		$barcode.=$this->GetCheckDigit($barcode);
  	elseif(!$this->TestCheckDigit($barcode) and $noValidate==false)
  		$this->Error('Incorrect check digit');
  	//Convert digits to bars
  	$codes=array(
  		'A'=>array(
  			'0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011',
  			'5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'),
  		'B'=>array(
  			'0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101',
  			'5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'),
  		'C'=>array(
  			'0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100',
  			'5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100')
  		);
  	$parities=array(
  		'0'=>array('A','A','A','A','A','A'),
  		'1'=>array('A','A','B','A','B','B'),
  		'2'=>array('A','A','B','B','A','B'),
  		'3'=>array('A','A','B','B','B','A'),
  		'4'=>array('A','B','A','A','B','B'),
  		'5'=>array('A','B','B','A','A','B'),
  		'6'=>array('A','B','B','B','A','A'),
  		'7'=>array('A','B','A','B','A','B'),
  		'8'=>array('A','B','A','B','B','A'),
  		'9'=>array('A','B','B','A','B','A')
  		);
  	$code='101';
  	$p=$parities[$barcode[0]];
  	for($i=1;$i<=6;$i++) $code.=$codes[$p[$i-1]][$barcode[$i]];
  	$code.='01010';
  	for($i=7;$i<=12;$i++)	$code.=$codes['C'][$barcode[$i]];
  	$code.='101';
  	//Draw bars
  	for($i=0;$i<strlen($code);$i++){
  		if($code[$i]=='1')
  			$this->Rect($x+$i*$w,$y,$w,$h,'F');
  	}
  	//Print text uder barcode
  	$this->SetFont('Arial','',12);
    //$this->SetXY(0,-15);
    $this->SetXY(5,0.3);
  	//$this->Cell(62,6,substr($barcode,-$len),0,0,'L');
    $this->Cell(40,6,substr($barcode,-$len),0,0,'C');
  }
}


/**
 * @class PDF417
 * Class to create PDF417 barcode arrays for TCPDF class.
 * PDF417 (ISO/IEC 15438:2006) is a 2-dimensional stacked bar code created by Symbol Technologies in 1991.
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 1.0.004_PHP4
 */
class PDF_417 extends \FPDF{
  function pdf417($params){
    $pdf417 = new \BigFish\PDF417\PDF417();
    $data = $pdf417->encode($params["barcode"]);
    $renderer = new \BigFish\PDF417\Renderers\ImageRenderer([
    'format' => 'png',
    'quality' => 100,
    'scale' => 1,
    'ratio'=>14
    ]);
    $this->SetFont('Arial','',12);
    //$this->SetXY(0,-15);
  	//$this->Cell(62,6,$code,0,0,'C');

    $image = $renderer->render($data);
    $tempPath=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$params["user"]->getId();
    if (!file_exists($tempPath) && !is_dir($tempPath)) {
  			mkdir($tempPath, 0775, true);
  	}
    $code=preg_replace('([^A-Za-z0-9])', '', $params["code"]);;
    $image->save($tempPath.DIRECTORY_SEPARATOR.$code.'.png');
    $this->Image($tempPath.DIRECTORY_SEPARATOR.$code.'.png', 1, 3);
    $this->SetXY(0,2);
    if(substr($params["barcode"],0,1)=="p")
      $this->Cell(62,4,"ITEM ".substr($params["barcode"],2),0,0,'C');
    if(substr($params["barcode"],0,1)=="v")
      $this->Cell(62,4,"VAR ".substr($params["barcode"],2),0,0,'C');
    $this->SetFillColor(0);
    unlink($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$params["user"]->getId().DIRECTORY_SEPARATOR.$params["code"].'.png');
  }
} // end PDF417 class


class PDF_Code39 extends FPDF
{
function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5){

    $wide = $baseline;
    $narrow = $baseline / 3 ;
    $gap = $narrow;

    $barChar['0'] = 'nnnwwnwnn';
    $barChar['1'] = 'wnnwnnnnw';
    $barChar['2'] = 'nnwwnnnnw';
    $barChar['3'] = 'wnwwnnnnn';
    $barChar['4'] = 'nnnwwnnnw';
    $barChar['5'] = 'wnnwwnnnn';
    $barChar['6'] = 'nnwwwnnnn';
    $barChar['7'] = 'nnnwnnwnw';
    $barChar['8'] = 'wnnwnnwnn';
    $barChar['9'] = 'nnwwnnwnn';
    $barChar['A'] = 'wnnnnwnnw';
    $barChar['B'] = 'nnwnnwnnw';
    $barChar['C'] = 'wnwnnwnnn';
    $barChar['D'] = 'nnnnwwnnw';
    $barChar['E'] = 'wnnnwwnnn';
    $barChar['F'] = 'nnwnwwnnn';
    $barChar['G'] = 'nnnnnwwnw';
    $barChar['H'] = 'wnnnnwwnn';
    $barChar['I'] = 'nnwnnwwnn';
    $barChar['J'] = 'nnnnwwwnn';
    $barChar['K'] = 'wnnnnnnww';
    $barChar['L'] = 'nnwnnnnww';
    $barChar['M'] = 'wnwnnnnwn';
    $barChar['N'] = 'nnnnwnnww';
    $barChar['O'] = 'wnnnwnnwn';
    $barChar['P'] = 'nnwnwnnwn';
    $barChar['Q'] = 'nnnnnnwww';
    $barChar['R'] = 'wnnnnnwwn';
    $barChar['S'] = 'nnwnnnwwn';
    $barChar['T'] = 'nnnnwnwwn';
    $barChar['U'] = 'wwnnnnnnw';
    $barChar['V'] = 'nwwnnnnnw';
    $barChar['W'] = 'wwwnnnnnn';
    $barChar['X'] = 'nwnnwnnnw';
    $barChar['Y'] = 'wwnnwnnnn';
    $barChar['Z'] = 'nwwnwnnnn';
    $barChar['-'] = 'nwnnnnwnw';
    $barChar['.'] = 'wwnnnnwnn';
    $barChar[' '] = 'nwwnnnwnn';
    $barChar['*'] = 'nwnnwnwnn';
    $barChar['$'] = 'nwnwnwnnn';
    $barChar['/'] = 'nwnwnnnwn';
    $barChar['+'] = 'nwnnnwnwn';
    $barChar['%'] = 'nnnwnwnwn';

    //Print text uder barcode
  	$this->SetFont('Arial','',12);
    //$this->SetXY(0,-15);
  	//$this->Cell(62,6,$code,0,0,'C');
    $this->SetXY(5,0.1);
  	//$this->Cell(62,6,substr($barcode,-$len),0,0,'L');
    $this->Cell(40,4,substr($code,2),0,0,'C');
    $this->SetFillColor(0);

    $code = '*'.strtoupper($code).'*';
    for($i=0; $i<strlen($code); $i++){
        $char = $code[$i];
        if(!isset($barChar[$char])){
            $this->Error('Invalid character in barcode: '.$char);
        }
        $seq = $barChar[$char];
        for($bar=0; $bar<9; $bar++){
            if($seq[$bar] == 'n'){
                $lineWidth = $narrow;
            }else{
                $lineWidth = $wide;
            }
            if($bar % 2 == 0){
                $this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
            }
            $xpos += $lineWidth;
        }
        $xpos += $gap;
    }
}
}


class ERPEan13Reports{
  public $params;
  public $pdf;

  function TestCheckDigit($barcode){
  	//Test validity of check digit
  	$sum=0;
    if(strlen($barcode)<13)return null;
    try{
    	for($i=1;$i<=11;$i+=2)
        //if(!is_numeric($barcode[$i])) return false;
    		$sum+=3*intval($barcode[$i]);
    	for($i=0;$i<=10;$i+=2)
    		$sum+=intval($barcode[$i]);
    	return ($sum+intval($barcode[12]))%10==0;
    }catch(Exception $e){
      return false;
    }
  }

  function create($params, $dest='I', $file=null){
    setlocale( LC_NUMERIC, 'es_ES' );


      if(!$this->TestCheckDigit($params["barcode"]) and $params["noValidate"]==false){
         //$this->pdf = new PDF_Code39('L','mm',array(36,62));
         $this->pdf = new PDF_417('L','mm',array(36,62));
       }else{
           $this->pdf = new PDF_EAN13('L','mm',array(36,62));
      }

      $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();
        $this->pdf->SetFont('Arial','',12);
        //$this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'logoEAN.png', 2, 6, 13, 13);

        if(!$this->TestCheckDigit($params["barcode"]) and $params["noValidate"]==false){

          //     $this->pdf->Code39(2,3,$params["barcode"],0.5,20);
            $this->pdf->pdf417($params);
            $this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'logoEAN.png',53, 11, 7, 7);
        }else{
         $this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'logoEAN.png', 47, 6, 14, 14);
         $this->pdf->EAN13(6,5,$params["barcode"],16,.40, $params["noValidate"]);
       }
        //$this->pdf->SetY(-9.5);
        $this->pdf->SetY(-9.5);
        $this->pdf->SetX(0);
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->MultiCell(62,3,utf8_decode($params["name"]),0,'C',0);
        //$this->pdf->SetXY(0,0);
        $this->pdf->SetXY(0,22);
        $this->pdf->Cell(62,5,utf8_decode($params["code"]),0,0,'C');
      return $this->pdf->Output($dest, $file==null?$params["barcode"].".pdf":$file);


}
}
