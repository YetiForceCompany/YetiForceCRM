<?php

/**
 * Condition builder view file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Condition builder view class.
 */
class Settings_PickListDependency_ConditionBuilder_View extends Vtiger_ConditionBuilder_View
{
	use \App\Controller\Traits\SettingsPermission;

	/**
	 * Display one condition for a field.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function row(App\Request $request): void
	{
		$sourceModuleName = $request->getByType('sourceModuleName', \App\Purifier::ALNUM);
		$sourceField = $request->getByType('sourceField', \App\Purifier::ALNUM);
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleName);

		$structure = Settings_PickListDependency_Module_Model::getConditionBuilderStructure($sourceModuleModel, $sourceField);
		$fieldInfo = false;
		if ($request->isEmpty('fieldname')) {
			$fieldModel = current(current($structure));
		} else {
			$fieldInfo = $request->getForSql('fieldname', false);
			[$fieldName, $fieldModuleName, $sourceFieldName] = array_pad(explode(':', $fieldInfo), 3, false);
			if (!empty($sourceFieldName)) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($fieldModuleName));
			} else {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $sourceModuleModel);
			}
		}

		$operators = $fieldModel->getRecordOperators();
		if ($request->isEmpty('operator', true)) {
			$selectedOperator = array_key_first($operators);
		} else {
			$selectedOperator = $request->getByType('operator', \App\Purifier::ALNUM);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('OPERATORS', $operators);
		$viewer->assign('SELECTED_OPERATOR', $selectedOperator);
		$viewer->assign('SELECTED_FIELD_MODEL', $fieldModel);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', []);
		$viewer->assign('RECORD_STRUCTURE', $structure);
		$viewer->assign('FIELD_INFO', $fieldInfo);
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->view('ConditionBuilderRow.tpl', $sourceModuleName);
	}

	/**
	 * Display the condition builder panel.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function builder(App\Request $request): void
	{
		$sourceModuleName = $request->getByType('sourceModuleName', \App\Purifier::ALNUM);
		$sourceField = $request->getByType('sourceField', \App\Purifier::ALNUM);
		$sourceModuleModel = \Vtiger_Module_Model::getInstance($sourceModuleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('ADVANCE_CRITERIA', []);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', []);
		$viewer->assign('RECORD_STRUCTURE', Settings_PickListDependency_Module_Model::getConditionBuilderStructure($sourceModuleModel, $sourceField));
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->view('ConditionBuilder.tpl', $sourceModuleName);
	}
}
