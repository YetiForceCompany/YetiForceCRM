<?php

/**
 * Import View Class for Workflows Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_Workflows_Import_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		
		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);

		if ($request->has('upload') && $request->get('upload') == 'true') {
			$xmlName = $_FILES['imported_xml']['name'];
			$uploadedXml = $_FILES['imported_xml']['tmp_name'];
			$xmlError = $_FILES['imported_xml']['error'];
			$extension = end(explode('.', $xmlName));

			if ($xmlError == UPLOAD_ERR_OK && $extension === 'xml') {
				$xml = simplexml_load_file($uploadedXml);

				$params = [];
				$taskIndex = $methodIndex = 0;
				foreach ($xml as $fieldsKey => $fieldsValue) {
					foreach ($fieldsValue as $fieldKey => $fieldValue) {
						foreach ($fieldValue as $columnKey => $columnValue) {
							if ($columnKey === 'conditions') {
								$columnKey = 'test';
							} else if ($columnKey == 'type' && empty($columnValue)) {
								$columnValue = 'basic';
							}
							switch ($fieldKey) {
								case 'workflow_method':
									$params[$fieldsKey][$methodIndex][$columnKey] = (string) $columnValue;
									break;

								case 'workflow_task':
									$params[$fieldsKey][$taskIndex][$columnKey] = (string) $columnValue;
									break;

								default:
									$params[$fieldsKey][$columnKey] = (string) $columnValue;
							}
						}
						if ($fieldKey === 'workflow_task') {
							$taskIndex++;
						} elseif ($fieldKey === 'workflow_method') {
							$methodIndex++;
						}
					}
				}
				$workflowModel = Settings_Workflows_Module_Model::getInstance('Settings:Workflows');
				$messages = $workflowModel->importWorkflow($params);

				$viewer->assign('RECORDID', $messages['id']);
				$viewer->assign('UPLOAD', true);
				$viewer->assign('MESSAGES', $messages);
			} else {
				$viewer->assign('UPLOAD_ERROR', vtranslate('LBL_UPLOAD_ERROR', $qualifiedModule));
				$viewer->assign('UPLOAD', false);
			}
		}

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Import.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = [
			"modules.Settings.$moduleName.Import",
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($cssInstances, $headerCssInstances);
	}
}
