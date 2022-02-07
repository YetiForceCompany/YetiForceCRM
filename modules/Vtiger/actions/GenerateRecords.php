<?php

/**
 * Generate records file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Generate records class.
 */
class Vtiger_GenerateRecords_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getByType('target'), 'CreateView') || !$userPriviligesModel->hasModuleActionPermission($request->getModule(), 'RecordMappingList')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function checkMandatoryFields($recordModel)
	{
		$mandatoryFields = $recordModel->getModule()->getMandatoryFieldModels();
		foreach ($mandatoryFields as $field) {
			if (empty($recordModel->get($field->getName()))) {
				return true;
			}
		}
		return false;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$records = $request->getArray('records', 'Integer');
		$moduleName = $request->getModule();
		$template = $request->getInteger('template');
		$targetModuleName = $request->getByType('target');
		$method = $request->getInteger('method');
		$success = [];
		if (!empty($template)) {
			$templateRecord = Vtiger_MappedFields_Model::getInstanceById($template);
			foreach ($records as $recordId) {
				if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && $templateRecord->checkFiltersForRecord((int) $recordId)) {
					if (0 == $method) {
						$recordModel = Vtiger_Record_Model::getCleanInstance($targetModuleName);
						$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
						$recordModel->setRecordFieldValues($parentRecordModel);
						if ($this->checkMandatoryFields($recordModel)) {
							continue;
						}
						$recordModel->save();
						if (\App\Record::isExists($recordModel->getId())) {
							$success[] = $recordId;
						}
					} else {
						$success[] = $recordId;
					}
				}
			}
		}
		$output = ['all' => \count($records), 'ok' => $success, 'fail' => array_diff($records, $success)];
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
