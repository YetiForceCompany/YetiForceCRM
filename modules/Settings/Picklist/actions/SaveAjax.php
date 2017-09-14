<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */

class Settings_Picklist_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->exposeMethod('add');
		$this->exposeMethod('rename');
		$this->exposeMethod('remove');
		$this->exposeMethod('assignValueToRole');
		$this->exposeMethod('saveOrder');
		$this->exposeMethod('enableOrDisable');
	}

	/**
	 * Process request
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		$this->invokeExposedMethod($mode, $request);
	}
	/*
	 * @function updates user tables with new picklist value for default event and status fields
	 */

	public function updateDefaultPicklistValues($pickListFieldName, $oldValue, $newValue)
	{
		$db = PearDatabase::getInstance();
		if ($pickListFieldName == 'activitytype')
			$defaultFieldName = 'defaultactivitytype';
		else
			$defaultFieldName = 'defaulteventstatus';
		$queryToGetId = sprintf('SELECT id FROM vtiger_users WHERE %s IN (', $defaultFieldName);
		if (is_array($oldValue)) {
			$countOldValue = count($oldValue);
			for ($i = 0; $i < $countOldValue; $i++) {
				$queryToGetId .= '"' . $oldValue[$i] . '"';
				if ($i < (count($oldValue) - 1)) {
					$queryToGetId .= ',';
				}
			}
			$queryToGetId .= ')';
		} else {
			$queryToGetId .= '"' . $oldValue . '")';
		}
		$result = $db->pquery($queryToGetId, []);
		$rowCount = $db->numRows($result);
		for ($i = 0; $i < $rowCount; $i++) {
			$recordId = $db->queryResultRowData($result, $i);
			$recordId = $recordId['id'];
			$record = Vtiger_Record_Model::getInstanceById($recordId, 'Users');
			$record->set($defaultFieldName, $newValue);
			$record->save();
		}
	}

	public function add(\App\Request $request)
	{
		$newValue = $request->get('newValue');
		$moduleName = $request->getByType('source_module', 1);
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($request->getForSql('picklistName'), $moduleModel);
		$rolesSelected = [];
		if ($fieldModel->isRoleBased()) {
			$userSelectedRoles = $request->getArray('rolesSelected');
			//selected all roles option
			if (in_array('all', $userSelectedRoles)) {
				$roleRecordList = Settings_Roles_Record_Model::getAll();
				foreach ($roleRecordList as $roleRecord) {
					$rolesSelected[] = $roleRecord->getId();
				}
			} else {
				$rolesSelected = $userSelectedRoles;
			}
		}
		$response = new Vtiger_Response();
		try {
			$id = $moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
			$response->setResult(array('id' => $id['id']));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Rename picklist value
	 * @param \App\Request $request
	 */
	public function rename(\App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 1);
		$newValue = $request->get('newValue');
		$pickListFieldName = $request->getForSql('picklistName');
		$oldValue = $request->get('oldValue');
		$id = $request->get('id');
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $moduleModel);
		$response = new Vtiger_Response();
		if ($fieldModel->isEditable()) {
			try {
				if ($moduleName === 'Events' && ($pickListFieldName === 'activitytype' || $pickListFieldName === 'activitystatus')) {
					$this->updateDefaultPicklistValues($pickListFieldName, $oldValue, $newValue);
				}
				$status = $moduleModel->renamePickListValues($fieldModel, $oldValue, $newValue, $id);
				$response->setResult(['success', $status]);
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		}
		$response->emit();
	}

	public function remove(\App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 1);
		$valueToDelete = $request->getArray('delete_value');
		$replaceValue = $request->get('replace_value');
		$pickListFieldName = $request->getForSql('picklistName');
		if ($moduleName === 'Events' && ($pickListFieldName === 'activitytype' || $pickListFieldName === 'activitystatus')) {
			$this->updateDefaultPicklistValues($pickListFieldName, $valueToDelete, $replaceValue);
		}
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$response = new Vtiger_Response();
		try {
			$status = $moduleModel->remove($pickListFieldName, $valueToDelete, $replaceValue, $moduleName);
			$response->setResult(array('success', $status));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Function which will assign existing values to the roles
	 * @param \App\Request $request
	 */
	public function assignValueToRole(\App\Request $request)
	{
		$userSelectedRoles = $request->getArray('rolesSelected');
		$roleIdList = [];
		//selected all roles option
		if (in_array('all', $userSelectedRoles)) {
			$roleRecordList = Settings_Roles_Record_Model::getAll();
			foreach ($roleRecordList as $roleRecord) {
				$roleIdList[] = $roleRecord->getId();
			}
		} else {
			$roleIdList = $userSelectedRoles;
		}

		$moduleModel = new Settings_Picklist_Module_Model();

		$response = new Vtiger_Response();
		try {
			$moduleModel->enableOrDisableValuesForRole($request->getForSql('picklistName'), $request->getArray('assign_values'), [], $roleIdList);
			$response->setResult(array('success', true));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function saveOrder(\App\Request $request)
	{
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->updateSequence($request->getForSql('picklistName'), $request->getArray('picklistValues'));
			$response->setResult(array('success', true));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function enableOrDisable(\App\Request $request)
	{
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->enableOrDisableValuesForRole($request->getForSql('picklistName'), $request->getArray('enabled_values', []), $request->getArray('disabled_values', []), $request->getArray('rolesSelected'));
			$response->setResult(array('success', true));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
