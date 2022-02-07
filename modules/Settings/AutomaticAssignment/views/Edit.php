<?php

/**
 * Automatic assignment edit view.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$selectedModuleName = '';
		if ($request->isEmpty('record')) {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($request->getInteger('record'));
			$selectedModuleName = \App\Module::getModuleName($recordModel->get('tabid'));
		}

		$viewer = $this->getViewer($request);
		if ($selectedModuleName) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
			$recordStructureModulesField = [];
			foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
				foreach ($referenceField->getReferenceList() as $relatedModuleName) {
					$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
				}
			}
			$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
			$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		}
		\App\Config::set('performance', 'picklistLimit', 9999);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('STRUCTURE', $recordModel->getModule()->getEditViewStructure($recordModel));
		$viewer->assign('ADVANCE_CRITERIA', \App\Json::decode($recordModel->get('conditions')));
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		$viewer->assign('RECORD_ID', $recordModel->getId());

		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}
}
