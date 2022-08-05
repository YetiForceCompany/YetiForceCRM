<?php

/**
 * Condition builder view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Condition builder view class.
 */
class Vtiger_ConditionBuilder_View extends \App\Controller\View\Page
{
	use App\Controller\ClearProcess;
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getByType('sourceModuleName', 2))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('row');
		$this->exposeMethod('builder');
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->row($request);
		}
	}

	/**
	 * Display one condition for a field.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function row(App\Request $request): void
	{
		$relatedModuleSkip = $request->getBoolean('relatedModuleSkip', false);
		$sourceModuleName = $request->getByType('sourceModuleName', \App\Purifier::ALNUM);
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleName);
		$recordStructureModulesField = [];
		if (!$relatedModuleSkip) {
			foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
				foreach ($referenceField->getReferenceList() as $relatedModuleName) {
					$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
				}
			}
		}
		$fieldInfo = false;
		if ($request->isEmpty('fieldname')) {
			$fieldModel = current($sourceModuleModel->getFields());
		} else {
			$fieldInfo = $request->getForSql('fieldname', false);
			[$fieldName, $fieldModuleName, $sourceFieldName] = array_pad(explode(':', $fieldInfo), 3, false);
			if (!empty($sourceFieldName)) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($fieldModuleName));
			} else {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $sourceModuleModel);
			}
		}
		$operators = $request->isEmpty('parent', 1) ? $fieldModel->getQueryOperators() : $fieldModel->getRecordOperators();
		if ($request->isEmpty('operator', true)) {
			$selectedOperator = key($operators);
		} else {
			$selectedOperator = $request->getByType('operator', \App\Purifier::ALNUM);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('OPERATORS', $operators);
		$viewer->assign('SELECTED_OPERATOR', $selectedOperator);
		$viewer->assign('SELECTED_FIELD_MODEL', $fieldModel);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
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
		$viewer = $this->getViewer($request);
		$advanceCriteria = $request->getArray('advanceCriteria', \App\Purifier::TEXT);
		$relatedModuleSkip = $request->getBoolean('relatedModuleSkip', false);
		$sourceModuleName = $request->getByType('sourceModuleName', \App\Purifier::ALNUM);
		$sourceModuleModel = \Vtiger_Module_Model::getInstance($sourceModuleName);
		$recordStructureModulesField = [];
		if (!$relatedModuleSkip) {
			foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
				foreach ($referenceField->getReferenceList() as $relatedModuleName) {
					$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
				}
			}
		}

		$viewer->assign('ADVANCE_CRITERIA', $advanceCriteria ? \App\Condition::getConditionsFromRequest($advanceCriteria) : []);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->view('ConditionBuilder.tpl', $sourceModuleName);
	}
}
