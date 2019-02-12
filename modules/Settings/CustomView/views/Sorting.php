<?php

/**
 * Sorting View Class for CustomView.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_Sorting_View extends Settings_Vtiger_BasicModal_View
{
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($moduleName);
		$sourceModuleId = $request->getInteger('sourceModule');
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleId);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel);
		$recordStructure = $recordStructureInstance->getStructure();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('SOURCE_MODULE_MODEL', $sourceModuleModel);
		$viewer->assign('SOURCE_MODULE', $sourceModuleModel->getName());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CVID', $request->getInteger('cvid'));
		$this->preProcess($request);
		$viewer->view('Sorting.tpl', $moduleName);
		$this->postProcess($request);
	}
}
