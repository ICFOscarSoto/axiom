<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPBankAccounts;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPBankAccountsUtils;


class ERPBankAccountsController extends Controller
{
	private $class=ERPBankAccounts::class;
	private $utilsClass=ERPBankAccountsUtils::class;

    /**
     * @Route("/{_locale}/ERP/{id}/bankaccounts", name="bankaccounts")
     */
    public function bankaccounts($id, RouterInterface $router,Request $request)
    {
		   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
	 		 $supplier=$suppliersRepository->findOneBy(["id"=>$id]);
			 $bankaccountsRepository=$this->getDoctrine()->getRepository(ERPBankAccounts::class);

			 $bankAccounts=$bankaccountsRepository->findOneBy(["supplier"=>$id]);
			 if($bankAccounts)
			 {
			 $this_id=$bankAccounts->getId();
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$template=dirname(__FILE__)."/../Forms/BankAccounts.json";
			$formUtils = new GlobaleFormUtils();
			$listBankAccounts = new ERPBankAccountsUtils();
			$formUtils->initialize($this->getUser(), new ERPBankAccounts(), $template, $request, $this, $this->getDoctrine(),$listBankAccounts->getExcludedForm([]),$listBankAccounts->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]));
			$listBankAccounts->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]);
			$forms[]=$formUtils->formatForm('BankAccounts', true, $this_id, ERPBankAccounts::class);

			return $this->render('@Globale/list.html.twig', [
				'listConstructor' => $listBankAccounts->formatListbySupplier($this_id, $id),
				'forms' => $forms,
				'id' => $this_id,
				'id-parent' => $id,
				'include_post_templates' => [],
				'include_pre_templates' => ['@ERP/banckaccountSEPA.html.twig']
				]);

				}

				else{
				 $this_id=0;
				 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
				 $locale = $request->getLocale();
				 $this->router = $router;
				 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
				 $template=dirname(__FILE__)."/../Forms/BankAccounts.json";
				 $formUtils = new GlobaleFormUtils();
				 $listBankAccounts = new ERPBankAccountsUtils();
				 $formUtils->initialize($this->getUser(), new ERPBankAccounts(), $template, $request, $this, $this->getDoctrine(),$listBankAccounts->getExcludedForm([]),$listBankAccounts->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]));
				 $listBankAccounts->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]);
				// $forms[]=$formUtils->formatForm('BankAccounts', null, null, ERPBankAccounts::class);
				 return $this->render('@Globale/list.html.twig', [
					 'listConstructor' => $listBankAccounts->formatListbySupplier($this_id,$id),
					 'forms' => null,
					 'id' => $this_id,
					 'parent' => $id,
					 'include_post_templates' => [],
					 'include_pre_templates' => ['@ERP/banckaccountSEPA.html.twig']
					 ]);


					}

			/*
    	$utils = new ERPBankAccountsUtils();
  		$templateLists=$utils->formatListbyEntity($id);
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/BankAccounts.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('bankaccounts', true, $id, $this->class, "dataBankAccounts",["id"=>$id, "action"=>"save"]);
			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/list.html.twig', [
					'listConstructor' => $templateLists,
					'forms' => $templateForms,
					'entity_id' => $id,
					'include_post_templates' => [],
					'include_pre_templates' => ['@ERP/banckaccountSEPA.html.twig']
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
			*/
    }

		/**
		 * @Route("/{_locale}/bankaccount/data/{id}/{action}/{identity}", name="dataBankAccounts", defaults={"id"=0, "action"="read", "identity"=0})
		 */
		 public function data($id, $action, $identity, Request $request)
		 {
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/BankAccounts.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new $this->utilsClass();
		 if($identity==0) $identity=$request->query->get('entity');
		 $bankaccountRepository=$this->getDoctrine()->getRepository(ERPBankAccounts::class);
		 $obj=new $this->class();
		 if($id==0){
		 	if($identity==0 ) $identity=$request->query->get('entity');
		 	if($identity==0 || $identity==null) $identity=$request->request->get('id-parent',0);
		}else $obj = $bankaccountRepository->find($id);
		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
		 												method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		 return $utils->make($id, $this->class, $action, "formIdentities", "modal");
		}

    /**
    * @Route("/api/global/bankaccount/{id}/get", name="getBankAccount")
    */
    public function getBankAccount($id){
     $bankaccount = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$bankaccount) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($bankaccount->encodeJson());
    }

  /**
   * @Route("/api/bankaccount/{id}/{supplierid}/list", name="bankaccountlist")
   */
  public function indexlist(RouterInterface $router,Request $request, $id, $supplierid){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPBankAccounts::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/BankAccounts.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPBankAccounts::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplierid]]);
    return new JsonResponse($return);

  }

	/**
	* @Route("/{_locale}/admin/global/bankaccount/{id}/disable", name="disableBankAccount")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/bankaccount/{id}/enable", name="enableBankAccount")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/bankaccount/{id}/delete", name="deleteBankAccount")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
