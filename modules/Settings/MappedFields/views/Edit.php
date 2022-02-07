<?php

/**
 * Edit View Class for MappedFields Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_Edit_View extends Settings_Vtiger_Index_View
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
		$recordId = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($recordId) {
			$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
			$viewer->assign('MAPPEDFIELDS_MODULE_MODEL', $moduleInstance);
		}
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule(false));
	}

	public function step($step, App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record')) {
			$recordId = $request->getInteger('record');
			$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODE', 'edit');
		} else {
			$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		}
		$viewer->assign('MAPPEDFIELDS_MODULE_MODEL', $moduleInstance);
		$allModules = Settings_MappedFields_Module_Model::getSupportedModules();
		$viewer->assign('ALL_MODULES', $allModules);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		switch ($step) {
			case 'step4':
				$viewer->view('Step4.tpl', $qualifiedModuleName);
				break;
			case 'step3':
				$moduleSourceName = \App\Module::getModuleName($moduleInstance->get('tabid'));
				$moduleModel = Vtiger_Module_Model::getInstance($moduleSourceName);
				$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
				$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
				$viewer->assign('SOURCE_MODULE', $moduleSourceName);
				$viewer->assign('ADVANCE_CRITERIA', Vtiger_AdvancedFilter_Helper::transformToAdvancedFilterCondition($moduleInstance->get('conditions')));
				$viewer->view('Step3.tpl', $qualifiedModuleName);
				break;
			case 'step2':
				$assignedToValues = [];
				$assignedToValues['LBL_USERS'] = \App\Fields\Owner::getInstance()->getAccessibleUsers();
				$assignedToValues['LBL_GROUPS'] = \App\Fields\Owner::getInstance()->getAccessibleGroups();
				$viewer->assign('SEL_MODULE_MODEL', Settings_MappedFields_Module_Model::getInstance($moduleInstance->get('tabid')));
				$viewer->assign('REL_MODULE_MODEL', Settings_MappedFields_Module_Model::getInstance($moduleInstance->get('reltabid')));
				$viewer->assign('USERS_LIST', $assignedToValues);
				$viewer->view('Step2.tpl', $qualifiedModuleName);
				break;
			case 'step1':
			default:
				$viewer->view('Step1.tpl', $qualifiedModuleName);
				break;
		}
	}

	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit",
			"modules.Settings.$moduleName.resources.Edit1",
			"modules.Settings.$moduleName.resources.Edit2",
			"modules.Settings.$moduleName.resources.Edit3",
			"modules.Settings.$moduleName.resources.Edit4",
			'modules.Vtiger.resources.AdvanceFilter',
			'modules.Vtiger.resources.AdvanceFilterEx',
		]));
	}
}
