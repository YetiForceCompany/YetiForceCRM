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
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
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

	// @function updates user tables with new picklist value for default event and status fields

	public function updateDefaultPicklistValues($pickListFieldName, $oldValue, $newValue)
	{
		if ($pickListFieldName === 'activitytype') {
			$defaultFieldName = 'defaultactivitytype';
		} else {
			$defaultFieldName = 'defaulteventstatus';
		}
		$dataReader = (new App\Db\Query())->select(['id'])
			->from('vtiger_users')
			->where([$defaultFieldName => $oldValue])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$record = Vtiger_Record_Model::getInstanceById($row['id'], 'Users');
			$record->set($defaultFieldName, $newValue);
			$record->save();
		}
		$dataReader->close();
	}

	public function add(\App\Request $request)
	{
		$newValue = $request->get('newValue');
		$moduleName = $request->getByType('source_module', 2);
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
			$fieldModel->validate($newValue);
			$id = $moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
			$response->setResult(['id' => $id['id']]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Rename picklist value.
	 *
	 * @param \App\Request $request
	 */
	public function rename(\App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 2);
		$newValue = $request->get('newValue');
		$pickListFieldName = $request->getForSql('picklistName');
		$oldValue = $request->get('oldValue');
		$id = $request->get('id');
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $moduleModel);
		$response = new Vtiger_Response();
		if ($fieldModel->isEditable()) {
			try {
				$fieldModel->validate($newValue, $id);
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

	/**
	 * Action to remove element.
	 *
	 * @param \App\Request $request
	 */
	public function remove(\App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 2);
		$valueToDelete = $request->getArray('delete_value');
		$replaceValue = $request->get('replace_value');
		$pickListFieldName = $request->getForSql('picklistName');
		if ($moduleName === 'Events' && ($pickListFieldName === 'activitytype' || $pickListFieldName === 'activitystatus')) {
			$picklistData = \App\Fields\Picklist::getValues($pickListFieldName);
			$valuesToDelete = [];
			foreach ($valueToDelete as $value) {
				$valuesToDelete[] = $picklistData[$value][$pickListFieldName];
			}
			$this->updateDefaultPicklistValues($pickListFieldName, $valuesToDelete, $picklistData[$replaceValue][$pickListFieldName]);
		}
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$response = new Vtiger_Response();
		try {
			$status = $moduleModel->remove($pickListFieldName, $valueToDelete, $replaceValue, $moduleName);
			$response->setResult(['success', $status]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Function which will assign existing values to the roles.
	 *
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
			$response->setResult(['success', true]);
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
			$response->setResult(['success', true]);
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
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
