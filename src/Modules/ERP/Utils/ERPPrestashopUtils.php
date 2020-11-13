<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPPrestashopFieldNames;
use App\Modules\ERP\Entity\ERPOfferPrices;

class ERPPrestashopUtils
{

  private $this_url="https://www.ferreteriacampollano.com";

  public function updateWebProduct($doctrine,$array_new_data,$product,$webproduct){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);


    try{

         $repositoryPrestashopFieldNames=$doctrine->getRepository(ERPPrestashopFieldNames::class);

         //lo primero que hacemos es comprobar si se ha modificado el campo checkweb. Este campo lo tenemos que tratar independientemente del resto y darle prioridad.
         if(array_key_exists("checkweb",$array_new_data)){

             //Si el usuario marca el producto como web, tenemos que revisar si ya está subido (pero aparece en Prestashop como desactivado) o si hay que subirlo
              if($array_new_data["checkweb"]=="1")
              {
                 // MIRAMOS SI EXISTE EL PRODUCTO EN PRESTASHOP OBTENIENDO EL ID
                 $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
                 $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                 $id_prestashop=NULL;
                 $id_prestashop=$xml->products->product['id'];

                 //El producto no está en Prestashop, luego hay que crearlo
                 if($id_prestashop==NULL){
                   $xml_string=file_get_contents($this->this_url."/api/products?schema=blank", false, $context);

                 }

                 //el producto estaba en Prestashop pero lo teníamos desactivado
                 else{

                   //OBTENER ID DEL PRODUCTO EN prestashop
                   $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
                   $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                   $id_prestashop=$xml->products->product['id'];

                   //obtenemos el XML del producto en prestashop que tenemos que modificar
                    $xml_string=file_get_contents($this->this_url."/api/products/".$id_prestashop, false, $context);
                   // $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
                    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                    unset($xml->product->manufacturer_name);
                    unset($xml->product->quantity);
                    $xml->product->active=1;
                    $this->callWSUpdateProduct($id_prestashop,$xml);

                 }
             }
             else{

                 //hay que desactivarlo de la web

             }

         }

         else
         {

                foreach($array_new_data as $clave=>$valor)
                {
                  $PrestashopFieldName=$repositoryPrestashopFieldNames->findOneBy(["axiomname"=>$clave]);
                  //OBTENER ID DEL PRODUCTO EN prestashopGetProduct
                  $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
                  $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                  $id_prestashop=$xml->products->product['id'];

                  //obtenemos el XML del producto en prestashop que tenemos que modificar
                   $xml_string=file_get_contents($this->this_url."/api/products/".$id_prestashop, false, $context);
                  // $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
                   $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                   unset($xml->product->manufacturer_name);
                   unset($xml->product->quantity);

                  /*si el campo tiene equivalencia en prestashop, actualizará el campo concreto*/
                  if($PrestashopFieldName!=NULL){


                     $psname=$PrestashopFieldName->getPrestashopname();

                    if($clave=="name"){
                        $valor=trim(preg_replace("[\n|\r|\n\r]","",$valor));
                        $valor=str_replace("p%","",$valor);
                        $valor=str_replace("P%","",$valor);
                        $valor=str_replace("vt/u","",$valor);
                        $valor=str_replace(chr(160), chr(32), $valor);

                        if($webproduct->getMinquantityofsaleweb()>1){
                             if(strpos($valor, 'v/metro') !== false OR strpos($valor, 'v/m') !== false OR strpos($valor, 'vta/metro') !== false OR strpos($valor, 'v/me') !== false){
                               $valor=$valor." (".round($webproduct->getMinquantityofsaleweb())." metros)";
                             }
                             else if(strpos($valor, 'venta kg') !== false){
                               $valor=$valor." (".round($webproduct->getMinquantityofsaleweb())." kilos)";
                             }
                              else if(strpos($valor, 'v/kg') !== false){
                               $valor=$valor." (".round($webproduct->getMinquantityofsaleweb())." kilos)";
                             }
                             else if(strpos($valor, 'venta m2') !== false){
                               $valor=$valor." (".round($webproduct->getMinquantityofsaleweb())." metros cuadrados)";

                             }/*
                             else if($unidad_minima_venta=="KG")
                             {
                               $nombre_new=$nombre_new." (".round($cantidad_pedido_minimo_new)." kilos)";
                             }*/
                             else{
                               if($product->getManufacturer()->getCode()!="OERLIKON" AND $product->getManufacturer()->getCode()!="ESAB")
                               {
                                 $valor=$valor." (".round($webproduct->getMinquantityofsaleweb())." unidades)";
                               }

                             }

                         }//CANTIDAD MINIMA DE VENTA WEB >1


                         if(strpos($valor, 'v/metro') !== false OR strpos($valor, 'v/m') !== false OR strpos($valor, 'vta/metro') !== false OR strpos($valor, 'v/me') !== false){
                          $xml->product->unidad_medida="metro";
                         }

                         else if((strpos($valor, 'venta kg') !== false) || (strpos($valor, 'v/kg') !== false) /*|| $unidad_minima_venta=="KG"*/){
                            $xml->product->unidad_medida="kilo";
                         }

                         else if(strpos($valor, 'venta m2') !== false){
                               $xml->product->unidad_medida="m2";
                             }

                           $valor=str_replace("v/metro","",$valor);
                           $valor=str_replace("v/m","",$valor);
                           $valor=str_replace("v/me","",$valor);
                           $valor=str_replace("vta/metro","",$valor);
                           $valor=str_replace("venta kg","",$valor);
                           $valor=str_replace("v/kg","",$valor);
                           $valor=str_replace("venta m2","",$valor);

                           if($product->getManufacturer()->getCode()!="C88" AND $product->getManufacturer()->getCode()!="LOCTITE"){
                             if($product->getManufacturer()->getCode()=="PELTOR") $valor=$valor." 3M";
                             else $valor=$valor." ".$product->getManufacturer()->getCode();
                           }
                      $xml->product->name->language=$valor;
                      $this->callWSUpdateProduct($id_prestashop,$xml);
                     }//END OF NAME

                     else if($clave=="minquantityofsaleweb"){

                       //multiplica el precios

                       //multiplica el peso

                       //para indicar en la web la cantidad mínima de venta, normalmente cogemos el valor minquantityofsaleweb.
                       //no obstante, si tenemos una unidad de embalaje de compra puesta y ésta es menor, lo priorizamos y subimos este valor.
                       if($product->getPurchasepacking()<$valor AND $product->getPurchasepacking()>0) $xml->product->cantidad_pedido_minimo=$product->getPurchasepacking();
                       else if($valor>0) $xml->product->cantidad_pedido_minimo=$valor;
                          $this->callWSUpdateProduct($id_prestashop,$xml);
                     }

                     else if($xml->product->$psname->language) {
                       $xml->product->$psname->language=$valor;
                      $this->callWSUpdateProduct($id_prestashop,$xml);
                     }
                     else {
                       $xml->product->$psname=$valor;
                       $this->callWSUpdateProduct($id_prestashop,$xml);
                     }



                   }

                   else if($clave=="webprice"){

                     $product->updateWebProductPrices($doctrine,$this);
                     $xml->product->on_sale=1;
                     $this->callWSUpdateProduct($id_prestashop,$xml);
                   }
                   else if($clave=="manomano" AND $valor==1){
                     $offerPricesRepository=$doctrine->getRepository(ERPOfferPrices::class);
                     $date= new \DateTime();
                     $availableOfferID=$offerPricesRepository->getAvailableOfferID($product,$date);

                     if($availableOfferID==NULL AND $webproduct->getWebprice()==NULL AND $product->getDiscontinued()==false AND $product->getBigsize()==false){
                       $xml->product->id_supplier=2;
                       $this->callWSUpdateProduct($id_prestashop,$xml);

                     }

                   }

                   /*parámetros que no tienen equivalencia directa en Prestashop o que tienen ciertas particularidades*/
                   else{

                     //OBTENER ID DEL PRODUCTO EN prestashopGetProduct
                     $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
                     $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                     $id_prestashop=$xml->products->product['id'];

                     //obtenemos el XML del producto en prestashop que tenemos que modificar
                      $xml_string=file_get_contents($this->this_url."/api/products/".$id_prestashop, false, $context);
                     // $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
                      $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                      unset($xml->product->manufacturer_name);
                      unset($xml->product->quantity);

                       if($clave=="manufacturer"){

                         //OBTENER ID DE LA MARCA EN prestashop
                         $xml_string=file_get_contents($this->this_url."/api/manufacturers/?display=[id]&filter[name]=".$valor->getCode(), false, $context);
                         $xml2 = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                         $id_manufacturer=$xml2->manufacturers->manufacturer->id;

                         $xml->product->id_manufacturer=$id_manufacturer[0];

                          if($valor->getCode()=="3M" OR $valor->getCode()=="PELTOR" OR $valor->getCode()=="ROBUSTA" OR $valor->getCode()=="JHAYBER"  OR $valor->getCode()=="PANTER"  OR $valor->getCode()=="SINEX"){
                            $xml->product->available_later->language="Sin stock";
                          }
                          $this->callWSUpdateProduct($id_prestashop,$xml);


                       }//END OF MANUFACTURER

                       else if($clave=="rotation" AND $valor==0){

                          $xml->product->available_later->language="Sin stock";
                          $this->callWSUpdateProduct($id_prestashop,$xml);

                       }//END OF ROTATION

                       else if($clave=="purchasepacking" AND $product->getMinquantityofsaleweb()>1 AND $valor>1){

                         if($valor<$product->getMinquantityofsaleweb()){
                            $xml->product->available_later->cantidad_pedido_minimo=$valor;
                            $this->callWSUpdateProduct($id_prestashop,$xml);
                          }

                       }//END OF PURCHASEPACKING

                       else if($clave=="additionalcost"){

                         //sumamos su valor a los precios
                         //unidad_medida_precio M, C, U

                         //((precio/unidad_medida_precio)+additionalcost)*minquantityofsaleweb

                       }//END OF additionalcost

                       else if($clave=="estimateddelivery"){
                         if($product->getRotation()==false OR $product->getManufacturer()->getName()=="3M"
                         OR $product->getManufacturer()->getName()=="PELTOR") $xml->product->available_later="Sin stock";
                         else
                         {
/*

                           falta por añadir en los pedidos de compra algun campo que indique que el material
                           de ese pedido ya se ha recibido, por lo que no tenemos todavía los pendientes de recibir.

                            $repositorystocks=$doctrine->getRepository(ERPStocks::class);
                            $repositoryproducts=$doctrine->getRepository(ERPProducts::class);
                            $stock=$repositorystocks->getAllStocksByProduct($product,null);
                            $pendingserve=$product->getPendigServe($product);
                            $pendingreceive=$product->getPendigReceive($product);
                            $real_stock=(($stock+$pendingreceive)-$pendingserve)/$product->getMinquantityofsaleweb();


                            if($pendingreceive>0 AND $real_stock>0 AND $repositoryproducts->getVariants($product)==NULL)
                            {
                                  if($real_stock>1){

                      							if($unidad_medida_new=="metro" OR $unidad_medida_new=="kilo" OR $unidad_medida_new=="m2"){
                      								  $xml->product->available_later="Pdte. de recibir ".floor($real_stock)." rollos";
                      							}
                      							else{
                      								  $xml->product->available_later="Pdte. de recibir ".floor($real_stock)." packs";
                      							}

                      						}
                      						else if($real_stock==1){
                      							if($unidad_medida_new=="metro" OR $unidad_medida_new=="kilo" OR $unidad_medida_new=="m2"){
                      								  $xml->product->available_later="Pdte. de recibir ".floor($real_stock)." rollo";
                      							}
                      							else{
                      								  $xml->product->available_later="Pdte. de recibir ".floor($real_stock)." pack";
                      							}

                      						}
                      						else{
                      								if($valor!="-1")
                      								{
                      									if($familia_nav=="FER42")   $xml->product->available_later="Sin stock";
                      									else  $xml->product->available_later="Bajo pedido ".$valor." dias";
                      								}
                      								else {
                      									if($familia_nav=="FER42") $xml->product->available_later="Sin stock";
                      									else  $xml->product->available_later="Bajo pedido";
                      								}
                      						}
                           }

                           else{
                    					 if($valor!="-1")  {
                    							if($familia_nav=="FER42") $xml->product->available_later="Sin stock";
                    							else $xml->product->available_later="Bajo pedido ".$valor." dias";

                    					 }
                    					 else {
                    							if($familia_nav=="FER42") $etiqueta_stock_new="Sin stock";
                    							else $etiqueta_stock_new="Bajo pedido";

                    					 }
                    				}
*/

                         }



                    }

                 }
            }
       }

     }catch(Exception $e){}

  }


  public function updateWebProductPrices($doctrine,$product,$webproduct){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

    //OBTENER ID DEL PRODUCTO EN prestashopGetProduct
    $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    $id_prestashop=$xml->products->product['id'];

    $PVP=(($product->getPVP()*$webproduct->getMinquantityofsaleweb())+$webproduct->getAdditionalcost())*$product->getSalepacking();
    //existe un precio web, luego hay que aplicarlo sobre todas las cosas
    if($webproduct->getWebprice())
    {
      //en el actulizador utilizabamos el campo unidad_medida_precio, pero ese valor en principio ya no se usa en Axiom
      $webprice=(($webproduct->getWebprice()*$webproduct->getMinquantityofsaleweb())+$webproduct->getAdditionalcost())*$product->getSalepacking();
     if($webprice<$PVP) $webprice_discount=round((1-(round($webprice*1,2)/round($PVP*1,2))),2);
        $this->checkSpecificPrice($id_prestashop,0,$webprice_discount,1);


        $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
        $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
        $id_prestashop=$xml->products->product['id'];

        //obtenemos el XML del producto en prestashop que tenemos que modificar
         $xml_string=file_get_contents($this->this_url."/api/products/".$id_prestashop, false, $context);
        // $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
         $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
         unset($xml->product->manufacturer_name);
         unset($xml->product->quantity);
         $xml->product->on_sale=1;
         $this->callWSUpdateProduct($id_prestashop,$xml);

    }
    else{

      $offerPricesRepository=$doctrine->getRepository(ERPOfferPrices::class);
      $date= new \DateTime();
      $availableOfferID=$offerPricesRepository->getAvailableOfferID($product,$date);

      if($availableOfferID){

       $obj_offer=$offerPricesRepository->findOneBy(["id"=>$availableOfferID]);
       $offerprice=(($obj_offer->getPrice()*$webproduct->getMinquantityofsaleweb())+$webproduct->getAdditionalcost())*$product->getSalepacking();


       if($offerprice<$PVP){
         $offer_discount=0;
         $offer_discount=round((1-(round($offerprice*1,2)/round($PVP*1,2))),2);

         $this->checkSpecificPrice($id_prestashop,0,$offer_discount,1);
       }

       else{
         $this->checkSpecificPrice($id_prestashop,0,0,1);

       }

        //se pone como precio en la web el precio de oferta
        //puesto que existe un precio de oferta, revisamos en prestashop los posibles descuentos específicos para grupos de clientes y se borrarán.
        //GDTO3
        $this->checkSpecificPrice($id_prestashop,6,0,1);
        //GDTO2
        $this->checkSpecificPrice($id_prestashop,8,0,1);
        //GDTO1
        $this->checkSpecificPrice($id_prestashop,7,0,1);

      }

     //no hay ni precio web ni precio oferta, luego habrá que comparar precios y subir solo el GDTO3 o GDTO1 y GDTO2 en caso de que haya diferencais
      else{

        $CustomerGroupsRepository=$doctrine->getRepository(ERPCustomerGroups::class);
        $gdto1=$CustomerGroupsRepository->findOneBy(["name"=>"GDTO1"]);
        $gdto2=$CustomerGroupsRepository->findOneBy(["name"=>"GDTO2"]);
        $gdto3=$CustomerGroupsRepository->findOneBy(["name"=>"GDTO3"]);

        $productpricesRepository=$doctrine->getRepository(ERPProductPrices::class);
        $price_for_gdto1=$productpricesRepository->getPriceforGroup($product,$gdto1);
        $price_for_gdto2=$productpricesRepository->getPriceforGroup($product,$gdto2);
        $price_for_gdto3=$productpricesRepository->getPriceforGroup($product,$gdto3);

        $gdto3_discount=0;
        $gdto2_discount=0;
        $gdto1_discount=0;

        if($price_for_gdto3!=NULL AND $product->getManufacturer()->getName()!="PETZL" AND $product->getManufacturer()->getName()!="FORUM") $gdto3_discount=round((1-(round($price_for_gdto3*1,2)/round($PVP*1,2))),2);
        if($price_for_gdto2!=NULL AND $product->getManufacturer()->getName()!="PETZL" AND $product->getManufacturer()->getName()!="FORUM") $gdto2_discount=round((1-(round($price_for_gdto2*1,2)/round($PVP*1,2))),2);
        if($price_for_gdto1!=NULL AND $product->getManufacturer()->getName()!="PETZL" AND $product->getManufacturer()->getName()!="FORUM") $gdto1_discount=round((1-(round($price_for_gdto1*1,2)/round($PVP*1,2))),2);

         //GDTO3
         $this->checkSpecificPrice($id_prestashop,6,$gdto3_discount,1);
         //GDTO2
         $this->checkSpecificPrice($id_prestashop,8,$gdto3_discount,1);
         //GDTO1
         $this->checkSpecificPrice($id_prestashop,7,$gdto3_discount,1);
      }


    }


    /*DESCUENTOS POR CANTIDAD*/

    $offerPricesRepository=$doctrine->getRepository(ERPOfferPrices::class);
    $date= new \DateTime();
    $availableQuantityOffers=$offerPricesRepository->getAvailableQuantityOffers($product,$date);

    foreach($availableQuantityOffers as $offer){

      $obj_offer=$offerPricesRepository->findOneBy(["id"=>$offer["id"]]);
      $offerprice=(($obj_offer->getPrice()*$webproduct->getMinquantityofsaleweb())+$webproduct->getAdditionalcost())*$product->getSalepacking();

      if($offerprice<$PVP){

        $offer_discount=0;
        $offer_discount=round((1-(round($offerprice*1,2)/round($PVP*1,2))),2);
        $this->checkSpecificPrice($id_prestashop,0,$offer_discount,$obj_offer->getQuantity());
      }

      else{
        $this->checkSpecificPrice($id_prestashop,0,0,$obj_offer->getQuantity());

      }

    }
  }

  public function callWSUpdateProduct($id_prestashop,$xml)
  {

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

   $url = "https://www.ferreteriacampollano.com/api/products/".$id_prestashop;
   //$url= $this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC";
   $ch = curl_init();

   $putString = $xml->asXML();
   //dump($putString);
   /** use a max of 256KB of RAM before going to disk */
   $putData = fopen('php://temp/maxmemory:256000', 'w');
   if (!$putData) {
       die('could not open temp memory data');
   }
   fwrite($putData, $putString);
   fseek($putData, 0);

   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
   curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_PUT, true);
   curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));

   curl_setopt($ch, CURLOPT_INFILE, $putData);

   $output = curl_exec($ch);

   fclose($putData);

   if (curl_errno($ch)) {  dump(curl_error($ch)); }
   else {  curl_close($ch); }


  }



  public function checkSpecificPrice($id_prestashop,$id_group,$discount,$quantity)
   {
     $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
     $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);
     $specific_price_id=NULL;
     $xml_specific_prices_string=file_get_contents($this->this_url."/api/specific_prices/?display=[id]&filter[id_product]=".$id_prestashop."&filter[id_group]=".$id_group."&filter[from_quantity]=".$quantity, false, $context);
     $xmL_gdto = simplexml_load_string($xml_specific_prices_string, 'SimpleXMLElement', LIBXML_NOCDATA);
     $specific_price_id=$xmL_gdto->specific_prices->specific_price->id[0];

     //hay un descuento específico y este ya existe en Prestashop
     if($discount>0 and $specific_price_id!=NULL)
     {
         $xml_string=file_get_contents($this->this_url."/api/specific_prices/".$specific_price_id, false, $context);
         $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);

         if($xml->specific_price->reduction!=$discount) {
           $xml->specific_price->reduction=$discount;
           $this->updateSpecificPrice($specific_price_id,$xml);
         }
     }

     //hay un descuento específico pero este NO EXISTE en Prestashop. Lo creamos.
     else if ($discount>0 and $specific_price_id==NULL){
       $this->addSpecificPrice($id_prestashop,$id_group,$discount,$quantity);

     }
     //no existe un descuento específico pero sí que lo tenemos en prestashop, luego hay que borrarlo
     else if ($discount==0 and $specific_price_id!=NULL){

       $xml_string=file_get_contents($this->this_url."/api/specific_prices/".$specific_price_id, false, $context);
       $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
       $this->deleteSpecificPrice($specific_price_id,$xml);

     }

  }


  public function deleteSpecificPrice($specific_price_id,$xml){
    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

    $url=$this->this_url."/api/specific_prices/".$specific_price_id;
    $putString = $xml->asXML();

    $ch = curl_init();
    $putData = fopen('php://temp/maxmemory:256000', 'w');
    if (!$putData) {
        die('could not open temp memory data');
    }
    fwrite($putData, $putString);
    fseek($putData, 0);


    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
    curl_setopt($ch, CURLOPT_INFILE, $putData);

    $output = curl_exec($ch);

    fclose($putData);
    if (curl_errno($ch)) {  dump(curl_error($ch)); }
    else {  curl_close($ch); }

  }

  public function updateSpecificPrice($specific_price_id,$xml){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

    $url=$this->this_url."/api/specific_prices/".$specific_price_id;
    //$url= $this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC";
    $ch = curl_init();

    $putString = $xml->asXML();
    //dump($putString);
    /** use a max of 256KB of RAM before going to disk */
    $putData = fopen('php://temp/maxmemory:256000', 'w');
    if (!$putData) {
        die('could not open temp memory data');
    }
    fwrite($putData, $putString);
    fseek($putData, 0);


    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));

    curl_setopt($ch, CURLOPT_INFILE, $putData);

    $output = curl_exec($ch);

    fclose($putData);

    if (curl_errno($ch)) {  dump(curl_error($ch)); }
    else {  curl_close($ch); }

  }

  public function addSpecificPrice($id_product,$id_group,$reduction,$quantity){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

    $xml_string=file_get_contents($this->this_url."/api/specific_prices?schema=blank", false, $context);
    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    $xml->specific_price->id_shop_group=0;
    $xml->specific_price->id_shop=0;
    $xml->specific_price->id_shop=0;
    $xml->specific_price->id_cart=0;
    $xml->specific_price->id_product=$id_product;
    $xml->specific_price->id_product_attribute=0;
    $xml->specific_price->id_currency=0;
    $xml->specific_price->id_country=0;
    $xml->specific_price->id_group=$id_group;
    $xml->specific_price->id_customer=0;
    $xml->specific_price->id_specific_price_rule=0;
    $xml->specific_price->price="-1";
    $xml->specific_price->from_quantity=$quantity;
    $xml->specific_price->reduction=$reduction;
    $xml->specific_price->reduction_tax=1;
    $xml->specific_price->reduction_type="percentage";
    $xml->specific_price->from="0000-00-00 00:00:00";
    $xml->specific_price->to="0000-00-00 00:00:00";

    $url=$this->this_url."/api/specific_prices/";
    $postString=$xml->asXML();
    $ch = curl_init();


    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

    $output = curl_exec($ch);

    if (curl_errno($ch)) {  dump(curl_error($ch)); }
    else {  curl_close($ch); }


  }


}
