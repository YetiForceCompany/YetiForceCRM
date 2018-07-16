<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// Settings Module Model Class

class Settings_Vtiger_Module_Model extends \App\Base
{
	public $baseTable = 'vtiger_settings_field';
	public $baseIndex = 'fieldid';
	public $listFields = ['name' => 'Name', 'description' => 'Description'];
	public $nameFields = ['name'];
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

	public function getListFields()
	{
		if (!isset($this->listFieldModels)) {
			$fields = $this->listFields;
			$fieldObjects = [];
			foreach ($fields as $fieldName => $fieldLabel) {
				$fieldObjects[$fieldName] = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function to get name fields of this module.
	 *
	 * @return <Array> list field names
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

	/**
	 * Function to get all the Settings menus.
	 *
	 * @return <Array> - List of Settings_Vtiger_Menu_Model instances
	 */
	public function getMenus()
	{
		return Settings_Vtiger_Menu_Model::getAll();
	}

	/**
	 * Function to get all the Settings menu items for the given menu.
	 *
	 * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
	 */
	public function getMenuItems($menu = false)
	{
		$menuModel = false;
		if ($menu) {
			$menuModel = Settings_Vtiger_Menu_Model::getInstance($menu);
		}
		return Settings_Vtiger_MenuItem_Model::getAll($menuModel);
	}

	public function isPagingSupported()
	{
		return true;
	}

	/**
	 * Function to get the instance of Settings module model.
	 *
	 * @return Settings_Vtiger_Module_Model instance
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
	public function getIndexViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=Index';
	}

	public function prepareMenuToDisplay($menuModels, $moduleName, $selectedMenuId, $fieldId)
	{
		if (!empty($selectedMenuId)) {
			$selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($selectedMenuId);
		} elseif (!empty($moduleName) && $moduleName != 'Vtiger') {
			$fieldItem = Settings_Vtiger_Index_View::getSelectedFieldFromModule($menuModels, $moduleName);
			if ($fieldItem) {
				$selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($fieldItem->get('blockid'));
				$fieldId = $fieldItem->get('fieldid');
			} else {
				reset($menuModels);
				$firstKey = key($menuModels);
				$selectedMenu = $menuModels[$firstKey];
			}
		} else {
			$selectedMenu = false;
		}

		$menu = [];
		foreach ($menuModels as $blockId => $menuModel) {
			if ($menuModel->getType() != 1) {
				$childs = [];
				foreach ($menuModel->getMenuItems() as $menuItem) {
					if ($menuItem->getId() == $fieldId) {
						$this->set('selected', $menuItem);
					}
					$childs[] = [
						'id' => $menuItem->getId(),
						'active' => $menuItem->getId() == $fieldId ? true : false,
						'name' => $menuItem->get('name'),
						'type' => 'Shortcut',
						'sequence' => $menuModel->get('sequence'),
						'newwindow' => '0',
						'icon' => $menuItem->get('iconpath'),
						'dataurl' => $menuItem->getUrl(),
						'parent' => 'Settings',
						'moduleName' => Vtiger_Menu_Model::getModuleNameFromUrl($menuItem->getUrl()),
					];
				}
				$menu[] = [
					'id' => $blockId,
					'active' => ($selectedMenu && $selectedMenu->get('blockid') == $blockId) ? true : false,
					'name' => $menuModel->getLabel(),
					'type' => 'Label',
					'sequence' => $menuModel->get('sequence'),
					'childs' => $childs,
					'icon' => $menuModel->get('icon'),
					'moduleName' => 'Settings::Vtiger',
				];
			} else {
				$menu[] = [
					'id' => $blockId,
					'active' => ($selectedMenu && $selectedMenu->get('blockid') == $blockId) ? true : false,
					'name' => $menuModel->getLabel(),
					'type' => 'Shortcut',
					'sequence' => $menuModel->get('sequence'),
					'newwindow' => '0',
					'icon' => $menuModel->get('icon'),
					'dataurl' => $menuModel->get('linkto'),
					'moduleName' => 'Settings::Vtiger',
				];
			}
		}
		return $menu;
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
	}

	public static function deleteSettingsField($block, $name)
	{
		App\Db::getInstance()->createCommand()->delete('vtiger_settings_field', ['name' => $name, 'blockid' => vtlib\Deprecated::getSettingsBlockId($block)])->execute();
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
	}
}
