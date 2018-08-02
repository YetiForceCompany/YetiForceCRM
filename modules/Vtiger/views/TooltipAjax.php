<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_TooltipAjax_View extends \App\Controller\View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$tooltipViewModel = Vtiger_TooltipView_Model::getInstance($moduleName, $request->getInteger('record'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_MODEL', $tooltipViewModel->getRecord()->getModule());
		$viewer->assign('RECORD', $tooltipViewModel->getRecord());
		$viewer->assign('RECORD_STRUCTURE', $tooltipViewModel->getStructure());
		$viewer->view('TooltipContents.tpl', $moduleName);
	}
}
