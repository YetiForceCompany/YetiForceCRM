<?php

/**
 * Validate selected records for export to pdf Class for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_ValidateRecords_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$forModule = $request->get('for_module');
		$view = $request->get('view');
		$records = $request->get('records');
		$templates = $request->get('templates');
		$allRecords = count($records);

		$output = ['valid_records' => [], 'message' => vtranslate('LBL_VALID_RECORDS', $moduleName, 0, $allRecords)];

		if (!empty($templates)) {
			foreach ($templates as $templateId) {
				$templateRecord = Settings_PDF_Record_Model::getInstanceById($templateId);

				foreach ($records as $recordId) {
					if (!$templateRecord->checkFiltersForRecord(intval($recordId))) {// echo 'nie spełnia warunków<br>';//exit;
						if (($key = array_search($recordId, $records)) !== false) {
							unset($records[$key]);
						}
					}
				}
			}
			$selectedRecords = count($records);

			$output = ['valid_records' => $records, 'message' => vtranslate('LBL_VALID_RECORDS', $moduleName, $selectedRecords, $allRecords)];
		}
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
