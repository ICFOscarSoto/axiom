<?php
namespace App\Modules\Form\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Modules\Globale\Entity\Companies;
/** FornUtils is class for create dinamic form
*/
class FormController extends Controller{
  var $fullform ="<script>var load_wait=0;</script>";
  var $formid;
  var $name;
  var $action;
  var $method;
  var $enctype;
  var $onsubmit;
  var $form = array();
  var $input;
  var $type;
  var $label;
  var $value;
  var $oForm;
  var $cForm;
  var $oPanel;
  var $cPanel;
  var $oRow;
  var $cRow;
  var $radio;
  var $checkbox;
  var $datepicker;
  var $knob;
  var $select;
  var $spiner;
  var $slider;
  var $submitbutton;
  var $texto;
  var $fieldset;
  var $legend;
  var $widht;
  var $serializeSwitches="";

  /*======================================
  ==         Auxiliary Form             ==
  ========================================*/


  /**
  * Order field, this function see order number on form and order it
  *
  * @param array $a  field 1
  * @param array $b  field 2
  * @return int -1 if order a<b return -1, in other case return 1
  *
  */
  private function orderField($a, $b){
    return ($a["order"]<=$b["order"] ? -1:1);
  }



  /*======================================
  ==           Prepare Form             ==
  ========================================*/
  /*
  function addForm($name,$submit){
  $tempform = array("name" => $name, "submit"=>$submit, "step" => array());
  array_push($this->form,$tempform);
}
function removeForm($name){

$key = array_search($name, array_column($this->form, "name"));
if($key !== FALSE){
array_splice($this->form,$key,1);
}
}

function addStep($nameform){
$key = array_search($nameform, array_column($this->form, "name"));

if($key !== FALSE){
$tempstep = array ("panels"=>array());
$this->form[$key]["step"][]=$tempstep;
}
}
function removeStep($nameform,$step){
$key = array_search($nameform, array_column($this->form, "name"));

if($key !== FALSE){
array_splice($this->form[$key]["step"],$step,1);
}

}
function addPanel($nameform,$step,$namepanel){
$key = array_search($nameform, array_column($this->form, "name"));

if($key !== FALSE){
$temppanel = array ("name"=>$namepanel, "groups"=>array());
$this->form[$key]["step"][$step]=$temppanel;
}
}

function removePanel($nameform,$step,$namepanel){
$key = array_search($nameform, array_column($this->form, "name"));

if($key !== FALSE){
$keypanel = array_search($namepanel, array_column($this->form[$key]["step"][$step], "name"));
if($keypanel !== FALSE){
array_splice($this->form[$key]["step"]["step"],$keypanel,1);
}
}
}
function addGroup($nameForm,$step,$namepanel){}
function removeGroup($nameForm,$step,$namepanel){}
function addfield($nameForm,$step,$namepanel){}
function removefield($nameForm,$step,$namepanel){}*/

/**
* datareceived processing of form data and save it
*
* @param object $container   Container for obtain ORM(Doctrine)
* @param Request $request Form response
* @param object $object Object with form type
* @return object Return filled object
*
*/
function datareceived($container,Request $request, $object){
  $entityManager = $container->getDoctrine()->getManager();
  $class = get_class ($object);
  $vars = get_class_vars($class);

  foreach($entityManager->getClassMetadata($class)->fieldMappings as $key=>$value){

    $data = null;
    $continue = true;
    switch  ($value["type"]){
      case "datetime":
      if(!($request->query->has($value["fieldName"]."-date")&& $request->query->has($value["fieldName"]."-time"))){$continue = false;}
      if($request->query->get($value["fieldName"]."-date") ==null || $request->query->get($value["fieldName"]."-time") ==null){$continue = false;}
      $data = (\DateTime::createFromFormat("d/m/Y H:i", $request->query->get($value["fieldName"]."-date")." ".$request->query->get($value["fieldName"]."-time")));
      break;
      case "boolean":
      $data=($request->query->get($value["fieldName"])=="true")?true:false;
      break;
      default :
      if(!$request->query->has($value["fieldName"]))continue;
      $data=$request->query->get($value["fieldName"]);
    }
    if(!$continue)continue;
    if(method_exists($object, 'set'.ucfirst($value["fieldName"]))){
      $object->{'set'.ucfirst($value["fieldName"])}($data);
    }

  }
  //Iterate all relation field
  foreach($entityManager->getClassMetadata($class)->associationMappings as $key=>$value){
    if(!isset($value["joinColumns"])) continue;
    $repository = $container->getDoctrine()->getRepository($value["targetEntity"]);
    $tempObject= $repository->find($request->query->get($value["fieldName"]));
    if(method_exists($object, 'set'.ucfirst($value["fieldName"]))){
      $object->{'set'.ucfirst($value["fieldName"])}($tempObject);
    }
  }
  try{
    $entityManager->persist($object);
    $entityManager->flush();
  }
  catch(Exception $e){
    return null;
  }
  return $object;
}

/**
* Read a JSON from file
*
* @param string $file Specifies full path of file
*
*/
function readJSON($path){
  if (is_readable($path) ) {
    // Read JSON file
    $json = file_get_contents($path);
    //Decode JSON
    $this->form = json_decode($json,true);
  }

}

/**
* Save form enconded with JSON on file
*
* @param string $file Specifies full path of file
*
*/
function saveJSON($path){
  $json=json_encode($this->form);
  file_put_contents("/Users/juanpedro/Documents/HTTP/axiom/src/Utils/Form/Json",$json);
}


/**
* Prepare form with all field and style
*/

function printForm(){

  //Step or Single form?
  //Open Panels
  //Group
  //Rows
  //Fields
  //Close panels
  //Close Step

  foreach ($this->form as $form){

    $this->openForm($form["name"]);//Open forms
    foreach ($form["step"] as $step){
      if(count($form["step"])>1){
        $this->addStep("");
      }
      foreach ($step["panels"] as $panels){

        $this->openPanel($panels["label"]);
        foreach ($panels["group"] as $group){
          if(count($group) > 1){
            $this->openGroup();
          }
          $size = 0;
          $lastorder = 0;
          //Fución orden

          usort ($group["fields"], array(__NAMESPACE__."\FormController","orderField"));
          $this->openRow();
          foreach ($group["fields"] as $field){
            if($field["order"] - $lastorder > 1 || $size+$field["size"] > 12){
              $size=0;
              $this->closeRow();
              $this->openRow();
            }
            $lastorder = $field["order"];
            if($field["visibility"]!="hidden"){
              $size += $field["size"];
            }
            switch ($field["type"]){
              case "submit":
              if($size>0){
                $size=0;
                $this->closeRow();
                $this->openRow();
              }
              case "text":
              $validation ="";
              if($field["nullable"]){
                $validation .= "required";
              }
              if(isset($field["lenght"])){
                $validation .= ",maxlength[".$field["length"]."]";
              }
              $this->addInput($field["type"], $field["name"], $field["label"],$field["placeholder"],$field["size"] ,$field["visibility"],$value='',$addon='',$validation);
              break;
              case "radio":
              case "checkbox":
              $this->addCheckbox($field["type"], $field["name"],$field["label"],$field["size"],$field["value"],"");
              break;
              case "select":
              if(isset($field["origin"])){
                $this->addSelect($field["type"], $field["name"], $field["label"], $field["size"], $field["value"], $field["origin"],0);
              }
              break;
              case "datetime":
              $this->addDateTime($field["name"], $field["label"],$field["visibility"], $field["value"],$field["enabled"],$validation);
              break;
              default:
              break;
            }

          }
          $this->closeRow();
          if(count($group) > 1){
            $this->closeGroup();
          }
        }
        $this->closePanel();
      }
      if(count($form["step"])>1){
        $this->closeStep();
      }
    }
    $this->closeForm();//Close forms

  }
}


/*======================================
==       Theme personalization        ==
========================================*/
/**
* Open a new Form
*
* @param string $name   Form name
* @param string $method Get/post
* @param string $action Action for submit form
* @param string $enctype Specifies how the form-data should be encoded when submitting it to the server
* @param string $onsubmit The onsubmit event occurs when a form is submitted.
* @return string Open Form with param
*
*/

function openForm($name){
  $this->fullform .= "<form name='".$name."' id='".$name."' action='' method='' enctype='' onsubmit='' class='validate'>";
  $this->formid =$name;
}
/**
* Open a panel on Form
*
* @param string $name   Panel name
* @return string Open Panel string
*
*/
function openPanel($name){
  $this->fullform .="<div class='panel panel-primary' data-collapsed='0'>";
  $this->fullform .="<div class='panel-heading'> <div class='panel-title'>".$name."</div></div>";
  $this->fullform .="<div class='panel-body'>";

}

/**
* Open a row for Panel or Form
*/
function openRow(){
  $this->fullform .="<div class='row'>";
}

/**
* Open a group form
*/
function openGroup(){
  $this->fullform .="<div class='form-group'>";
}

/**
* Add input on Forms
*@param string Input type
*@param string Input name
*@param string Input placeholder
*@param string Input label
*@param string Input value
*@return string Input type template
*/
function addInput($type, $name, $label,$placeholder='', $size='',$visibility, $value='',$addon='',$validation){
  $input = "<div class='form-group col-sm-".$size;
  if($visibility=="hidden"){
    $input .= "' style= 'display:none'>";
  }
  else{
    $input .= "'>";
  }
  if($type == "submit" || $type=="reset"){
    $input .= "<label></label>";
    $this->submitbutton = $name;
    $value = $label;
    $input .= "<input type='".$type."' name='".$name."' value='".$value."' id='".$name."' class='form-control'/>";
  }
  else{
    if ($addon !=''){
      $input .= "<span class='input-group-addon'>".$addon."</span>";
    }
    $input.= "<label>".$label."</label><br>";
    $input .= "<input type='".$type."' name='".$name."' value='".$value."' id='".$name."' class='form-control' data-validate='".$validation."'/>";

  }


  $input .="</div>";
  $this->fullform .= $input;
}

function addCheckbox($type, $name, $label, $size, $value,$icon){
  $this->serializeSwitches .= "+'&".$name."='+$(\"[name='".$name."']\").bootstrapSwitch('status')";
  $checkbox = "<div class='form-group'>";
  $checkbox .= "<label class='col-sm-3 control-label'>".$label."</label>";
  $checkbox .= "<div class='col-sm-5'>";
  $checkbox .= "<div name='".$name."' class='make-switch'>";
  $checkbox .= "<input type='checkbox'value='".$value."' checked=''>";
  $checkbox .="</div></div></div>";
  $this->fullform .= $checkbox;

}
function addcheckradio($type, $name, $label, $size, $values,$icon,$selected){
  $radio = "<div class='form-group col-sm-".$size."'>";
  if ($type=="checkbox"){
    $name = $name."[]";
  }
  $c=1;
  if($type =="checkbox"){
    $radio .= "<div class='checkbox checkbox-replace'>";
  }else if($this->$type =="radio" ){
    $radio .= "<div class='radio radio-replace'>";
  }
  foreach($values as $value){
    foreach($selected as $itemselected){

    }
  }
  while(list($val,$l)=each($this->value)){
    if ($c==$selected){
      $check = " checked/>";
    }
    else{
      $check = " />";
    }

    $this->radio .=  "<label>".$this->value[$val]."</label><input type='".$this->type."' name='".$this->name."' value='".$val."'".$this->check."<br>";
    $c++;
  }

  $this->radio .= "</div>";

  $this->fullform .= $this->radio;
  return  $this->radio;
}


function addTextarea($name, $cols=20, $rows=5, $label='',$value=''){
  $this->name=$name;
  $this->row= $rows;
  $this->col= $cols;
  $this->value = $value;
  $this->label = $label;

  $this->textarea = "<label>".$this->label."</label><br><textarea name='".$this->name."' cols='".$this->col."' rows='".$this->row."'>".$this->value."</textarea>";
  return $this->textarea;
}

function addSelect($type, $name, $label, $size, $values, $origin, $multiple=0){
  $select = "<div class='form-group col-sm-".$size."'>";

  if($multiple==1){
    $select .= "<label>".$label."</label><br><select name='".$name."[]' class='form-control multi-select' multiple='multiple'>";
  }
  else{
    $select .= "<label>".$label."</label><br><select name='".$name."' id='".$name."' class='form-control selectboxit'>";
  }
  if($values!=null && count($values)>1 ){
    foreach ($values as $value){
      $select .= "<option value='".$value."'>".$value."</option>";
    }
  }
  $select  .= "</select></div>";
  $this->fullform .= $select;
  if(count($values)==1 && isset($origin,$name,$values[0])){
    $script = "<script>if((++load_wait)==1) $(\"#load-spinner\").fadeIn();
    $.getJSON('".$origin."', function( data ){
      var selectedIndex=0;
      var i=0;
      $.each(data, function( key, val ) {
        $(\"#".$name."\").data(\"selectBox-selectBoxIt\").add({ value: key, text: val.name , id:'".$name."-option-'+key});
        if(val.name=='".$values[0]."') selectedIndex=i;
        i++;
      });
      $(\"#".$name."\").data(\"selectBox-selectBoxIt\").selectOption(selectedIndex);
    }).always(function() {
      if((--load_wait)==0) $(\"#load-spinner\").fadeOut();
    });
    </script>";
    $this->fullform .= $script;
  }
}
function addDatePicker($label){
  $this->datePicker = "<label class='control-label'>".$label."</label>";
  $this->datePicker .= "<input type='text' class='form-control datepicker' data-start-date='-2d' data-end-date='+1w'>";
}

function addDateTime( $name, $label,$visibility, $value='',$enabled=true,$validation){
  $datePicker = "<div class='form-group col-sm-3'>";
  $datePicker .= "<label >".$label."</label><br>";
  //$datePicker .= "<label class='col-sm-3 control-label'>Time &amp; Date Picker</label>";
  //$datePicker .= "<div class='col-sm-3'>";
  $datePicker .= "<div class='date-and-time'>";
  $enabledString = "";
  if(!$enabled){
    $enabledString ="disabled";
  }
  $datePicker .= "<input type='text' name='".$name."-date' id='".$name."-date' class='form-control datepicker' data-format='dd/mm/yyyy' ".$enabledString.">";
  $datePicker .= "<input type='text' name='".$name."-time' id='".$name."-time' class='form-control timepicker' data-template='dropdown' data-show-seconds='false' data-show-meridian='false' data-minute-step='5' data-second-step='5' ".$enabledString." />";
  $datePicker .= "</div> </div> ";
  $datePicker .="<script>$(document).ready(function(){
    $('#".$name."-date').datepicker(\"setValue\",new Date(\"dd/mm/yyyy\"));});</script>";
    $this->fullform .= $datePicker;
  }

  function addInputSpiner($label,$size,$dataMin,$dataMax,$dataStep=1){

    $this->spiner = "<label class='control-label'>".$label."</label>";

    $this->spiner .= "<div class='input-spinner'>";
    $this->spiner .= "<button type='button' class='btn btn-default' data-step='-".$dataStep."'>-</button>";
    $this->spiner .= "<input type='text' class='form-control size-".$size."' data-min='".$dataMin."' data-max='".$dataMax."' value='1' />";
    $this->spiner .= "<button type='button' class='btn btn-default' data-step='".$dataStep."'>+</button>";
    $this->spiner .= "</div>";

    $this->fullForm .= $this->spiner;
    return $this->spiner;
  }
  function addSlider($label){
    $this->slider = "<label class='control-label'>".$label."</label>";

    $this->slider .=	"<div class='slider slider-gold data-postfix=' $' data-min='0' data-max='2000' data-min-val='400' data-max-val='1400'></div>";
    $this->fullForm .= $this->slider;
    return $this->slider;
  }
  function addKnob($label,$value, $dataMax,$dataMin, $size,$sizeCircle, $bgColor, $fgColor){
    $this->knob = "<label class='control-label'>".$label."</label>";

    $this->knob .= "<input class='knob' data-min='".$dataMin."' data-max='".$dataMax."' data-width='".$size."' data-height='".$size."' data-thickness='".$sizeCircle."' data-fgColor='".$fgColor."' data-bgColor='".$bgColor."' value='".$value."'>";
    $this->fullform .= $this->knob;
    return $this->knob;
  }
  function openFieldset($texto,$width='300'){
    $this->legend=$texto;
    $this->width=$width;

    $this->fieldset="<fieldset style='width:".$this->width."px;'><legend>".$this->legend."</legend>";
    return $this->fieldset;
  }
  function closePanel(){
    $closePanel = "</div>"; //Close PanelBody
    $closePanel .="<div class='panel-footer text-right'>";
    $closePanel .= "<button class='btn btn-sm btn-red' type='submit' form='".$this->formid."' onclick='this.disabled='>
    <span class='glyphicon glyphicon-floppy-disk'></span> Guardar  </button>";
    $closePanel .= "</div></div>"; //Close Panel footer and full panel
    $this->fullform .= $closePanel;
  }
  function closeRow(){
    $this->fullform .="</div>";
  }
  function closeFieldset(){

    $this->fieldset="</fieldset>";
    return $this->fieldset;
  }
  function closeStep(){}
    function closeForm(){
      $this->fullform .= "</form>";
    }

    public function fullForm($routeData=null){
      $this->onsubmit="/api/global/companies/new"; //Test route
      $this->fullform .= "<script type='text/javascript'>";
      if($routeData!=null){
        $this->fullform .="
        if((++load_wait)==1) $(\"#load-spinner\").fadeIn();
        $.getJSON('".$routeData."', function( data ) {
          $.each( data, function( key, val ) {
            $(\"#\"+key).val(val);
          });
          replaceCheckboxes();
        }).always(function() {
          if((--load_wait)==0) $(\"#load-spinner\").fadeOut();
        });
        ";
      }

      $this->fullform .="
      $(document).ready(function() {
        $('#".$this->formid."').submit(function(e){
          e.preventDefault();
          var form = $(this);
          if(! form.valid()) return false;
          var action = window.location.origin + '".$this->onsubmit."';
          var dataString = $('#".$this->formid."').serialize()".$this->serializeSwitches.";
          $.ajax({
            type: 'GET',
            url: action,
            data: dataString,
            dataType: 'json',
            success: function(data)
            {
              if(data.result){
                toastr.success('Datos guardados correctamente', 'Confirmación', confirmation);
              }else toastr.error('Ocurrió un error al intentar guardar los datos', 'Confirmación', confirmation);
            }
          })

        });
      });
      </script>";
      return $this->fullform;
    }
  }
