<?php

/**
 * Settings RealizationProcesses SaveGeneral action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_RealizationProcesses_SaveGeneral_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
	}

	/**
	 * Save date.
	 *
	 * @param <array> request
	 * @param \App\Request $request
	 *
	 * @return true if saved, false otherwise
	 */
	public function save(App\Request $request)
	{
		$response = new Vtiger_Response();
		$status = $request->getByType('status', 'Text');
		$moduleId = $request->getInteger('moduleId');
		$moduleName = $request->getModule(false);
		try {
			if (Settings_RealizationProcesses_Module_Model::updateStatusNotModify($moduleId, $status)) {
				$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_CONFIG_OK', $moduleName)]);
			} else {
				$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_SAVE_CONFIG_ERROR', $moduleName)]);
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
