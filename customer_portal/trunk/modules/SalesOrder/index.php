<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
class SalesOrder extends BaseModule{

	/*****************************************************************************
	* Function: BaseModule::detail()
	* ****************************************************************************/
	public function detail($targetid,$display=true) 
    {
    	$this->targetid = $targetid;
		$sparams = array(
			'id' => $this->targetid, 
			'block'=>$this->module,
			'contactid'=>$_SESSION["loggeduser"]['id'],
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);
		$lmod = $GLOBALS["sclient"]->call('get_details', $sparams);
		//echo '<h2>request</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
		foreach($lmod[0][$this->module] as $ticketfield) {	
			$fieldlabel = $ticketfield['fieldlabel'];
			$fieldvalue = $ticketfield['fieldvalue'];
			$blockname = $ticketfield['blockname'];
			if(!isset($mod_infos[$blockname])) $mod_infos[$blockname]=array();
			$mod_infos[$blockname][]=array("label"=>$fieldlabel,"value"=>$fieldvalue);				
		}
		$iparams = array(
			'id' => "$targetid", 
			'module'=>$this->module,
			'contactid'=>$_SESSION["loggeduser"]['id'],
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);
		$products = $GLOBALS["sclient"]->call('get_inventory_products', $iparams);
		$data['products']=$products[0];
		
		$docs=$this->get_documents();
		if(isset($docs) && count($docs)>0) $mod_infos=array_merge($mod_infos, $docs);
		
		$data['recordinfo']=$mod_infos;
		if($display) Template::display($this->module,$data,'detail');
		else return $mod_infos;					
		
	}
}