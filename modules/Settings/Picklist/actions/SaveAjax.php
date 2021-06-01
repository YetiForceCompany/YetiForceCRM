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
		parent::__construct();
		$this->exposeMethod('add');
		$this->exposeMethod('import');
		$this->exposeMethod('rename');
		$this->exposeMethod('processStatus');
		$this->exposeMethod('remove');
		$this->exposeMethod('assignValueToRole');
		$this->exposeMethod('saveOrder');
		$this->exposeMethod('enableOrDisable');
	}

	/** @function updates user tables with new picklist value for default event and status fields */
	public function updateDefaultPicklistValues($pickListFieldName, $oldValue, $newValue)
	{
		if ('activitytype' === $pickListFieldName) {
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

	public function add(App\Request $request)
	{
		$newValue = $request->getByType('newValue', 'Text');
		$moduleModel = Settings_Picklist_Module_Model::getInstance($request->getByType('source_module', 'Alnum'));
		$fieldModel = Settings_Picklist_Field_Model::getInstance($request->getForSql('picklistName'), $moduleModel);
		$rolesSelected = [];
		if ($fieldModel->isRoleBased()) {
			$userSelectedRoles = $request->getArray('rolesSelected', 'Alnum');
			//selected all roles option
			if (\in_array('all', $userSelectedRoles)) {
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
			$id = $moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected, $request->getForHtml('description'), $request->getByType('prefix', 'Text'), $request->getByType('record_state', 'Integer'));
			$response->setResult(['id' => $id['id']]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Import Picklist.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function import(App\Request $request): void
	{
		if (empty($_FILES['file']['name'])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$fileInstance = \App\Fields\File::loadFromRequest($_FILES['file']);
		if (!$fileInstance->validate() || 'csv' !== $fileInstance->getExtension()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$moduleModel = Settings_Picklist_Module_Model::getInstance($request->getByType('source_module', 'Alnum'));
		$fieldModel = Settings_Picklist_Field_Model::getInstance($request->getForSql('picklistName'), $moduleModel);
		$csv = new \ParseCsv\Csv();
		$csv->heading = false;
		$csv->use_mb_convert_encoding = true;
		if ($fileInstance->getEncoding(['UTF-8', 'ISO-8859-1']) !== \App\Config::main('default_charset', 'UTF-8')) {
			$csv->encoding($fileInstance->getEncoding(), \App\Config::main('default_charset', 'UTF-8'));
		}
		$csv->auto($fileInstance->getPath());
		$error = '';
		$allCounter = $successCounter = $errorsCounter = 0;
		$rolesSelected = [];
		if ($fieldModel->isRoleBased()) {
			$rolesSelected = (new \App\Db\Query())
				->select(['vtiger_role.roleid'])
				->from('vtiger_user2role')
				->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
				->column();
		}
		foreach ($csv->data as $lineNo => $row) {
			if ('' === $row[0]) {
				continue;
			}
			++$allCounter;
			try {
				$fieldModel->validate($row[0]);
				$moduleModel->addPickListValues($fieldModel, $row[0], $rolesSelected, $row[1] ?? '', $row[2] ?? '');
				++$successCounter;
			} catch (\Throwable $th) {
				++$errorsCounter;
				$error .= "[$lineNo] '{$row[0]}': {$th->getMessage()}\n";
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'all' => $allCounter,
			'success' => $successCounter,
			'errors' => $errorsCounter,
			'errorMessage' => $error,
		]);
		$response->emit();
	}

	/**
	 * Rename picklist value.
	 *
	 * @param \App\Request $request
	 */
	public function rename(App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 'Alnum');
		$newValue = $request->getByType('newValue', 'Text');
		$pickListFieldName = $request->getForSql('picklistName');
		$oldValue = $request->getByType('oldValue', 'Text');
		$id = $request->getInteger('primaryKeyId');
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $moduleModel);
		$selectedFieldNonEditablePickListValues = App\Fields\Picklist::getNonEditableValues($fieldModel->getName());
		if (!\in_array($oldValue, \App\Fields\Picklist::getValuesName($pickListFieldName)) || (isset($selectedFieldNonEditablePickListValues[$id]) && !empty($newValue))) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
		}
		$newValue = $newValue ?: $oldValue;
		$response = new Vtiger_Response();
		if ($fieldModel->isEditable()) {
			try {
				$fieldModel->validate($newValue, $id);
				if ('Calendar' === $moduleName && ('activitytype' === $pickListFieldName || 'activitystatus' === $pickListFieldName)) {
					$this->updateDefaultPicklistValues($pickListFieldName, $oldValue, $newValue);
				}
				$status = $moduleModel->renamePickListValues($fieldModel, $oldValue, $newValue, $id, $request->getForHtml('description'), $request->getByType('prefix', 'Text'));
				if ($fieldModel->isProcessStatusField() || !empty(\App\RecordStatus::getLockStatus($moduleName, false)[$request->getInteger('picklist_valueid')])) {
					$fieldModel->updateCloseState($request->getInteger('picklist_valueid'), $newValue);
				}
				$response->setResult([
					'success',
					$status,
					'newValue' => \App\Language::translate($newValue, $moduleName)
				]);
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		}
		$response->emit();
	}

	/**
	 * Update process status.
	 *
	 * @param \App\Request $request
	 */
	public function processStatus(App\Request $request)
	{
		$id = $request->getInteger('primaryKeyId');
		$moduleModel = Settings_Picklist_Module_Model::getInstance($request->getByType('source_module', 'Alnum'));
		$fieldModel = Settings_Picklist_Field_Model::getInstance($request->getForSql('picklistName'), $moduleModel);
		$response = new Vtiger_Response();
		$valueId = $request->getInteger('picklist_valueid');
		$fieldName = $fieldModel->getName();
		$value = App\Fields\Picklist::getValues($fieldName)[$id][$fieldName] ?? null;
		$result = true;
		try {
			if (null === $value) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
			if ($fieldModel->isProcessStatusField()) {
				$timeCounting = $request->has('time_counting') ? $request->getInteger('time_counting') : null;
				$result = $result && $fieldModel->updateRecordStatus($id, $request->getInteger('record_state'), $timeCounting);
			}
			if (15 === $fieldModel->getUIType()) {
				$result = $result && $fieldModel->updateCloseState($valueId, $value, $request->getBoolean('close_state'));
			}
			$response->setResult($result);
		} catch (\Throwable $e) {
			$response->setException($e);
		}
		$response->emit();
	}

	/**
	 * Action to remove element.
	 *
	 * @param \App\Request $request
	 */
	public function remove(App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 'Alnum');
		$valueToDelete = $request->getArray('delete_value', 'Integer');
		$replaceValue = $request->getInteger('replace_value');
		$pickListFieldName = $request->getForSql('picklistName');
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, Vtiger_Module_Model::getInstance($moduleName));
		if (!$fieldModel || \count($fieldModel->getPicklistValues(true)) <= 1) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		if ('Calendar' === $moduleName && ('activitytype' === $pickListFieldName || 'activitystatus' === $pickListFieldName)) {
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
			$response->setException($e);
		}
		$response->emit();
	}

	/**
	 * Function which will assign existing values to the roles.
	 *
	 * @param \App\Request $request
	 */
	public function assignValueToRole(App\Request $request)
	{
		$userSelectedRoles = $request->getArray('rolesSelected', 'Alnum');
		$roleIdList = [];
		//selected all roles option
		if (\in_array('all', $userSelectedRoles)) {
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
			$moduleModel->enableOrDisableValuesForRole($request->getForSql('picklistName'), $request->getArray('assign_values', 'Integer'), [], $roleIdList);
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setException($e);
		}
		$response->emit();
	}

	public function saveOrder(App\Request $request)
	{
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->updateSequence($request->getForSql('picklistName'), $request->getArray('picklistValues', 'Integer'));
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setException($e);
		}
		$response->emit();
	}

	public function enableOrDisable(App\Request $request)
	{
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->enableOrDisableValuesForRole($request->getForSql('picklistName'), $request->getArray('enabled_values', 'Integer'), $request->getArray('disabled_values', 'Integer'), $request->getArray('rolesSelected', 'Alnum'));
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setException($e);
		}
		$response->emit();
	}
}
