<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_ModuleManager_ModuleExport_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('exportModule');
	}

	/**
	 * Action to export module.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected function exportModule(App\Request $request)
	{
		$moduleName = $request->getByType('forModule', 2);
		$moduleModel = \vtlib\Module::getInstance($moduleName);
		if (!$moduleModel->isExportable()) {
			throw new \App\Exceptions\AppException('ERR_NOT_ACCESSIBLE');
		}
		$package = new vtlib\PackageExport();
		$package->export($moduleModel, '', sprintf('%s-%s.zip', $moduleModel->name, $moduleModel->version), true);
	}
}
