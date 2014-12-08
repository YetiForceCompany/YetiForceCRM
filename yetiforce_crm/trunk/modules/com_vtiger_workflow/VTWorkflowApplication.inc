<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class VTWorkflowApplication{
	function __construct($action){
		global $_REQUEST;
		$this->request;
		$this->name = "com_vtiger_workflow";
		$this->label = "Workflow";
		$this->action = $action;
		$this->returnUrl = $_SERVER["REQUEST_URI"];
	}
	
	function currentUrl(){
		// $req = $this->request;
		// $url = "index.php?module={$this->name}&action={$this->action}";
		// if($this->action=='editworkflow'){
		// 	if(isset($req['workflow_id'])){
		// 		$url.="&workflow_id=".$req['workflow_id'];
		// 	}
		// }else if($this->action=='edittask'){
		// 	if(isset($req['task_id'])){
		// 		$url.="&task_id=".$req['task_id'];
		// 	}
		// }
		return $_SERVER["REQUEST_URI"];			
	}
	
	function returnUrl(){
		return $this->returnUrl;
	}
	
	function listViewUrl(){
		return "index.php?module={$this->name}&action=workflowlist";
	}
	
	function editWorkflowUrl($id=null){
		if($id!=null){
			$idPart="&workflow_id=$id";
		}
		return "index.php?module={$this->name}&action=editworkflow$idPart&return_url=".urlencode($this->returnUrl());
	}
	
	function deleteWorkflowUrl($id){
		$idPart="&workflow_id=$id";
		return "index.php?module={$this->name}&action=deleteworkflow$idPart&return_url=".urlencode($this->returnUrl());
	}
	
	function editTaskUrl($id=null){
		if($id!=null){
			$idPart="&task_id=$id";
		}
		return "index.php?module={$this->name}&action=edittask$idPart&return_url=".urlencode($this->returnUrl());
	}
	
	function deleteTaskUrl($id){
		$idPart="&task_id=$id";
		return "index.php?module={$this->name}&action=deletetask$idPart&return_url=".urlencode($this->returnUrl());
	}

	function setReturnUrl($returnUrl){
		$this->returnUrl = $returnUrl;
	}

	function errorPageUrl($message){
		return "index.php?module={$this->name}&action=errormessage&message=".urlencode($message);
	}
}
?>