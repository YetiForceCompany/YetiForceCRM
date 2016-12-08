<?php

/**
 * Automatic assignment edit view
 * @package YetiForce.Settings.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Edit_View extends Settings_Vtiger_Index_View
{

	/**
	 * Checking permission 
	 * @param Vtiger_Request $request
	 * @throws \Exception\NoPermittedForAdmin
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin() || empty($request->get('record'))) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($request->get('record'));
		$sourceModuleName = $recordModel->getSourceModuleName();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);

		if ($request->has('tab')) {
			$viewer->assign('FIELD_NAME', $request->get('tab'));
			$viewer->assign('LABEL', $recordModel->getEditFields()[$request->get('tab')]);
			$viewer->view('Tab.tpl', $qualifiedModuleName);
		} else {
			$this->getVariablesToAdvancedFilter($viewer, $recordModel);
			$viewer->view('Edit.tpl', $qualifiedModuleName);
		}
	}

	/**
	 * Function gets variables to advanced filter
	 * @param Vtiger_Viewer $viewer
	 * @param Settings_AutomaticAssignment_Record_Model $recordModel
	 */
	private function getVariablesToAdvancedFilter(Vtiger_Viewer $viewer, $recordModel)
	{
		$sourceModuleName = $recordModel->getSourceModuleName();
		$moduleModel = Vtiger_Module_Model::getInstance($recordModel->get('tabid'));
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

		$conditions = $recordModel->get('conditions');
		if ($conditions) {
			$conditions = \App\Json::decode($conditions);
		}
		$criteria = Vtiger_AdvancedFilter_Helper::transformToAdvancedFilterCondition($conditions);
		$viewer->assign('ADVANCE_CRITERIA', Vtiger_AdvancedFilter_Helper::transformToAdvancedFilterCondition($conditions));

		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($sourceModuleName));

		if ($sourceModuleName === 'Calendar') {
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else {
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', \App\CustomView::ADVANCED_FILTER_OPTIONS);
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
	}

	/**
	 * Scripts
	 * @param Vtiger_Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = [
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit",
			'modules.CustomView.resources.CustomView'
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
