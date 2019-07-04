<?php
namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleClockDevices;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleCompaniesUtils;
//use App\Modules\Globale\UtilsEntityUtils;
//use App\Modules\Form\Controller\FormController;

class GlobaleClockDevicesController extends Controller
{
  private $class=GlobaleClockDevices::class;
  /**
   * @Route("/{_locale}/globale/clockdevices/form/{id}", name="clockdevices", defaults={"id"=0})
   */
   public function clockdevices($id, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
    $template=dirname(__FILE__)."/../Forms/ClockDevices.json";
    $userdata=$this->getUser()->getTemplateData();
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('genericindex', "Globale", "ClockDevices");
    array_push($breadcrumb, $new_breadcrumb);
    $clockDevicesRepository=$this->getDoctrine()->getRepository(GlobaleClockDevices::class);
    $obj = $clockDevicesRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'active'=>1, 'deleted'=>0]);
    if($id!=0 && $obj==null){
        return $this->render('@Globale/notfound.html.twig',[
          "status_code"=>404,
          "status_text"=>"Objeto no encontrado"
        ]);
    }
    $entity_name=$obj?($obj->getName()!=null?$obj->getName():$obj->getModel().' (sn: '.$obj->getSerialnumber().')'):'';
    return $this->render('@Globale/generictabform.html.twig', array(
            'entity_name' => $entity_name,
            'controllerName' => 'ClockDevicesController',
            'interfaceName' => 'Dispositivos',
            'optionSelected' => 'genericindex',
            'optionSelectedParams' => ["module"=>"Globale", "name"=>"ClockDevices"],
            'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $id,
            'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
            'tabs' => [["name" => "data", "caption"=>"Datos dispositivo", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("genericdata",["type"=>"full","module"=>"Globale","name"=>"ClockDevices","id"=>$id])],
                       ["name" => "deviceworkers", "icon"=>"fa fa-headphones", "caption"=>"Relación usuario", "route"=>$this->generateUrl("clockdevicesworker",["id"=>$id])]
                      ],
            'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
            'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
            /*'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>$tab=='data'?true:false, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
                       ["name" => "paymentroll", "active"=>($tab=='paymentroll' && $id)?true:false, "caption"=>"Nóminas"]
                      ]*/
    ));
  }

  /**
  * @Route("/{_locale}/admin/global/clockdevices/{id}/delete", name="deleteClockDevice", defaults={"id"=0})
  */
  public function delete($id, Request $request){
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $entityUtils=new GlobaleEntityUtils();
    if($id!=0) $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
     else {
       $ids=$request->request->get('ids');
       $ids=explode(",",$ids);
       foreach($ids as $item){
         $result=$entityUtils->deleteObject($item, $this->class, $this->getDoctrine());
       }
     }
    return new JsonResponse(array('result' => $result));
  }


}
