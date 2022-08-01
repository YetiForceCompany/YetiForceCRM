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

// Settings Module Model Class

class Settings_Vtiger_Module_Model extends \App\Base
{
	/** @var string Base table. */
	public $baseTable = 'vtiger_settings_field';

	/** @var string Base index. */
	public $baseIndex = 'fieldid';

	/** @var array List fields. */
	public $listFields = ['name' => 'Name', 'description' => 'Description'];
	public $nameFields = ['name'];

	/** @var string Module name. */
	public $name = 'Vtiger';

	public function getName($includeParentIfExists = false)
	{
		if ($includeParentIfExists) {
			return $this->getParentName() . ':' . $this->name;
		}
		return $this->name;
	}

	public function getParentName()
	{
		return 'Settings';
	}

	public function getBaseTable()
	{
		return $this->baseTable;
	}

	public function getBaseIndex()
	{
		return $this->baseIndex;
	}

	public function setListFields($fieldNames)
	{
		$this->listFields = $fieldNames;

		return $this;
	}

	/**
	 * Function returns list of fields available in list view.
	 *
	 * @return \App\Base[]
	 */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			$fieldObjects = [];
			foreach ($this->listFields as $fieldName => $fieldLabel) {
				$fieldObjects[$fieldName] = new \App\Base([
					'name' => $fieldName
				]);
				if (\is_array($fieldLabel)) {
					$fieldObjects[$fieldName]->set('label', $fieldLabel[0]);
					$fieldObjects[$fieldName]->set('moduleName', $fieldLabel[1]);
				} else {
					$fieldObjects[$fieldName]->set('label', $fieldLabel);
				}
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function to get name fields of this module.
	 *
	 * @return string[] list field names
	 */
	public function getNameFields()
	{
		return $this->nameFields;
	}

	/**
	 * Function to get field using field name.
	 *
	 * @param string $fieldName
	 *
	 * @return <Field_Model>
	 */
	public function getField($fieldName)
	{
		return new \App\Base(['name' => $fieldName, 'label' => $fieldName]);
	}

	public function hasCreatePermissions()
	{
		return true;
	}

	public function isPagingSupported()
	{
		return true;
	}

	/**
	 * Function to get the instance of Settings module model.
	 *
	 * @param string $name
	 *
	 * @return $this instance
	 */
	public static function getInstance($name = 'Settings:Vtiger')
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		return new $modelClassName();
	}

	/**
	 * Function to get Index view Url.
	 *
	 * @return string URL
	 */
	public function getIndexViewUrl(): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=Index';
	}

	public static function addSettingsField($block, $params)
	{
		$db = App\Db::getInstance();
		$blockId = vtlib\Deprecated::getSettingsBlockId($block);
		$sequence = (new App\Db\Query())->from('vtiger_settings_field')->where(['blockid' => $blockId])
			->max('sequence');
		$params['blockid'] = $blockId;
		$params['sequence'] = ((int) $sequence) + 1;
		$db->createCommand()->insert('vtiger_settings_field', $params)->execute();
		Settings_Vtiger_Menu_Model::clearCache();
	}

	public static function deleteSettingsField($block, $name)
	{
		App\Db::getInstance()->createCommand()->delete('vtiger_settings_field', ['name' => $name, 'blockid' => vtlib\Deprecated::getSettingsBlockId($block)])->execute();
		Settings_Vtiger_Menu_Model::clearCache();
	}

	/**
	 * Delete settings field by module name.
	 *
	 * @param type $moduleName
	 */
	public static function deleteSettingsFieldBymodule($moduleName)
	{
		$db = App\Db::getInstance();
		$db->createCommand()->delete('vtiger_settings_field', ['like', 'linkto', "module={$moduleName}&"])->execute();
		Settings_Vtiger_Menu_Model::clearCache();
	}
}
