<?php

/**
 * Sorting View Class for CustomView
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_Sorting_View extends Settings_Vtiger_BasicModal_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$moduleModel = Settings_LangManagement_Module_Model::getInstance($moduleName);
		$sourceModuleId = $request->get('sourceModule');
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleId);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel);
		$recordStructure = $recordStructureInstance->getStructure();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);

		// Added to show event module custom fields
		if ($sourceModuleModel->getName() == 'Calendar') {
			$relatedModuleName = 'Events';
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$relatedRecordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($relatedModuleModel);
			$eventBlocksFields = $relatedRecordStructureInstance->getStructure();
			$viewer->assign('EVENT_RECORD_STRUCTURE_MODEL', $relatedRecordStructureInstance);
			$viewer->assign('EVENT_RECORD_STRUCTURE', $eventBlocksFields);
		}
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('SOURCE_MODULE_MODEL', $sourceModuleModel);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CVID', $request->get('cvid'));
		$this->preProcess($request);
		$viewer->view('Sorting.tpl', $moduleName);
		$this->postProcess($request);
	}
}
