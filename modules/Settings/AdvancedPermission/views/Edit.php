<?php

/**
 * Advanced permission edit view class
 * @package YetiForce.Settings.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Edit_View extends Settings_Vtiger_Index_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		} else {
			$this->step1($request);
		}
	}

	/**
	 * Edit view first step
	 * @param Vtiger_Request $request
	 */
	public function step1(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');

		if (!empty($record)) {
			$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($record);
		} else {
			$recordModel = new Settings_AdvancedPermission_Record_Model();
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('EditViewS1.tpl', $qualifiedModuleName);
	}

	/**
	 * Edit view second step
	 * @param Vtiger_Request $request
	 */
	public function step2(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');
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

	public function getFooterScripts(Vtiger_Request $request)
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
