<?php
namespace App\Modules\Email\Helpers;

class HelperArcos {

  private $url="https://www.ferreteriacampollano.com";

  public function parseStocks($csv, $doctrine, $output){

    $discordchannel="935430617842196601";
    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);
    $url="https://www.ferreteriacampollano.com";

    $rows = explode("\n",$csv);
    $s = array();
    foreach($rows as $row) {
        $s[] = str_getcsv($row);
    }


    foreach($s as $data){

        $fila_stock_string=$data[0];
        $fila_stock=explode(";",$fila_stock_string);
        $EAN="";
        $stock=0;
        if(isset($fila_stock[2])) $EAN=trim($fila_stock[2]);
        if(isset($fila_stock[3])) $stock=trim($fila_stock[3]);


        if($EAN!="" AND $EAN!=NULL){
          $output->writeln("Stock: ".trim($stock)." del EAN ".$EAN);
          //obtenemos el id del product
          $xml_string_product=file_get_contents($url."/api/products/?display=[id]&filter[ean13]=".$EAN."&filter[id_supplier]=3", false, $context);
          $xml_product = simplexml_load_string($xml_string_product, 'SimpleXMLElement', LIBXML_NOCDATA);
          $id_product_new=$xml_product->products->product->id;

          if($id_product_new!="")
          {
            //obtenemos el id de stocks_available
            $xml_string_stocks=file_get_contents($url."/api/stock_availables/?display=[id]&filter[id_product]=".$id_product_new, false, $context);
            $xml_stocks = simplexml_load_string($xml_string_stocks, 'SimpleXMLElement', LIBXML_NOCDATA);
            $id_stocks_new=$xml_stocks->stock_availables->stock_available->id;

            $xml_string=file_get_contents($url."/api/stock_availables/".$id_stocks_new, false, $context);
            $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
            if($stock>=50) $xml->stock_available->quantity=50;
            else if($stock>=25) $xml->stock_available->quantity=25;
            else if($stock>=10) $xml->stock_available->quantity=10;
            else $xml->stock_available->quantity=0;
            HelperArcos::updateStock($id_stocks_new,$xml, $output);
          }
        }

      }

      file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":white_check_mark:"." SCRIPT ".basename(__FILE__, '.php').": Se ha procesado correctamente el archivo diario de Arcos"));
  }

  public function updateStock($id_stocks_new,$xml, $output){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);
    $url="https://www.ferreteriacampollano.com/api/stock_availables/".$id_stocks_new;

    $ch = curl_init();
    $putString = $xml->asXML();

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

}
