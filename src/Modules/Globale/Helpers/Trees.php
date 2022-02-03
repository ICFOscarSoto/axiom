<?php
namespace App\Modules\Globale\Helpers;

class Trees
{
  public function createTree(&$list, $parent){
      $tree = array();
      foreach ($parent as $k=>$l){
          if(isset($list[$l['id']])){
              $l['children'] = Trees::createTree($list, $list[$l['id']]);
          }
          $tree[] = $l;
      }
      return $tree;
  }
}
