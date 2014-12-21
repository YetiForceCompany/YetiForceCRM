<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
class Faq extends BaseModule{

	function get_list(){
		$allow_all = $GLOBALS["sclient"]->call('show_all',array('module'=>$this->module));
		if($allow_all!='true') $onlymine="true";
		
		$params = array(array('id' => $_SESSION["loggeduser"]['id'], 'sessionid'=>$_SESSION["loggeduser"]['sessionid']));
		$result = $GLOBALS["sclient"]->call('get_KBase_details', $params);
		
		$data['faqcategories']=$result[0];
		$data['faqproducts']=$result[1];
		$data['faqs']=$result[2];
		
		$faqspercat=array();
		if(is_array($data['faqs'])){
			foreach($data['faqs'] as $faq){
				if(!isset($faqspercat[$faq["category"]])) $faqspercat[$faq["category"]]=array();
				
				$params = array('id' => $faq["id"] ,'module' => "Documents",'contactid' => $_SESSION["loggeduser"]['id'], 'sessionid'=>$_SESSION["loggeduser"]['sessionid']);
				$resultb = $GLOBALS["sclient"]->call('get_documents', $params);
				
				if(isset($resultb) && count($resultb)>0 && $resultb!="") 
				foreach($resultb[1]['Documents']['data'] as $doc) $faq["attachments"][]=$doc;
				
				$faqspercat[$faq["category"]][]=$faq;
			}
		}
		$faqpr=array();
		if(is_array($data['faqproducts'])){
			foreach($data['faqproducts'] as $faqprod){
				if(!isset($faqpr[$faqprod["productid"]])) $faqpr[$faqprod["productid"]]=array();
				$faqpr[$faqprod["productid"]]=$faqprod;
			}
		}
		
		$data['faqs']=$faqspercat;
		$data['faqproducts']=$faqpr;
		Template::display($this->module,$data,'list');
	}
}