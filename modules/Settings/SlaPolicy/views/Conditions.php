<?php
/**
 * Settings SlaPolicy Conditions View class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SlaPolicy_Conditions_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getByType('record', 'Alnum');
		$recordModel = Settings_SlaPolicy_Record_Model::getCleanInstance();
		if (!empty($record)) {
			$recordModel = Settings_SlaPolicy_Record_Model::getInstanceById($record);
		}
		$tabId = $recordModel->get('tabid');
		$sourceModuleName = $tabId ? \App\Module::getModuleName($tabId) : 'HelpDesk';
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleName);
		$recordStructureModulesField = [];
		foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
			}
		}
		$viewer->assign('ADVANCE_CRITERIA', \App\Json::decode($recordModel->get('conditions')));
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('ConditionBuilder.tpl', $qualifiedModuleName);
	}

}
