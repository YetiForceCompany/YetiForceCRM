<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CustomView_Delete_Action extends Vtiger_Action_Controller
{
	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!CustomView_Record_Model::getInstanceById($request->get('record')->isDeletable())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}
	/**
	 * Main function of action
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));
		$customViewModel->delete();
		$listViewUrl = $customViewModel->getModule()->getListViewUrl();
		header("Location: $listViewUrl");
	}

	/**
	 * Validate request
	 * @param \App\Request $request
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
