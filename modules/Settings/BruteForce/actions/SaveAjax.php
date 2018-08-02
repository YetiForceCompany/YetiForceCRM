<?php

/**
 * Brute force save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author YetiForce.com
 */
class Settings_BruteForce_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveConfig');
		$this->exposeMethod('unBlock');
	}

	/**
	 * Function updates module configuration.
	 *
	 * @param \App\Request $request
	 */
	public function saveConfig(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		Settings_BruteForce_Module_Model::updateConfig($request->getArray('param', 'Integer'));
		$response = new Vtiger_Response();
		$response->setResult(['message' => \App\Language::translate('LBL_SAVE_SUCCESS', $moduleName)]);
		$response->emit();
	}

	/**
	 * Function unblocks user.
	 *
	 * @param \App\Request $request
	 */
	public function unBlock(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		if (!Settings_BruteForce_Module_Model::unBlock($request->getInteger('param'))) {
			$return = ['success' => false, 'message' => \App\Language::translate('LBL_UNBLOCK_FAIL', $moduleName)];
		} else {
			$return = ['success' => true, 'message' => \App\Language::translate('LBL_UNBLOCK_SUCCESS', $moduleName)];
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
