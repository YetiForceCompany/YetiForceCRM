<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Portal_EditAjax_View extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		if ($request->has('record')) {
			$recordId = $request->getInteger('record');
			$data = Portal_Module_Model::getRecord($recordId);
		} else {
			$recordId = null;
			$data = ['bookmarkName' => null, 'bookmarkUrl' => null];
		}

		$viewer->assign('RECORD', $recordId);
		$viewer->assign('BOOKMARK_NAME', $data['bookmarkName']);
		$viewer->assign('BOOKMARK_URL', $data['bookmarkUrl']);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('EditView.tpl', $moduleName);
	}
}
