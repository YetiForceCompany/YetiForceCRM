<?php

/**
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_GenerateRecords_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		//TODO Add permission
	}

	function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$records = $request->get('records');
		$template = $request->get('template');
		$targetModuleName = $request->get('target');
		$method = $request->get('method');
		$allRecords = count($records);
		$recordsOk = [];
		if (!empty($template)) {
			$templateRecord = Vtiger_MappedFields_Model::getInstanceById($template);
			foreach ($records as $recordId) {
				if ($templateRecord->checkFiltersForRecord(intval($recordId))) {
					if ($method == 0) {
						$recordModel = Vtiger_Record_Model::getCleanInstance($targetModuleName);
						$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
						$recordModel->setRecordFieldValues($parentRecordModel);
						$recordModel->save();
						if (isRecordExists($recordModel->getId())) {
							$success[] = $recordId;
						}
					} else {
						$success[] = $recordId;
					}
				}
			}
		}
		$output = ['all' => count($records), 'ok' => $success, 'fail' => array_diff($records, $success)];
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
