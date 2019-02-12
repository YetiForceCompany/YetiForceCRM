<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */

class Settings_Vtiger_ConfigEditorSaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \ReflectionException
	 */
	public function process(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_ConfigModule_Model::getInstance();
		try {
			$configFile = new \App\ConfigFile('main');
			foreach (array_keys($moduleModel->listFields) as $fieldName) {
				if ($request->has($fieldName)) {
					$configFile->set($fieldName, $request->getRaw($fieldName));
				}
			}
			$configFile->create();
			$response->setResult(true);
		} catch (\Throwable $e) {
			$response->setError(\App\Language::translate('LBL_ERROR', $qualifiedModuleName));
		}
		$response->emit();
	}
}
