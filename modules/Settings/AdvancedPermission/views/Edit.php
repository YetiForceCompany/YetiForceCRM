<?php

/**
 * Advanced permission edit view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Edit_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		} else {
			$this->step1($request);
		}
	}

	/**
	 * Edit view first step.
	 *
	 * @param \App\Request $request
	 */
	public function step1(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		if ($request->isEmpty('record')) {
			$recordModel = new Settings_AdvancedPermission_Record_Model();
		} else {
			$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->getInteger('record'));
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_ID', $recordModel->getId());
		$viewer->view('EditViewS1.tpl', $qualifiedModuleName);
	}

	/**
	 * Edit view second step.
	 *
	 * @param \App\Request $request
	 */
	public function step2(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getInteger('record');
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($record);
		$selectedModule = \App\Module::getModuleName($recordModel->get('tabid'));
		$moduleModel = Vtiger_Module_Model::getInstance($selectedModule);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('ADVANCE_CRITERIA', Vtiger_AdvancedFilter_Helper::transformToAdvancedFilterCondition($recordModel->get('conditions')));
		$viewer->assign('SOURCE_MODULE', $selectedModule);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', 'Settings:Workflows');
		$viewer->view('EditViewS2.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = [
			'modules.Vtiger.resources.AdvanceFilterEx',
			'modules.Settings.AdvancedPermission.resources.Edit',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

		return array_merge($headerScriptInstances, $jsScriptInstances);
	}
}
