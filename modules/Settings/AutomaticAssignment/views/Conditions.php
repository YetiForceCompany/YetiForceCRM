<?php
/**
 * Settings Condition Builder View file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Condition Builder View class.
 */
class Settings_AutomaticAssignment_Conditions_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$tabId = $request->getInteger('sourceTabId');
		$sourceModuleName = \App\Module::getModuleName($tabId);
		$sourceModuleModel = \Vtiger_Module_Model::getInstance($sourceModuleName);
		$recordStructureModulesField = [];
		foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
			}
		}
		$viewer->assign('ADVANCE_CRITERIA', []);
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		$viewer->view('ConditionBuilder.tpl', $qualifiedModuleName);
	}
}
