<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Entity\Contacts;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ContactsUtils;

class ContactsController extends Controller
{
	private $class=Contacts::class;

    /**
     * @Route("/{_locale}/admin/global/contacts", name="contacts")
     */
    public function index(RouterInterface $router,Request $request)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
    	$utils = new ContactsUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'currenciesController',
  				'interfaceName' => 'Contactos',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

    /**
    * @Route("/{_locale}/admin/global/contact/new", name="newContact")
    */

    public function newContact(Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $obj=new Contacts();
      $utils = new ContactsUtils();
      $editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "New", "fa fa-plus");
      return $this->render($editor["template"], $editor["vars"]);
    }

    /**
    * @Route("/{_locale}/admin/global/contact/{id}/edit", name="editContact")
    */
    public function editDepartment($id,Request $request)
      {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $repository = $this->getDoctrine()->getRepository($this->class);
        $obj=$repository->find($id);
        $utils = new ContactsUtils();
        $editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "Edit", "fa fa-edit");
        return $this->render($editor["template"], $editor["vars"]);
    }

    /**
    * @Route("/api/global/contact/{id}/get", name="getContacts")
    */
    public function getDepartment($id){
      $contact = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$contact) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($contact->encodeJson());
    }

  /**
   * @Route("/api/contact/list", name="contactlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(Contacts::class);
    $listUtils=new ListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Contacts.json"),true);
    $return=$listUtils->getRecords($repository,$request,$manager,$listFields, Contacts::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/contact/{id}/disable", name="disableContact")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/contact/{id}/enable", name="enableContact")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/contact/{id}/delete", name="deleteContact")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
