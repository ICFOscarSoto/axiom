<?php
namespace App\Modules\Globale\Helpers;

class HelperDatetime{

  function getTimeAgo( $time ){
      $time_difference = time() - $time;

      if( $time_difference < 1 ) { return 'Hace menos de 1 segundo'; }
      $condition = array( 12 * 30 * 24 * 60 * 60 =>  'año',
                  30 * 24 * 60 * 60       =>  'mes',
                  24 * 60 * 60            =>  'dia',
                  60 * 60                 =>  'hora',
                  60                      =>  'minuto',
                  1                       =>  'segundo'
      );
      foreach( $condition as $secs => $str ){
          $d = $time_difference / $secs;

          if( $d >= 1 ){
              $t = round( $d );
              return 'Hace ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ';
          }
      }
  }

}
