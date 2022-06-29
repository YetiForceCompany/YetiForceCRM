<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Picklist_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$recordId = 0;
		if (\App\Request::_has('picklistName')) {
			$request = \App\Request::init();
			$pickListFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
			$moduleName = $request->getByType('source_module', \App\Purifier::ALNUM);
			$recordId = Vtiger_Module_Model::getInstance($moduleName)->getFieldByName($pickListFieldName)->getId();
		}
		Settings_Vtiger_Tracker_Model::setRecordId($recordId);
		Settings_Vtiger_Tracker_Model::addBasic('save');
		parent::__construct();
		$this->exposeMethod('import');
		$this->exposeMethod('edit');
		$this->exposeMethod('remove');
		$this->exposeMethod('assignValueToRole');
		$this->exposeMethod('saveOrder');
		$this->exposeMethod('enableOrDisable');
		$this->exposeMethod('preSaveValidation');
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

		$moduleModel = Vtiger_Module_Model::getInstance($request->getByType('source_module', \App\Purifier::ALNUM));
		$fieldModel = Settings_Picklist_Field_Model::getInstance($request->getForSql('picklistName'), $moduleModel);
		if (!$fieldModel->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}

		$fileInstance = \App\Fields\File::loadFromRequest($_FILES['file']);
		if (!$fileInstance->validate() || 'csv' !== $fileInstance->getExtension() || $fileInstance->getSize() > \App\Config::getMaxUploadSize()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$csv = new \ParseCsv\Csv();
		$csv->heading = false;
		$csv->use_mb_convert_encoding = true;
		if ($fileInstance->getEncoding(['UTF-8', 'ISO-8859-1']) !== \App\Config::main('default_charset', 'UTF-8')) {
			$csv->encoding($fileInstance->getEncoding(), \App\Config::main('default_charset', 'UTF-8'));
		}
		$csv->auto($fileInstance->getPath());
		$error = '';
		$allCounter = $successCounter = $errorsCounter = 0;
		$rolesSelected = $fieldModel->isRoleBased() ? array_keys(Settings_Roles_Record_Model::getAll()) : [];

		foreach ($csv->data as $lineNo => $row) {
			if ('' === $row[0]) {
				continue;
			}
			++$allCounter;
			try {
				$itemModel = $fieldModel->getItemModel();
				foreach (['name' => 0, 'description' => 1, 'prefix' => 2] as $property => $key) {
					if (isset($row[$key])) {
						$itemModel->validateValue($property, $row[$key]);
						$itemModel->set($property, $row[$key]);
					}
				}
				if ($rolesSelected) {
					$itemModel->set('roles', $rolesSelected);
				}
				$itemModel->save();
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
	 * PreSave validation function.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function preSaveValidation(App\Request $request)
	{
		$itemModel = $this->getItemModelFromRequest($request);
		$response = new Vtiger_Response();
		$response->setResult($itemModel->validate());
		$response->emit();
	}

	/**
	 * Function to get the picklist value model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\Fields\Picklist\Item
	 */
	protected function getItemModelFromRequest(App\Request $request): App\Fields\Picklist\Item
	{
		$moduleName = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickListFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $moduleModel);
		$id = $request->getInteger('primaryKeyId', 0);
		if (!$id && !$fieldModel->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}

		$itemModel = $fieldModel->getItemModel($id);
		foreach ($itemModel->getEditFields() as $fieldName => $fieldModel) {
			if ($request->has($fieldName) && !$fieldModel->isEditableReadOnly()) {
				if ('roles' === $fieldName) {
					$roleIdList = $request->getArray($fieldName, \App\Purifier::ALNUM);
					if (\in_array('all', $roleIdList)) {
						$roleIdList = array_keys(Settings_Roles_Record_Model::getAll());
					}
					$itemModel->set($fieldName, $roleIdList);
				} else {
					$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
					$fieldUITypeModel = $fieldModel->getUITypeModel();
					$fieldUITypeModel->validate($value, true);
					$value = $fieldModel->getDBValue($value);
					$itemModel->set($fieldName, $value);
				}
			}
		}

		return $itemModel;
	}

	/**
	 * Edit picklist value data.
	 *
	 * @param \App\Request $request
	 */
	public function edit(App\Request $request)
	{
		$itemModel = $this->getItemModelFromRequest($request);
		$valueId = $itemModel->getId();
		$result = $itemModel->save();
		Settings_Vtiger_Tracker_Model::addDetail($itemModel->getPreviousValue(), $valueId ? array_intersect_key($itemModel->getData(), $itemModel->getPreviousValue()) : $itemModel->getData());
		\App\Colors::generate('picklist');

		$response = new Vtiger_Response();
		$response->setResult(['success' => $result]);
		$response->emit();
	}

	/**
	 * Action to remove element.
	 *
	 * @param \App\Request $request
	 */
	public function remove(App\Request $request)
	{
		$itemModel = $this->getItemModelFromRequest($request);
		if (!$itemModel->isDeletable() || !$request->getInteger('replace_value', 0)) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		$replaceId = $request->getInteger('replace_value');
		$itemModel->delete($replaceId);
		$picklisValue = \App\Fields\Picklist::getValues($itemModel->getFieldModel()->getName())[$replaceId]['picklistValue'] ?? '';
		Settings_Vtiger_Tracker_Model::addDetail(['name' => $itemModel->get('name')], ['name' => $picklisValue]);
		\App\Colors::generate('picklist');

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	/**
	 * Function which will assign existing values to the roles.
	 *
	 * @param \App\Request $request
	 */
	public function assignValueToRole(App\Request $request)
	{
		$moduleName = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickListFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, Vtiger_Module_Model::getInstance($moduleName));
		if (!$fieldModel->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		$roleIdList = $request->getArray('rolesSelected', \App\Purifier::ALNUM);
		if (\in_array('all', $roleIdList)) {
			$roleIdList = array_keys(Settings_Roles_Record_Model::getAll());
		}
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->enableOrDisableValuesForRole(
				$pickListFieldName,
				$request->getArray('assign_values', \App\Purifier::INTEGER),
				[],
				$roleIdList);
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setException($e);
		}
		$response->emit();
	}

	/**
	 * Save picklist values order.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function saveOrder(App\Request $request)
	{
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->updateSequence($request->getForSql('picklistName'), $request->getArray('seq', \App\Purifier::INTEGER, [], \App\Purifier::INTEGER));
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setException($e);
		}
		$response->emit();
	}

	/**
	 * Change state of picklist values permissions.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function enableOrDisable(App\Request $request)
	{
		$moduleName = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickListFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, Vtiger_Module_Model::getInstance($moduleName));
		if (!$fieldModel->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		$moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
		try {
			$moduleModel->enableOrDisableValuesForRole(
				$request->getByType('picklistName', \App\Purifier::ALNUM),
				$request->getArray('enabled_values', \App\Purifier::INTEGER),
				$request->getArray('disabled_values', \App\Purifier::INTEGER),
				$request->getArray('rolesSelected', \App\Purifier::ALNUM));
			$response->setResult(['success', true]);
		} catch (Exception $e) {
			$response->setException($e);
		}
		$response->emit();
	}
}
