<?php
/*
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
 */

Class DataAccess_validate_mandatory
{

	public $config = false;

	public function process($moduleName, $ID, $record_form, $config)
	{
		$save_record = true;
		$view = isset($record_form['view']) ? $record_form['view'] : false;
		if ($view == 'quick_edit' && $moduleName != 'Calendar' && $moduleName != 'Events') {
			$records = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
			$recordModel = Users_Record_Model::getCleanInstance($moduleName);
			$fieldList = $recordModel->getModule()->getFields();
			foreach ($fieldList as $fieldName => $field) {
				if ($field->isMandatory() && !$records->get($fieldName) && !$record_form[$fieldName]) {
					$invalidField = $field->get('label');
					$fieldName2 = $fieldName;
					$save_record = false;
					break;
				}
			}
		}
		if (!$save_record)
			return Array(
				'save_record' => $save_record,
				's' => $moduleName,
				'fne' => $fieldName2,
				'type' => 0,
				'info' => Array(
					'title' => vtranslate('LBL_FAILED_TO_APPROVE_CHANGES', 'Settings:DataAccess'),
					'text' => vtranslate('LBL_MANDATORY_FIELD', 'Settings:DataAccess') . ': ' . vtranslate($invalidField, $moduleName),
					'type' => 'info'
				)
			);
		else
			return Array('save_record' => $save_record);
	}

	public function getConfig($id, $module, $baseModule)
	{
		return false;
	}
}
