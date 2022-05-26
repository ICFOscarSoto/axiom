<?php
namespace App\Modules\Email\Helpers;

class HelperVelilla {




  public function parseStocks($spreadsheet, $doctrine, $output){
    $discordchannel_web="935430617842196601";
    $discordchannel_critico="883046233017552956";
    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);
    $sheet=$spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow()-1;
    $url="https://www.ferreteriacampollano.com";

    $stocksArray=Array();
    $item=null;

    for ($row = 2; $row <= $highestRow; ++$row) {

        $EAN=$sheet->getCell("B" . $row)->getValue();
        $stock=$sheet->getCell("C" . $row)->getValue();

        $item["ean"]=$EAN;
        $item["stock"]=$stock;

      //  $output->writeln($row." -> ".$EAN." - ".$stock);
        $stocksArray[]=$item;
        $item=null;



        //obtenemos el id de product_attribute
        $xml_string_combination=file_get_contents($url."/api/combinations/?display=[id]&filter[reference]=".$EAN, false, $context);
        $xml_combination = simplexml_load_string($xml_string_combination, 'SimpleXMLElement', LIBXML_NOCDATA);
        $id_combination_new=$xml_combination->combinations->combination->id;

        if($id_combination_new!=""){
          //obtenemos el id de stocks_available
          $xml_string_stocks=file_get_contents($url."/api/stock_availables/?display=[id]&filter[id_product_attribute]=".$id_combination_new, false, $context);
          $xml_stocks = simplexml_load_string($xml_string_stocks, 'SimpleXMLElement', LIBXML_NOCDATA);
          $id_stocks_new=$xml_stocks->stock_availables->stock_available->id;

          $xml_string=file_get_contents($url."/api/stock_availables/".$id_stocks_new, false, $context);
          $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
          if($stock>=40) $xml->stock_available->quantity=30;
          else if($stock>=30) $xml->stock_available->quantity=20;
          else if($stock>=20) $xml->stock_available->quantity=10;
          else if($stock>=10) $xml->stock_available->quantity=7;
          else if($stock>=5) $xml->stock_available->quantity=2;
          else $xml->stock_available->quantity=0;
          $output->writeln("Ponemos stock ".$stock." en la combinación ".$id_combination_new." que tiene EAN ".$EAN);
          HelperVelilla::updateStock($id_stocks_new,$xml);

        }
        else{
          //no hemos encontrado la combinación, así que buscamos el producto dom_import_simplexml
          //obtenemos el id del product

          $xml_string_product=file_get_contents($url."/api/products/?display=[id]&filter[ean13]=".$EAN."&filter[id_supplier]=3", false, $context);
          $xml_product = simplexml_load_string($xml_string_product, 'SimpleXMLElement', LIBXML_NOCDATA);
          $id_product_new=$xml_product->products->product->id;

          if($id_product_new!=""){

            //obtenemos el id de stocks_available
            $xml_string_stocks=file_get_contents($url."/api/stock_availables/?display=[id]&filter[id_product]=".$id_product_new, false, $context);
            $xml_stocks = simplexml_load_string($xml_string_stocks, 'SimpleXMLElement', LIBXML_NOCDATA);
            $id_stocks_new=$xml_stocks->stock_availables->stock_available->id;

            $xml_string=file_get_contents($url."/api/stock_availables/".$id_stocks_new, false, $context);
            $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
            if($stock>=15) $xml->stock_available->quantixxty=15;
            else if($stock>=10) $xml->stock_available->quantity=10;
            else $xml->stock_available->quantity=0;
            $output->writeln("Ponemos stock ".$stock." en el producto simple ".$id_product_new." que tiene EAN ".$EAN);
            HelperVelilla::updateStock($id_stocks_new,$xml);
          }

        }

        file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel_web."&msg=".urlencode(":white_check_mark:"." SCRIPT ".basename(__FILE__, '.php').": Se ha procesado correctamente el archivo diario de Velilla"));

    }


/*
    $json=json_encode($stocksArray);
    $postdata = http_build_query(['json' => $json]);
    $opts = [
      'http' =>['method'  => 'POST',
      'header'  => 'Content-Type: application/x-www-form-urlencoded',
      'content' => $postdata,
      "Cookie: foo=bar\r\n" ,  // check function.stream-context-create on php.net
      "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0"
      ]
    ];

    $context  = stream_context_create($opts);
    $result = file_get_contents('https://www.ferreteriacampollano.com/feeds/diario_velilla.php', false, $context);
    $result = json_decode($result,true);
*/

/*
    if(json_last_error() !== JSON_ERROR_NONE){
      file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel_critico."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Error valor retornado al procesar stocks de Velilla"));
    }
    else{
      if($result["result"]!=1){
        file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel_critico."&msg=".urlencode(":sos:"."SCRIPT ".basename(__FILE__, '.php').": Ocurrieron errores y no se pudieron procesar los stocks de Velilla"));
      }
      else{
        file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel_web."&msg=".urlencode(":white_check_mark:"." SCRIPT ".basename(__FILE__, '.php').": Se ha procesado correctamente el archivo diario de Velilla"));

      }
    }

*/

  }


  public function updateStock($id_stocks_new,$xml){

    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);

    $url="https://www.ferreteriacampollano.com/api/stock_availables/".$id_stocks_new;
    $ch = curl_init();

    $putString = $xml->asXML();
    //dump($putString);
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
