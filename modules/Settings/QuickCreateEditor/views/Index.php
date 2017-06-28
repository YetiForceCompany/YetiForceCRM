<?php

/**
 * Settings QuickCreateEditor index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_QuickCreateEditor_Index_View extends Settings_Vtiger_Index_View
{

	public function __construct()
	{
		$this->exposeMethod('showFieldLayout');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if ($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			//by default show field layout
			$this->showFieldLayout($request);
		}
	}

	public function showFieldLayout(\App\Request $request)
	{
		$sourceModule = $request->get('sourceModule');
		$menuModelsList = Vtiger_Module_Model::getQuickCreateModules();

		if (empty($sourceModule)) {
			//To get the first element
			$firstElement = reset($menuModelsList);
			$sourceModule = array($firstElement->get('name'));
		} else
			$sourceModule = array($sourceModule);

		$quickCreateContents = [];

		if (in_array('Calendar', $sourceModule))
			$sourceModule = array('Calendar', 'Events');

		foreach ($sourceModule as $module) {
			$recordModel = Vtiger_Record_Model::getCleanInstance($module);

			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
			$quickCreateContents[$module] = $recordStructureInstance->getStructure();
		}

		$qualifiedModule = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule[0]);
		$viewer->assign('SUPPORTED_MODULES', $menuModelsList);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('RECORDS_STRUCTURE', $quickCreateContents);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);

		$viewer->view('Index.tpl', $qualifiedModule);
	}
}
