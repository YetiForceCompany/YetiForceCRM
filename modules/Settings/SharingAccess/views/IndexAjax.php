<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_SharingAccess_IndexAjax_View extends Settings_Vtiger_IndexAjax_View
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRules');
		$this->exposeMethod('editRule');
	}

	/**
	 * Show rules.
	 *
	 * @param \App\Request $request
	 */
	public function showRules(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$forModule = $request->getByType('for_module', 'Alnum');

		$moduleModel = Settings_SharingAccess_Module_Model::getInstance($forModule);
		$ruleModelList = Settings_SharingAccess_Rule_Model::getAllByModule($moduleModel);

		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FOR_MODULE', $forModule);
		$viewer->assign('RULE_MODEL_LIST', $ruleModelList);
		echo $viewer->view('ListRules.tpl', $qualifiedModuleName, true);
	}

	/**
	 * Edit rule.
	 *
	 * @param \App\Request $request
	 */
	public function editRule(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$forModule = $request->getByType('for_module', 'Alnum');
		$ruleId = $request->getInteger('record');

		$moduleModel = Settings_SharingAccess_Module_Model::getInstance($forModule);
		if ($ruleId) {
			$ruleModel = Settings_SharingAccess_Rule_Model::getInstance($moduleModel, $ruleId);
		} else {
			$ruleModel = new Settings_SharingAccess_Rule_Model();
			$ruleModel->setModuleFromInstance($moduleModel);
		}

		$viewer->assign('ALL_RULE_MEMBERS', Settings_SharingAccess_RuleMember_Model::getAll());
		$viewer->assign('ALL_PERMISSIONS', Settings_SharingAccess_Rule_Model::$allPermissions);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RULE_MODEL', $ruleModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		echo $viewer->view('EditRule.tpl', $qualifiedModuleName, true);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return \Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.Index',
			"modules.Settings.{$request->getModule()}.resources.Index",
		]));
	}
}
