<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

Class Settings_PBXManager_Edit_View extends Settings_Vtiger_Index_View
{

	public function __construct()
	{
		$this->exposeMethod('showPopup');
	}

	public function process(Vtiger_Request $request)
	{
		$this->showPopup($request);
	}

	public function showPopup(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		if ($id) {
			$recordModel = Settings_PBXManager_Record_Model::getInstanceById($id, $qualifiedModuleName);
		} else {
			$recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
		}
		$viewer->assign('RECORD_ID', $id);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $request->getModule(false));
		$viewer->view('Edit.tpl', $request->getModule(false));
	}
}
