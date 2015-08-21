<?php
/*
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
 */

Class DataAccess_show_quick_create
{

	var $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		$db = PearDatabase::getInstance();
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!isset($ID) || $ID == 0 || $ID == '' || !$userPrivModel->hasModuleActionPermission(getTabid($config['modules']), 'EditView')) {
			return Array('save_record' => true);
		}
		return Array(
			'save_record' => false,
			'type' => 1,
			'module' => $config['modules']
		);
	}

	public function getConfig($id, $module, $baseModule)
	{
		$db = PearDatabase::getInstance();
		$modulesQuickCreate = Vtiger_Module_Model::getQuickCreateModules(true);
		$modules = [];
		foreach ($modulesQuickCreate as $moduleName => $moduleModel) {
			$quickCreateModule = $moduleModel->isQuickCreateSupported();
			$singularLabel = $moduleModel->getSingularLabelKey();
			if ($singularLabel == 'SINGLE_Calendar') {
				$singularLabel = 'LBL_EVENT_OR_TASK';
			}
			if ($quickCreateModule == 1) {
				$modules[$moduleName] = $singularLabel;
			}
		}
		return Array('modules' => $modules);
	}
}
