<?php

/**
 * Settings ModTracker save action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ModTracker_Save_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('changeActiveStatus');
	}

	public function changeActiveStatus(App\Request $request)
	{
		$status = $request->getBoolean('status');
		$moduleModel = new Settings_ModTracker_Module_Model();
		$moduleModel->changeActiveStatus($request->getInteger('id'), (int) $status);
		$response = new Vtiger_Response();
		if ($status) {
			$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_TRACK_CHANGES_ENABLED', $request->getModule(false))]);
		} else {
			$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_TRACK_CHANGES_DISABLE', $request->getModule(false))]);
		}
		$response->emit();
	}
}
