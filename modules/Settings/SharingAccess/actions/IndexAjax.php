<?php

/**
 * Settings SharingAccess IndexAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_SharingAccess_IndexAjax_Action extends Settings_Vtiger_Save_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::lockTracking();
		parent::__construct();
		$this->exposeMethod('saveRule');
		$this->exposeMethod('deleteRule');
	}

	public function saveRule(App\Request $request)
	{
		Settings_Vtiger_Tracker_Model::lockTracking(false);
		Settings_Vtiger_Tracker_Model::addBasic('save');
		$forModule = $request->getByType('for_module', 2);
		\App\Privilege::setUpdater($forModule);
		$moduleModel = Settings_SharingAccess_Module_Model::getInstance($forModule);
		if ($request->isEmpty('record')) {
			$ruleModel = new Settings_SharingAccess_Rule_Model();
			$ruleModel->setModuleFromInstance($moduleModel);
		} else {
			$ruleModel = Settings_SharingAccess_Rule_Model::getInstance($moduleModel, $request->getInteger('record'));
		}
		$prevValues['permission'] = $ruleModel->getPermission();
		$newValues['permission'] = $request->getInteger('permission');
		Settings_Vtiger_Tracker_Model::addDetail($prevValues, $newValues);
		$ruleModel->set('source_id', $request->getByType('source_id', 'Text'));
		$ruleModel->set('target_id', $request->getByType('target_id', 'Text'));
		$ruleModel->set('permission', $request->getInteger('permission'));
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		try {
			$ruleModel->save();
			$response->setResult([
				'success' => true,
				'message' => \App\Language::translate('LBL_CUSTOM_RULE_SAVED_SUCCESSFULLY', $request->getModule(false))
			]);
		} catch (\App\Exceptions\AppException $e) {
			$response->setError(\App\Language::translate('LBL_CUSTOM_RULE_SAVED_FAILED', $request->getModule(false)));
		}
		$response->emit();
	}

	public function deleteRule(App\Request $request)
	{
		Settings_Vtiger_Tracker_Model::lockTracking(false);
		Settings_Vtiger_Tracker_Model::addBasic('delete');
		$forModule = $request->getByType('for_module', 2);
		\App\Privilege::setUpdater(\App\Module::getModuleName($forModule));
		$moduleModel = Settings_SharingAccess_Module_Model::getInstance($forModule);
		$ruleModel = Settings_SharingAccess_Rule_Model::getInstance($moduleModel, $request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		try {
			$ruleModel->delete();
		} catch (\App\Exceptions\AppException $e) {
			$response->setError(\App\Language::translate('LBL_CUSTOM_RULE_DELETING_FAILED', $request->getModule(false)));
		}
		$response->emit();
	}
}
