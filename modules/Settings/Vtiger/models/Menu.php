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

// Settings Menu Model Class

class Settings_Vtiger_Menu_Model extends \App\Base
{
	/**
	 * Menu type: Label.
	 */
	private const TYPE_LABEL = 0;
	/**
	 * Menu type: Link.
	 */
	private const TYPE_LINK = 1;

	protected static $menusTable = 'vtiger_settings_blocks';
	protected static $menuId = 'blockid';

	/**
	 * @var array Parses the URL into variables
	 */
	protected $params;

	/**
	 * Function to get the Id of the Menu Model.
	 *
	 * @return int - Menu Id
	 */
	public function getId()
	{
		return $this->get(self::$menuId);
	}

	/**
	 * Function to get the menu label.
	 *
	 * @return string - Menu Label
	 */
	public function getLabel()
	{
		return $this->get('label');
	}

	/**
	 * Function to get the menu type.
	 *
	 * @return string - Menu Label
	 */
	public function getType()
	{
		return $this->get('type');
	}

	/**
	 * Function to get the url to get to the Settings Menu Block.
	 *
	 * @return string - Menu Item landing url
	 */
	public function getUrl()
	{
		return App\Purifier::decodeHtml((string) $this->get('linkto'));
	}

	/**
	 * Check if the menu item has been selected.
	 *
	 * @param string $moduleName
	 * @param string $view
	 * @param string $mode
	 *
	 * @return bool
	 */
	public function isSelected(string $moduleName, string $view, string $mode = ''): bool
	{
		return $this->getParam('view') === $view && $this->getParam('module') === $moduleName && $this->getParam('mode') === $mode;
	}

	/**
	 * Check permission.
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public function isPermitted(int $userId = 0): bool
	{
		$userId = $userId ?: \App\User::getCurrentUserId();
		return (empty($this->getParam('module')) || \App\Security\AdminAccess::isPermitted($this->getParam('module'), $userId))
			&& (empty($this->get('admin_access')) || false !== strpos($this->get('admin_access'), ",{$userId},"));
	}

	/**
	 * Gets data from URL.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getParam(string $key): string
	{
		if (!isset($this->params)) {
			$this->params = \vtlib\Functions::getQueryParams($this->getUrl());
		}
		return $this->params[$key] ?? '';
	}

	/**
	 * Function returns menu items for the current menu.
	 *
	 * @return \Settings_Vtiger_MenuItem_Model[]
	 */
	public function getMenuItems()
	{
		return Settings_Vtiger_MenuItem_Model::getAll($this->getId());
	}

	/**
	 * Gets menu element by module.
	 *
	 * @param string $moduleName
	 *
	 * @return Settings_Vtiger_MenuItem_Model|null
	 */
	public static function getSelectedFieldFromModule(string $moduleName): ?Settings_Vtiger_MenuItem_Model
	{
		foreach (self::getAll() as $menuModel) {
			foreach ($menuModel->getMenuItems() as $menuItem) {
				if ($menuItem->getParam('module') === $moduleName && $menuItem->isPermitted()) {
					return $menuItem;
				}
			}
		}
		return null;
	}

	/**
	 * Static function to get the list of all the Settings Menus.
	 *
	 * @return \Settings_Vtiger_Menu_Model[]
	 */
	public static function getAll(): array
	{
		$key = '';
		$cacheName = 'MenuAll';
		if (!\App\Cache::has($cacheName, $key)) {
			$menuModels = [];
			$dataReader = (new App\Db\Query())->from(self::$menusTable)->orderBy(['sequence' => SORT_ASC])->createCommand()->query();
			while ($rowData = $dataReader->read()) {
				$blockId = (int) $rowData[self::$menuId];
				$menuModels[$blockId] = self::getInstanceFromArray($rowData);
			}
			$dataReader->close();
			\App\Cache::save($cacheName, $key, $menuModels);
		}
		return \App\Cache::get($cacheName, $key);
	}

	/**
	 * Static Function to get the instance of Settings Menu model with the given value map array.
	 *
	 * @param array $valueMap
	 *
	 * @return <Settings_Vtiger_Menu_Model> instance
	 */
	public static function getInstanceFromArray($valueMap)
	{
		return new self($valueMap);
	}

	/**
	 * Array with instances, kay as number id element of menu.
	 *
	 * @var array
	 */
	public static $cacheInstance = false;

	/**
	 * Gets block instance.
	 *
	 * @param int $id
	 *
	 * @return Settings_Vtiger_Menu_Model|null
	 */
	public static function getInstanceById(int $id)
	{
		return self::getAll()[$id] ?? null;
	}

	/**
	 * Static Function to get the instance of Settings Menu model for the given menu name.
	 *
	 * @param string $name - Menu Name
	 *
	 * @return <Settings_Vtiger_Menu_Model> instance
	 */
	public static function getInstance($name)
	{
		$rowData = (new App\Db\Query())
			->from(self::$menusTable)
			->where(['label' => $name])
			->limit(1)
			->one();
		if ($rowData) {
			return self::getInstanceFromArray($rowData);
		}
		return false;
	}

	/**
	 * Gets menu elements.
	 *
	 * @param string     $moduleName
	 * @param string     $view
	 * @param string     $mode
	 * @param mixed|null $selected
	 *
	 * @return array
	 */
	public static function getMenu(string $moduleName, string $view, string $mode = '', &$selected = null): array
	{
		$selectedFieldId = 0;
		$selectedBlockId = 0;
		$menu = [];
		foreach (self::getAll() as $blockId => $menuModel) {
			if (!$menuModel->isPermitted()) {
				continue;
			}
			if (self::TYPE_LABEL === (int) $menuModel->getType()) {
				$children = [];
				foreach ($menuModel->getMenuItems() as $fieldId => $menuItem) {
					if (!$menuItem->isPermitted()) {
						continue;
					}
					if (!$selectedBlockId && $menuItem->isSelected($moduleName, $view, $mode)) {
						$selectedBlockId = $menuItem->getBlockId();
						$selectedFieldId = $menuItem->getId();
						$selected = $menuItem;
					}
					$children[$fieldId] = [
						'id' => $menuItem->getId(),
						'active' => $selectedFieldId === $menuItem->getId(),
						'name' => \App\Language::translate($menuItem->get('name'), $menuItem->getModuleName()),
						'type' => 'Shortcut',
						'sequence' => $menuItem->get('sequence'),
						'newwindow' => '0',
						'icon' => $menuItem->get('iconpath'),
						'dataurl' => $menuItem->getUrl(),
						'parent' => 'Settings',
						'moduleName' => $menuItem->getModuleName(),
					];
					if ($menuItem->get('premium')) {
						$children[$fieldId]['addonIcon'] = 'yfi-premium color-yellow-600';
						$children[$fieldId]['addonIconTitle'] = App\Language::translate('LBL_PAID_FUNCTIONALITY');
					}
				}
				$menu[$blockId] = [
					'id' => $blockId,
					'active' => $selectedBlockId === $blockId,
					'name' => \App\Language::translate($menuModel->getLabel(), 'Settings::Vtiger'),
					'type' => 'Label',
					'sequence' => $menuModel->get('sequence'),
					'childs' => $children,
					'icon' => $menuModel->get('icon'),
					'moduleName' => 'Settings::Vtiger',
				];
			} else {
				if (!$selectedBlockId && $menuModel->isSelected($moduleName, $view, $mode)) {
					$selectedBlockId = $blockId;
				}
				$menu[$blockId] = [
					'id' => $blockId,
					'active' => $selectedBlockId === $blockId,
					'name' => \App\Language::translate($menuModel->getLabel(), 'Settings::Vtiger'),
					'type' => 'Shortcut',
					'sequence' => $menuModel->get('sequence'),
					'newwindow' => '0',
					'icon' => $menuModel->get('icon'),
					'dataurl' => $menuModel->getUrl(),
					'moduleName' => 'Settings::Vtiger',
				];
			}
		}
		if (0 === $selectedFieldId && 0 === $selectedBlockId && ($selected = self::getSelectedFieldFromModule($moduleName))) {
			$menu[$selected->getBlockId()]['active'] = true;
			$menu[$selected->getBlockId()]['childs'][$selected->getId()]['active'] = true;
		}
		return $menu;
	}

	/**
	 * Clear cache.
	 *
	 * @return void
	 */
	public static function clearCache(): void
	{
		\App\Cache::delete('MenuItemAll', \Settings_Vtiger_MenuItem_Model::ACTIVE);
		\App\Cache::delete('MenuItemAll', \Settings_Vtiger_MenuItem_Model::INACTIVE);
		\App\Cache::delete('MenuAll', '');
	}
}
