<?php
namespace App\Modules\Globale\Utils;

use App\Modules\Globale\Entity\GlobaleCompanies;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

class GlobaleExportUtils
{
    private $template=null;

    private function createCSV(array &$array){
       if (count($array) == 0) {
         return null;
       }
       ob_start();
       $df = fopen("php://output", 'w');
       $head = [];
       foreach ($this->template as $key => $value) {
         array_push($head, (isset($value['caption']) && $value['caption']!=''?$value['caption']:$value['name']));
       }
       fputcsv($df, array_map("utf8_decode",$head));
       foreach ($array as $row) {
          fputcsv($df, array_values (array_map("utf8_decode", $row )));
       }
       fclose($df);
       return ob_get_clean();
   }

   public function applyFormats($array){
     $temp=$array;
     foreach($this->template as $key=>$field){
       if(isset($field["format"])){
          //Parse type fields
          switch($field["format"]){
            case "time":
              foreach($temp as $key=>$record){
                $temp[$key][$field["name"]]=gmdate("H:i:s", $temp[$key][$field["name"]]);
              }
            break;
          }
       }
     }
     return $temp;
   }

   public function export($list, $template){
     $this->template=$template;
     $filename='export.csv';
     $array=$list["data"];
     //exclude tags column, last
     $key='_tags';
     array_walk($array, function (&$v) use ($key) {
      unset($v[$key]);
     });
     $array=$this->applyFormats($array);

     $fileContent=$this->createCSV($array);
     $response = new Response($fileContent);
     // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
      );
     // Set the content disposition
     $seconds_to_cache = 0;
     $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
     $response->headers->set("Expires", $ts);
     $response->headers->set("Pragma", "cache");
     $response->headers->set("Cache-Control", "max-age=0, no-cache, must-revalidate, proxy-revalidate");
     $response->headers->set('Content-Type', 'application/force-download');
     $response->headers->set('Content-Type', 'application/octet-stream');
     $response->headers->set('Content-Type', 'application/download');
     $response->headers->set('Content-Disposition', $disposition);
     // Dispatch request
     return $response;

   }

}
