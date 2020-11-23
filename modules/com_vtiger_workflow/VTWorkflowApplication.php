<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class VTWorkflowApplication
{
	public function __construct($action)
	{
		$this->request;
		$this->name = 'com_vtiger_workflow';
		$this->label = 'Workflow';
		$this->action = $action;
		$this->returnUrl = \App\Request::_getServer('REQUEST_URI');
	}

	public function currentUrl()
	{
		return \App\Request::_getServer('REQUEST_URI');
	}

	public function returnUrl()
	{
		return $this->returnUrl;
	}

	public function listViewUrl()
	{
		return "index.php?module={$this->name}&action=workflowlist";
	}

	public function editWorkflowUrl($id = null)
	{
		if (null !== $id) {
			$idPart = "&workflow_id=$id";
		}
		return "index.php?module={$this->name}&action=editworkflow$idPart&return_url=" . urlencode($this->returnUrl());
	}

	public function deleteWorkflowUrl($id)
	{
		$idPart = "&workflow_id=$id";

		return "index.php?module={$this->name}&action=deleteworkflow$idPart&return_url=" . urlencode($this->returnUrl());
	}

	public function editTaskUrl($id = null)
	{
		if (null !== $id) {
			$idPart = "&task_id=$id";
		}
		return "index.php?module={$this->name}&action=edittask$idPart&return_url=" . urlencode($this->returnUrl());
	}

	public function deleteTaskUrl($id)
	{
		$idPart = "&task_id=$id";

		return "index.php?module={$this->name}&action=deletetask$idPart&return_url=" . urlencode($this->returnUrl());
	}

	public function setReturnUrl($returnUrl)
	{
		$this->returnUrl = $returnUrl;
	}

	public function errorPageUrl($message)
	{
		return "index.php?module={$this->name}&action=errormessage&message=" . urlencode($message);
	}
}
