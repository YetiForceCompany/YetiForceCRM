<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Settings module model class for groups.
 */
class Settings_Groups_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $baseTable = 'vtiger_groups';

	/** {@inheritdoc} */
	public $baseIndex = 'groupid';

	/** {@inheritdoc} */
	public $listFields = ['groupname' => 'Name', 'description' => 'Description', 'parentid' => 'FL_PARENT', 'modules' => 'LBL_MODULES', 'members' => 'LBL_GROUP_MEMBERS'];

	/** {@inheritdoc} */
	public $name = 'Groups';

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		$fields = [];
		foreach (array_keys($this->listFields) as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName);
		}
		return $fields;
	}

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=Index';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=Edit';
	}

	/** @var string[] Fields name for edit view */
	public $editFields = [
		'groupname',
		'description',
		'parentid',
		'modules',
		'members'
	];

	/**
	 * Editable fields.
	 *
	 * @return array
	 */
	public function getEditableFields(): array
	{
		return $this->editFields;
	}

	/**
	 * Get structure fields.
	 *
	 * @param Settings_Groups_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getEditViewStructure($recordModel = null): array
	{
		$structure = [];
		foreach ($this->editFields as $fieldName) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($recordModel && $recordModel->has($fieldName)) {
				$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
			} else {
				$defaultValue = $fieldModel->get('defaultvalue');
				$fieldModel->set('fieldvalue', $defaultValue ?? '');
			}
			$structure[$fieldName] = $fieldModel;
		}

		return $structure;
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$params = [];
		switch ($name) {
			case 'groupname':
				$params = [
					'name' => $name,
					'label' => 'Name',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '100',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $this->getBaseTable()
				];
				break;
			case 'description':
				$params = [
					'name' => $name,
					'label' => 'Description',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '65535',
					'purifyType' => \App\Purifier::TEXT,
					'table' => $this->getBaseTable()
				];
				break;
			case 'parentid':
				$params = [
					'name' => $name,
					'label' => 'FL_PARENT',
					'uitype' => 53,
					'typeofdata' => 'I~O',
					'maximumlength' => '4294967295',
					'purifyType' => \App\Purifier::INTEGER,
					'table' => $this->getBaseTable(),
					'picklistValues' => []
				];
				break;
			case 'modules':
				$params = [
					'name' => $name,
					'label' => 'LBL_MODULES',
					'uitype' => 33,
					'typeofdata' => 'V~M',
					'maximumlength' => '65535',
					'purifyType' => \App\Purifier::TEXT,
					'table' => '',
					'picklistValues' => []
				];
				foreach (\vtlib\Functions::getAllModules(true, true, 0) as $module) {
					$params['picklistValues'][$module['tabid']] = \App\Language::translate($module['name'], $module['name']);
				}
				break;
			case 'members':
				$params = [
					'name' => $name,
					'label' => 'LBL_GROUP_MEMBERS',
					'uitype' => 33,
					'typeofdata' => 'V~M',
					'maximumlength' => '65535',
					'purifyType' => \App\Purifier::TEXT,
					'picklistValues' => []
				];
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($this->getName(true), $params, $name) : null;
	}

	/**
	 * Function to get the links.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getLinks(): array
	{
		return [Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => App\Language::translate('LBL_ADD_RECORD', $this->getName()),
			'linkurl' => $this->getCreateRecordUrl(),
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn-primary',
			'showLabel' => 1
		])];
	}

	/**
	 * Function to get Alphabet Search Field.
	 */
	public function getAlphabetSearchField()
	{
		return '';
	}

	/**
	 * Gets value from request.
	 *
	 * @param string      $fieldName
	 * @param App\Request $request
	 *
	 * @return mixed
	 */
	public function getValueFromRequest(string $fieldName, App\Request $request)
	{
		switch ($fieldName) {
			case 'groupname':
			case 'description':
				$value = $request->getByType($fieldName, \App\Purifier::TEXT);
				break;
			case 'parentid':
				$value = $request->getArray($fieldName, \App\Purifier::INTEGER);
				break;
			case 'members':
				$value = $request->getArray($fieldName, \App\Purifier::TEXT);
				break;
			case 'modules':
				$value = $request->getArray($fieldName, \App\Purifier::INTEGER);
				break;
		}
		return $value;
	}
}
