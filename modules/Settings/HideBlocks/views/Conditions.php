<?php

/**
 * Settings HideBlocks conditions view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_Conditions_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$blockId = $request->getInteger('blockid');
		$views = $request->getArray('views', 'Standard');
		$qualifiedModuleName = $request->getModule(false);
		$mode = '';
		$viewer = $this->getViewer($request);

		if ($views != '') {
			$views = implode($views, ',');
		}
		if ($recordId) {
			$mode = 'edit';
		} else {
		}
		$moduleModel = Settings_HideBlocks_Record_Model::getModuleInstanceByBlockId($blockId);
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
		$structuredValues = $recordStrucure->getStructure();
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$blockInstance = vtlib\Block::getInstance($blockId, $moduleModel->id);
		$blockLabel = $blockInstance->label;
		$blockModelList = $moduleModel->getBlocks();
		$blockModel = $blockModelList[$blockLabel];
		$fieldModelList = $blockModel->getFields();
		$mandatoryFields = [];
		if (!empty($fieldModelList)) {
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if ($fieldModel->isMandatory()) {
					$mandatoryFields[$fieldName] = $fieldModel;
				}
			}
		}
		$viewer->assign('MANDATORY_FIELDS', $mandatoryFields);
		$viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($qualifiedModuleName));
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Settings_Workflows_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Settings_Workflows_Field_Model::getAdvancedFilterOpsByFieldType());
		$viewer->assign('COLUMNNAME_API', 'getName');
		$recordModel = Settings_HideBlocks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		if ($recordModel) {
			$viewer->assign('ADVANCE_CRITERIA', $this->transformToAdvancedFilterCondition($recordModel->get('conditions')));
		}
		$viewer->assign('MODE', $mode);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('MODULE', 'HideBlocks');
		$viewer->assign('SOURCE_MODULE', $moduleModel->get('name'));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('BLOCKID', $blockId);
		$viewer->assign('ENABLED', $request->getBoolean('enabled'));
		$viewer->assign('VIEWS', $views);
		$viewer->view('Conditions.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.$moduleName.resources.Conditions",
			"modules.Settings.$moduleName.resources.AdvanceFilter",
		]));
	}

	public function transformToAdvancedFilterCondition($conditions)
	{
		$conditions = \App\Json::decode($conditions);
		$firstGroup = $secondGroup = [];
		$transformedConditions = [];
		if (!empty($conditions)) {
			foreach ($conditions as $info) {
				if (!($info['groupid'])) {
					$firstGroup[] = ['columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid'], ];
				} else {
					$secondGroup[] = ['columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid'], ];
				}
			}
		}
		$transformedConditions[1] = ['columns' => $firstGroup];
		$transformedConditions[2] = ['columns' => $secondGroup];

		return $transformedConditions;
	}
}
