<?php

/**
 * Import View Class for Workflows Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_Workflows_Import_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);

		if ($request->has('upload') && 'true' == $request->get('upload')) {
			$xmlName = $_FILES['imported_xml']['name'];
			$uploadedXml = $_FILES['imported_xml']['tmp_name'];
			$xmlError = $_FILES['imported_xml']['error'];
			$explodeXmlName = explode('.', $xmlName);
			$extension = end($explodeXmlName);
			if (UPLOAD_ERR_OK == $xmlError && 'xml' === $extension) {
				$xml = simplexml_load_file($uploadedXml);

				$params = [];
				$taskIndex = $methodIndex = 0;
				foreach ($xml as $fieldsKey => $fieldsValue) {
					foreach ($fieldsValue as $fieldKey => $fieldValue) {
						foreach ($fieldValue as $columnKey => $columnValue) {
							if ('conditions' === $columnKey) {
								$columnKey = 'test';
							} elseif ('type' == $columnKey && empty($columnValue)) {
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
						if ('workflow_task' === $fieldKey) {
							++$taskIndex;
						} elseif ('workflow_method' === $fieldKey) {
							++$methodIndex;
						}
					}
				}
				$workflowModel = Settings_Workflows_Module_Model::getInstance('Settings:Workflows');
				$messages = $workflowModel->importWorkflow($params);

				$viewer->assign('RECORDID', $messages['id']);
				$viewer->assign('UPLOAD', true);
				$viewer->assign('MESSAGES', $messages);
			} else {
				$viewer->assign('UPLOAD_ERROR', \App\Language::translate('LBL_UPLOAD_ERROR', $qualifiedModule));
				$viewer->assign('UPLOAD', false);
			}
		}

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Import.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}

	public function getHeaderCss(App\Request $request)
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
