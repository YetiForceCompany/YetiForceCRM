<?php

/**
 * Edit View Class for PDF Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_Edit_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$step = strtolower($request->getMode());
		$this->step($step, $request);
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule(false));
	}

	public function step($step, Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('record');
		if ($recordId) {
			$pdfModel = Vtiger_PDF_Model::getInstanceById($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODE', 'edit');
			$selectedModuleName = $pdfModel->get('module_name');
		} else {
			$selectedModuleName = $request->get('source_module');
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance();
		}
		$viewer->assign('SELECTED_MODULE', $selectedModuleName);
		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		switch ($step) {
			case 'step8':
				$viewer->assign('WATERMARK_TEXT', Vtiger_mPDF_Pdf::WATERMARK_TYPE_TEXT);
				$viewer->view('Step8.tpl', $qualifiedModuleName);
				break;

			case 'step7':
				$viewer->view('Step7.tpl', $qualifiedModuleName);
				break;

			case 'step6':
				$moduleModel = Vtiger_Module_Model::getInstance($pdfModel->get('module_name'));
				$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
				$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
				$viewer->assign('ADVANCE_CRITERIA', Vtiger_AdvancedFilter_Helper::transformToAdvancedFilterCondition($pdfModel->get('conditions')));
				$viewer->view('Step6.tpl', $qualifiedModuleName);
				break;

			case 'step5':
				$insertOperations = [
					'PAGENO' => 'PAGENO',
					'PAGENUM' => 'nb'
				];
				$viewer->assign('INSERT', $insertOperations);
				$viewer->view('Step5.tpl', $qualifiedModuleName);
				break;

			case 'step4':
				$viewer->view('Step4.tpl', $qualifiedModuleName);
				break;

			case 'step3':
				$insertOperations = [
					'PAGENO' => 'PAGENO',
					'PAGENUM' => 'nb'
				];
				$viewer->assign('INSERT', $insertOperations);
				$viewer->view('Step3.tpl', $qualifiedModuleName);
				break;

			case 'step2':
				$viewer->view('Step2.tpl', $qualifiedModuleName);
				break;

			case 'step1':
			default:
				$allModules = Settings_PDF_Module_Model::getSupportedModules();
				$viewer->assign('ALL_MODULES', $allModules);
				$viewer->view('Step1.tpl', $qualifiedModuleName);
				break;
		}
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = [
			'libraries.jquery.clipboardjs.clipboard',
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit",
			"modules.Settings.$moduleName.resources.Edit1",
			"modules.Settings.$moduleName.resources.Edit2",
			"modules.Settings.$moduleName.resources.Edit3",
			"modules.Settings.$moduleName.resources.Edit4",
			"modules.Settings.$moduleName.resources.Edit5",
			"modules.Settings.$moduleName.resources.Edit6",
			"modules.Settings.$moduleName.resources.Edit7",
			"modules.Settings.$moduleName.resources.Edit8",
			'modules.Vtiger.resources.AdvanceFilter',
			'modules.Vtiger.resources.AdvanceFilterEx',
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();
		$cssFileNames = [
			"modules.Settings.$moduleName.Edit",
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($cssInstances, $headerCssInstances);
		return $headerCssInstances;
	}
}
