<?php

/**
 * View to display row with fields, operators and value.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_ConditionBuilder_View extends Vtiger_IndexAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModuleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureModulesField = [];
		foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
			}
		}
		if ($request->isEmpty('fieldname')) {
			$fieldModel = reset($sourceModuleModel->getFields());
		} else {
			$fieldInfo = $request->getForSql('fieldname', false);
			[$fieldModuleName, $fieldName, $sourceFieldName] = array_pad(explode(':', $fieldInfo), 3, false);
			if (!empty($sourceFieldName)) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($fieldModuleName));
			} else {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $sourceModuleModel);
			}
		}
		$operators = $fieldModel->getOperators();
		if ($request->isEmpty('operator', true)) {
			$selectedOperator = key($operators);
		} else {
			$selectedOperator = $request->getByType('operator', 'Standard');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('OPERATORS', $operators);
		$viewer->assign('SELECTED_OPERATOR', $selectedOperator);
		$viewer->assign('SELECTED_FIELD_MODEL', $fieldModel);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		$viewer->assign('FIELD_INFO', $fieldInfo);
		$viewer->view('ConditionBuilderRow.tpl', $moduleName);
	}
}
