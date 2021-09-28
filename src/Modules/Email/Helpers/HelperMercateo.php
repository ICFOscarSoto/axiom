<?php
namespace App\Modules\Email\Helpers;
use App\Modules\Globale\Entity\GlobaleStates;

class HelperMercateo {

  public function convertToOrder($xml, $doctrine){
    $statesRepository = $doctrine->getRepository(GlobaleStates::class);
    $state = $statesRepository->findOneBy(["numcode"=>substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->ZIP->__toString(),0,2) ,"active"=>1, "deleted"=>0]);
    if(!$state) $state=''; else $state=$state->getName();
    $stateShipment = $statesRepository->findOneBy(["numcode"=>substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->SHIPMENT_PARTIES->DELIVERY_PARTY->PARTY->ADDRESS->ZIP->__toString(),0,2) ,"active"=>1, "deleted"=>0]);
    if(!$stateShipment) $stateShipment=''; else $stateShipment=$stateShipment->getName();

    $date = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($xml->ORDER_HEADER->CONTROL_INFO->GENERATION_DATE->__toString(),0,19));
    $order=[];
    $order['Fecha']=$date->format("Y-m-d H:i:s");
    $order['CodCliente']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->PARTY_ID->__toString(),0,20);
    $order['Nombre']=substr(mb_strtoupper($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->NAME->__toString(),'utf-8'),0,50);
    $order['Apellido1']='';
    $order['Apellido2']='';
    $order['Direccion1']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->STREET->__toString(),0,50);
    $order['Direccion2']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->STREET->__toString(),49,50);
    if(!$order['Direccion2']) $order['Direccion2']='';
    $order['CP']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->ZIP->__toString(),0,20);
    $order['Poblacion']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->CITY->__toString(),0,30);
    $order['Provincia']=substr($state,0,30);
    $order['Contacto']='';
    $order['TerminosPago']='085';
    $order['FormaPago']='TRANSFER';
    $order['DNI']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->VAT_ID->__toString(),2);
    $order['Telefono']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->PHONE->__toString(),0,50);
    $order['CodClientePagadorPS']=$xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->INVOICE_PARTY->PARTY->PARTY_ID->__toString();
    $order['NombrePagador']=substr(mb_strtoupper($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->INVOICE_PARTY->PARTY->ADDRESS->NAME->__toString(), 'utf-8'),0,50);
    $order['Apellido1Pagagor']='';
    $order['Apellido2Pagagor']='';
    $order['ConceptoGastoEnvio']='';
    $order['Direccion1Envio']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->SHIPMENT_PARTIES->DELIVERY_PARTY->PARTY->ADDRESS->STREET->__toString(),0,50);
    $order['Direccion2Envio']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->SHIPMENT_PARTIES->DELIVERY_PARTY->PARTY->ADDRESS->STREET->__toString(),49,50);
    if(!$order['Direccion2Envio']) $order['Direccion2Envio']='';
    $order['CPEnvio']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->SHIPMENT_PARTIES->DELIVERY_PARTY->PARTY->ADDRESS->ZIP->__toString(),0,20);
    $order['PoblacionEnvio']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->SHIPMENT_PARTIES->DELIVERY_PARTY->PARTY->ADDRESS->CITY->__toString(),0,30);
    $order['ProvinciaEnvio']=substr($stateShipment,0,30);
    $order['CodPedidoPS']=substr(preg_replace("/[^A-Za-z0-9 ]/", '', $xml->ORDER_HEADER->ORDER_INFO->ORDER_ID->__toString()),0,20);
    $order['RefPedidoPS']=substr(preg_replace("/[^A-Za-z0-9 ]/", '', $xml->ORDER_HEADER->ORDER_INFO->ORDER_ID->__toString()),0,20);
    $order['Observaciones']='';
    $order['EmpresaPagador']=substr(mb_strtoupper($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->NAME->__toString(),'utf-8'),0,50);
    $order['EmpresaEnvios']=substr(mb_strtoupper($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->SHIPMENT_PARTIES->DELIVERY_PARTY->PARTY->ADDRESS->NAME->__toString(),'utf-8'),0,50);
    $order['Email']=substr($xml->ORDER_HEADER->ORDER_INFO->ORDER_PARTIES->BUYER_PARTY->PARTY->ADDRESS->EMAIL->__toString(),0,100);
    $lines=[];
    foreach($xml->ORDER_ITEM_LIST->children() as $item){
      if($item->ARTICLE_ID->BUYER_AID->__toString()=="V-8297_ALMACEN"){
        $line['Cod_NAV']='29004';
        $line['Descripcion']='Gastos de envio y manipulacion';
      }else{
        $line['Cod_NAV']=$item->ARTICLE_ID->SUPPLIER_AID->__toString();
        $line['Descripcion']=$item->ARTICLE_ID->DESCRIPTION_SHORT->__toString();
      }
      $line['NLinea']=$item->LINE_ITEM_ID->__toString()*1000;
      $line['Cantidad']=$item->QUANTITY->__toString()*1;
      $line['PrecioVenta']=$item->ARTICLE_PRICE->PRICE_AMOUNT->__toString()*1;
      $line['GrupoIVA']=21;
      $line['Descuento']=0;
      $line['ImporteLinea']=$item->ARTICLE_PRICE->PRICE_LINE_AMOUNT->__toString()*1;
      $line['Variante']='';
      $lines[]=$line;
    }
    $order['Lineas']=$lines;

    return $order;
  }

}
