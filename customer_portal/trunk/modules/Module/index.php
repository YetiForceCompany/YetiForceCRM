<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

class BaseModule{
	var $module;
	var $targetid;
	
	/*****************************************************************************
	* Function: BaseModule::__construct()
	* ****************************************************************************/
	public function __construct($module=false,$targetid=false){
		if(!$module) $this->module=get_class($this);
		else $this->module=$module;
	}

	/*****************************************************************************
	* Function: BaseModule::get_list()
	* ****************************************************************************/
	public function get_list() 
    {
    	$allow_all = $GLOBALS["sclient"]->call('show_all',array('module'=>$this->module));
		if($allow_all!='true') $onlymine="true";
		$sparams = array(
			'id' => $_SESSION["loggeduser"]['id'], 
			'block'=>$this->module,
			'sessionid'=>$_SESSION["loggeduser"]['sessionid'],
			'onlymine'=>$onlymine
		);
		$lmod = $GLOBALS["sclient"]->call('get_list_values', $sparams);
		//echo '<h2>request</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
		if(isset($lmod) && count($lmod)>0 && $lmod!=""){
			$data['recordlist']=$lmod[1][$this->module]['data'];
			$data['tableheader']=$lmod[0][$this->module]['head'][0];
		}
		Template::display($this->module,$data,'list');
	}

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
		
		$docs=$this->get_documents();
		if(isset($docs) && count($docs)>0) $mod_infos=array_merge($mod_infos, $docs);
		
		$data['recordinfo']=$mod_infos;
		if($display) Template::display($this->module,$data,'detail');
		else return $mod_infos;					
		
	}

	/*****************************************************************************
	* Function: BaseModule::get_documents()
	* ****************************************************************************/
	public function get_documents(){
		$params = Array(
			'id' => $this->targetid ,
			'module' => "Documents",
			'contactid' => $_SESSION["loggeduser"]['id'], 
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);
		
		$resultb = $GLOBALS["sclient"]->call('get_documents', $params);

		if(isset($resultb) && count($resultb)>0 && $resultb!="" && $resultb[0] != '#MODULE INACTIVE#'){
			$ca=0;
			foreach($resultb[1]['Documents']['data'] as $doc){ 
				$mod_infos["Attachments"][$ca]['label']="File"; 
				$mod_infos["Attachments"][$ca]['value']=$doc[1]['fielddata']; 
				$ca++;
			}
		}
		return $mod_infos;
	}
}