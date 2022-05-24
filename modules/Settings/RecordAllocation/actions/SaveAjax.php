<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::lockTracking();
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('removePanel');
	}

	public function save(App\Request $request)
	{
		Settings_Vtiger_Tracker_Model::lockTracking(false);
		Settings_Vtiger_Tracker_Model::addBasic('save');
		$qualifiedModuleName = $request->getModule(false);
		$data = $request->getMultiDimensionArray('param', [
			'module' => 'Alnum',
			'userid' => 'Integer',
			'type' => 'Alnum',
			'ids' => [
				'users' => ['Integer'],
				'groups' => ['Integer'],
			]
		]);
		$oldValues = Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($data['type'], $data['module']);
		$oldValues = array_merge((array) ($oldValues[$data['userid']]['users'] ?? []), (array) ($oldValues[$data['userid']]['groups'] ?? []));

		$moduleInstance = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$moduleInstance->set('type', $data['type']);
		$moduleInstance->save(array_filter($data));
		Settings_RecordAllocation_Module_Model::resetDataVariable();
		$newValues = Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($data['type'], $data['module']);
		$newValues = array_merge((array) ($newValues[$data['userid']]['users'] ?? []), (array) ($newValues[$data['userid']]['groups'] ?? []));
		$prevDetail['userId'] = implode(',', $oldValues);
		$newDetail['userId'] = implode(',', $newValues);

		Settings_Vtiger_Tracker_Model::addDetail($prevDetail, $newDetail);
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(true);
		$responceToEmit->emit();
	}

	public function removePanel(App\Request $request)
	{
		Settings_Vtiger_Tracker_Model::lockTracking(false);
		Settings_Vtiger_Tracker_Model::addBasic('delete');
		$data = $request->getMultiDimensionArray('param', [
			'module' => 'Alnum',
			'type' => 'Alnum',
		]);
		$moduleName = $data['module'];
		$qualifiedModuleName = $request->getModule(false);

		$moduleInstance = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$moduleInstance->set('type', $data['type']);
		$moduleInstance->remove($moduleName);

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(true);
		$responceToEmit->emit();
	}
}
