<?php

/**
 * Settings SharingAccess IndexAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	public function saveRule(\App\Request $request)
	{
		Settings_Vtiger_Tracker_Model::lockTracking(false);
		Settings_Vtiger_Tracker_Model::addBasic('save');
		$forModule = $request->get('for_module');
		$ruleId = $request->get('record');

		\App\Privilege::setUpdater($forModule);
		$moduleModel = Settings_SharingAccess_Module_Model::getInstance($forModule);
		if (empty($ruleId)) {
			$ruleModel = new Settings_SharingAccess_Rule_Model();
			$ruleModel->setModuleFromInstance($moduleModel);
		} else {
			$ruleModel = Settings_SharingAccess_Rule_Model::getInstance($moduleModel, $ruleId);
		}

		$prevValues['permission'] = $ruleModel->getPermission();
		$newValues['permission'] = $request->get('permission');
		Settings_Vtiger_Tracker_Model::addDetail($prevValues, $newValues);

		$ruleModel->set('source_id', $request->get('source_id'));
		$ruleModel->set('target_id', $request->get('target_id'));
		$ruleModel->set('permission', (int) $request->get('permission'));

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		try {
			$ruleModel->save();
		} catch (\App\Exceptions\AppException $e) {
			$response->setError('Saving Sharing Access Rule failed');
		}
		$response->emit();
	}

	public function deleteRule(\App\Request $request)
	{
		Settings_Vtiger_Tracker_Model::lockTracking(false);
		Settings_Vtiger_Tracker_Model::addBasic('delete');
		$forModule = $request->get('for_module');
		$ruleId = $request->get('record');

		\App\Privilege::setUpdater(\App\Module::getModuleName($forModule));
		$moduleModel = Settings_SharingAccess_Module_Model::getInstance($forModule);
		$ruleModel = Settings_SharingAccess_Rule_Model::getInstance($moduleModel, $ruleId);

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		try {
			$ruleModel->delete();
		} catch (\App\Exceptions\AppException $e) {
			$response->setError('Deleting Sharing Access Rule failed');
		}
		$response->emit();
	}
}
