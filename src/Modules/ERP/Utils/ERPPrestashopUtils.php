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



                    }//END OF estimateddelivery

                    /*
                     las variantes las tratamos de manera diferente :
                     - Si por ejemplo le cambiamos el nombre a una variante en Axiom, para que este cambio tenga efecto en PS,
                       le pasamos la variante antigua para que la borre y la nueva para que la cree.
                     - En cambio, si creamos una nueva variante en el producto, le pasamos null como variante anterior y solo
                       se encargará de crear la nueva.
                    */
                    else if($clave=="variants"){

                      if($valor["old"]!=null)
                      {
                        //comprobamos si existe la variante antigua en Prestashop
                        ///api/product_option_values/ --> fcattribute
                        $xml_string_variant=file_get_contents($this->this_url."/api/product_option_values/?display=[id]&filter[name]=".$valor["old"], false, $context);
                        $xml_variant = simplexml_load_string($xml_string_variant, 'SimpleXMLElement', LIBXML_NOCDATA);
                        $id_attribute_old=$xml_variant->product_option_values->product_option_value->id;

                        //dump("El id de la variante antigua es: ".$id_attribute_old);
                        //la variante antigua sí que existe en prestashop, luego hay que comprobar si la combinación variante-producto también existe      y borrarla
                        if($id_attribute_old!=NULL)
                        {
                          //obtenemos todas las combinaciones asociadas al producto.
                          $xml_string_product_combinations=file_get_contents($this->this_url."/api/combinations/?display=[id]&filter[id_product]=".$id_prestashop, false, $context);
                          $xml_product_combinations = simplexml_load_string($xml_string_product_combinations, 'SimpleXMLElement', LIBXML_NOCDATA);

                          //dump($xml_product_combinations);
                          foreach($xml_product_combinations->combinations->combination as $comb)
                          {
                            $array=array_unique((array) $comb);

                            //obtenemos cada combinación por el ID.
                          //  dump($this->this_url."/api/combinations/".$array["id"]);
                            $xml_string_product_combination=file_get_contents($this->this_url."/api/combinations/".$array["id"], false, $context);
                            $xml_product_combination = simplexml_load_string($xml_string_product_combination, 'SimpleXMLElement', LIBXML_NOCDATA);
                            $id_attribute_ps=$xml_product_combination->combination->associations->product_option_values->product_option_value;
                            $array_id_attribute=array_unique((array) $id_attribute_ps);
                            if($array_id_attribute["id"]==$id_attribute_old){
                            //  dump("vamos a borrar la combinación ".$array["id"]);
                              $this->deleteCombination($xml_product_combination,$array["id"]);
                              continue;
                            }
                        }
                        }
                      }


                      //OBTENER ID DE Prestashop de la nueva variante

                      $xml_string_variant=file_get_contents($this->this_url."/api/product_option_values/?display=[id]&filter[name]=".$valor["new"], false, $context);
                      $xml_variant = simplexml_load_string($xml_string_variant, 'SimpleXMLElement', LIBXML_NOCDATA);
                      $id_attribute_new=$xml_variant->product_option_values->product_option_value->id;

                      $this->addCombination($id_prestashop,$id_attribute_new);

                        ///api/combinations/ --> fcproduct_attribute
                        ///api/product_option_values --> fcattribute + fcattribute_lang

                      //id_attribute_group=4 (Talla)
                      //id_attribute_group=9 (Color)
                      //https://www.ferreteriacampollano.com/api/product_options/4 --> saca todas las tallas en PS


                      //https://www.ferreteriacampollano.com/api/product_option_values/&filter[name]=$valor
                      //sacamos de ahí el id del atributo y buscamos en el producto

                      //https://www.ferreteriacampollano.com/api/products/id
                      //sacamos el id de cada <associatons><product_option_values><product_option_value>


                      //https://www.ferreteriacampollano.com/api/product_option_values/

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


  public function uploadProductImages($product,$rootDir){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

    $id=$product->getId();

    //OBTENER ID DEL PRODUCTO EN prestashop
    $xml_string=file_get_contents($this->this_url."/api/products/?filter[reference]=".$product->getCode(), false, $context);
    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    $id_prestashop=$xml->products->product['id'];

    //$image_path = $rootDir.'/../cloud/'.$product->getCompany()->getId().'/images/products/'.$id.'/';

    $images=null;
    $image_path = $rootDir.'/../cloud/'.$product->getCompany()->getId().'/images/products/';

    if(file_exists($image_path.$id.'-large.png') || file_exists($image_path.$id.'-large.jpg')){
      $images[]=$rootDir.'/../cloud/'.$product->getCompany()->getId().'/images/products/'.$id."-large.png";
    }


    $found=true;
    $i=1;
    while($found==true){
      if(file_exists($image_path.$id.'/'.$id."-".$i.'-large.png') || file_exists($image_path.$id.'/'.$id."-".$i.'-large.jpg')){
        $i++;
      }else{
        $found=false;
        $i--;
      }
    }
    for($j=1;$j<=$i;$j++){
      $image=$rootDir.'/../cloud/'.$product->getCompany()->getId().'/images/products/'.$id."/".$id."-".$j."-large.png";
      $images[]=$image;
    }


    if($id_prestashop!=NULL AND !empty($images))
    {

      //  try{
          $xml_string=$this->curl_get_contents($this->this_url."/api/images/products/".$id_prestashop, false, $context);
        //  dump($xml_string);
          if($xml_string!="401 Unauthorized" AND $xml_string!="")
          {
            $xml_string=file_get_contents($this->this_url."/api/images/products/".$id_prestashop, false, $context);
            $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
            if($this->deleteProductImages($id_prestashop,$xml)==false) return false;
          }
        // }catch(Exception $e){}

        $url = $this->this_url.'/api/images/products/'.$id_prestashop;


        foreach($images as $image)
        {
            $key = '6TI5549NR221TXMGMLLEHKENMG89C8YV';
            $ch = curl_init();

            // Headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data', 'Expect:'));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERPWD, $key.':');
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('image'=> new \CurlFile($image)));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
          if (curl_errno($ch)) {
              //dump(curl_error($ch));
              return false;
          }
          else {
              curl_close($ch);
          }

        }
    return true;

   }
   else return false;

}


public function deleteProductImages($id_prestashop,$xml){
  $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
  $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);


  $xml_string=file_get_contents($this->this_url."/api/images/products/".$id_prestashop, false, $context);
  $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);

  foreach ($xml->image->declination as $image)
  {

    //$xml_string=file_get_contents($this->this_url."/api/images/products/".$id_prestashop."/".$image["id"]."?ps_method=DELETE", false, $context);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
    curl_setopt($ch, CURLOPT_URL, $this->this_url."/api/images/products/".$id_prestashop."/".$image["id"]."?ps_method=DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);

    if (curl_errno($ch)) {  return false; }
    else {  curl_close($ch); }


  }

  return true;


}


function curl_get_contents($url)
{
  $ch = curl_init($url);
  $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
  $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


public function deleteCombination($xml,$combination_id){

  $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
  $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

  $url=$this->this_url."/api/combinations/".$combination_id;
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


public function addCombination($id_product,$id_attribute){

  $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
  $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

  $xml_string=file_get_contents($this->this_url."/api/combinations?schema=blank", false, $context);
  $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
  $xml->combination->id_product=$id_product;
  $xml->combination->location="";
  $xml->combination->ean13="";
  $xml->combination->upc="";
  $xml->combination->quantity=0;
  $xml->combination->reference="";
  $xml->combination->supplier_reference="";
  $xml->combination->wholesale_price=0;
  $xml->combination->price=0;
  $xml->combination->ecotax=0;
  $xml->combination->weight=0;
  $xml->combination->unit_price_impact=0;
  $xml->combination->minimal_quantity=1;
  $xml->combination->default_on="";
  $xml->combination->available_date="0000-00-00 00:00:00";
  $xml->combination->associations->product_option_values->product_option_value->id=$id_attribute;


  $url=$this->this_url."/api/combinations/";
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

public function getCategoriesTree($parent)
{
  $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
  $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

  $xml_string=file_get_contents($this->this_url."/api/categories?display=[id,name,id_parent,level_depth]&filter[active]=1&filter[id_parent]=".$parent, false, $context);
  $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);

  $categories_JSON=[];
  //dump($xml);
  //dump($xml->categories);;
  foreach ($xml->categories->category as $category){
    //dump($category);
     $array=array_unique((array) $category);
     $array_name=array_unique((array) $array["name"]);
     $cat=["id"=>$array["id"],"name"=>$array_name["language"],"childrens"=>$this->getCategoriesTree($array["id"])];
     array_push($categories_JSON,$cat);
   }

  // return $categories_JSON;
   return null;

  }

}
