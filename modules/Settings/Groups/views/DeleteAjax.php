<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Groups_DeleteAjax_View extends Settings_Vtiger_Index_View
{
	use App\Controller\ClearProcess;

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RECORD_MODEL', Settings_Groups_Record_Model::getInstance($request->getInteger('record')));
		$viewer->assign('ALL_USERS', Users_Record_Model::getAll());
		$viewer->assign('ALL_GROUPS', Settings_Groups_Record_Model::getAll());
		$viewer->view('DeleteTransferForm.tpl', $qualifiedModuleName);
	}
}
