<?php

/**
 * Settings pickList dependency edit view file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings pickList dependency edit view class.
 */
class Settings_PickListDependency_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->isEmpty('record', true) ? 0 : $request->getInteger('record');
		$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();

		if ($recordId) {
			$recordModel = Settings_PickListDependency_Record_Model::getInstanceById($recordId);
			$mappedValues = $recordModel->getPickListDependency();
		} else {
			$selectedModule = $request->isEmpty('tabid', true) ? current($moduleModelList)['name'] : $request->getByType('tabid', \App\Purifier::ALNUM);
			$recordModel = Settings_PickListDependency_Record_Model::getCleanInstance();
			$recordModel->set('tabid', $selectedModule);
			$mappedValues = [];
		}
		$picklistValues = [];
		$selectedFieldName = '';
		$sourceModuleModel = $recordModel->getSourceModule();
		$sourceField = $recordModel->getFieldInstanceByName('source_field');
		$picklistValues = $recordModel->getPickListValuesByField($sourceField->getName());
		if ($sourceValue = $recordModel->get($sourceField->getName())) {
			$selectedFieldName = $sourceValue ? $sourceModuleModel->getFieldByName($sourceValue)->getName() : '';
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('STRUCTURE', $recordModel->getModule()->getEditViewStructure($recordModel));
		$viewer->assign('MODULE_FIELD_MODEL', $recordModel->getFieldInstanceByName('tabid'));
		$viewer->assign('MAPPED_VALUES', $mappedValues);

		$viewer->assign('SOURCE_PICKLIST_VALUES', $picklistValues);
		$viewer->assign('SOURCE_MODULE', $sourceModuleModel->getName());
		$viewer->assign('OPERATORS', ['e']);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', []);
		$viewer->assign('RECORD_STRUCTURE', Settings_PickListDependency_Module_Model::getConditionBuilderStructure($sourceModuleModel, $selectedFieldName));

		$viewer->view('Edit.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(
			parent::getFooterScripts($request),
			$this->checkAndConvertJsScripts([
				"modules.Settings.$moduleName.resources.ConditionBuilder"
			])
		);
	}
}
