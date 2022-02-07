<?php

/**
 * Generate modal view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Generate modal view class.
 */
class Vtiger_GenerateModal_View extends Vtiger_BasicModal_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		echo '<div class="generateMappingModal modal fade"><div class="modal-dialog"><div class="modal-content">';
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$view = $request->getByType('fromview', 1);
		$viewer = $this->getViewer($request);
		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
		$mfModel = new $handlerClass();
		if ('List' === $view) {
			$allRecords = Vtiger_Mass_Action::getRecordsListFromRequest($request);
			$templates = $mfModel->getActiveTemplatesForModule($moduleName, $view);
			$viewer->assign('ALL_RECORDS', $allRecords);
		} else {
			$recordId = $request->getInteger('record');
			$templates = $mfModel->getActiveTemplatesForRecord($recordId, $view, $moduleName);
		}
		$viewer->assign('RECORD', $recordId ?? '');
		$viewer->assign('TEMPLATES', $templates);
		$viewer->assign('VIEW', $view);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('BASE_MODULE_NAME', 'Vtiger');
		$this->preProcess($request);
		$viewer->view('GenerateModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
