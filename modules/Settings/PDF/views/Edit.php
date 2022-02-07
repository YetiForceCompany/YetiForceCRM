<?php

/**
 * Edit View Class for PDF Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_PDF_Edit_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$step = strtolower($request->getMode());
		$this->step($step, $request);
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule(false));
	}

	public function step($step, App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record', true)) {
			$pdfModel = Vtiger_PDF_Model::getInstanceById($request->getInteger('record'));
			$viewer->assign('RECORDID', $request->getInteger('record'));
			$viewer->assign('MODE', 'edit');
			$selectedModuleName = $pdfModel->get('module_name');
		} else {
			$selectedModuleName = $request->getByType('source_module', 2);
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance();
		}
		$viewer->assign('SELECTED_MODULE', $selectedModuleName);
		$viewer->assign('PDF_MODEL', $pdfModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		switch ($step) {
			case 'step3':
				$moduleModel = Vtiger_Module_Model::getInstance($pdfModel->get('module_name'));
				$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
				$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
				$viewer->assign('ADVANCE_CRITERIA', Vtiger_AdvancedFilter_Helper::transformToAdvancedFilterCondition($pdfModel->get('conditions')));
				$viewer->view('Step3.tpl', $qualifiedModuleName);
				break;
			case 'step2':
				$viewer->view('Step2.tpl', $qualifiedModuleName);
				break;
			case 'step1':
			default:
				$viewer->assign('ALL_MODULES', Settings_PDF_Module_Model::getSupportedModules());
				$viewer->view('Step1.tpl', $qualifiedModuleName);
				break;
		}
	}

	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard',
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit",
			"modules.Settings.$moduleName.resources.Edit1",
			"modules.Settings.$moduleName.resources.Edit2",
			"modules.Settings.$moduleName.resources.Edit3",
			'modules.Vtiger.resources.AdvanceFilter',
			'modules.Vtiger.resources.AdvanceFilterEx',
		]));
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'modules.Settings.' . $request->getModule() . '.Edit',
		]), parent::getHeaderCss($request));
	}
}
