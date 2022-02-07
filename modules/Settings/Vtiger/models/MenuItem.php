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

// Vtiger Settings MenuItem Model Class

class Settings_Vtiger_MenuItem_Model extends Settings_Vtiger_Menu_Model
{
	/**
	 * Active.
	 */
	const ACTIVE = 0;

	/**
	 * Inactive.
	 */
	const INACTIVE = 1;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected static $itemsTable = 'vtiger_settings_field';

	/**
	 * Table index.
	 *
	 * @var string
	 */
	protected static $itemId = 'fieldid';

	/**
	 * Function to get the Id of the menu item.
	 *
	 * @return <Number> - Menu Item Id
	 */
	public function getId()
	{
		return $this->get(self::$itemId);
	}

	/**
	 * Function to set the Menu to which the Item belongs, given Menu Id.
	 *
	 * @param <Number> $menuId
	 *
	 * @return Settings_Vtiger_MenuItem_Model
	 */
	public function setMenu($menuId)
	{
		$this->menu = Settings_Vtiger_Menu_Model::getInstanceById($menuId);

		return $this;
	}

	/**
	 * Gets block instance.
	 *
	 * @return Settings_Vtiger_Menu_Model|null
	 */
	public function getBlock(): ?Settings_Vtiger_Menu_Model
	{
		return \Settings_Vtiger_Menu_Model::getInstanceById($this->getBlockId());
	}

	/**
	 * Gets block ID.
	 *
	 * @return int
	 */
	public function getBlockId()
	{
		return $this->get('blockid');
	}

	/**
	 * Function to set the Menu to which the Item belongs, given Menu Model instance.
	 *
	 * @param <Settings_Vtiger_Menu_Model> $menu - Settings Menu Model instance
	 *
	 * @return Settings_Vtiger_MenuItem_Model
	 */
	public function setMenuFromInstance($menu)
	{
		$this->menu = $menu;

		return $this;
	}

	/**
	 * Function to get the module name, to which the Settings Menu Item belongs to.
	 *
	 * @return string - Module to which the Menu Item belongs
	 */
	public function getModuleName()
	{
		return 'Settings:' . ($this->getParam('module') ?: 'Vtiger');
	}

	/**
	 *  Function to get the pin and unpin action url.
	 */
	public function getPinUnpinActionUrl()
	{
		return 'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&fieldid=' . $this->getId();
	}

	/**
	 * Function to verify whether menuitem is pinned or not.
	 *
	 * @return bool true to pinned, false to not pinned
	 */
	public function isPinned()
	{
		$pinStatus = $this->get('pinned');

		return '1' == $pinStatus ? true : false;
	}

	/**
	 * Function which will update the pin status.
	 *
	 * @param bool $pinned - true to enable , false to disable
	 */
	private function updatePinStatus($pinned = false)
	{
		$pinnedStaus = 0;
		if ($pinned) {
			$pinnedStaus = 1;
		}
		\App\Db::getInstance()->createCommand()->update(self::$itemsTable, ['pinned' => $pinnedStaus], [self::$itemId => $this->getId()])->execute();
	}

	/**
	 * Function which will enable the field as pinned.
	 */
	public function markPinned()
	{
		$this->updatePinStatus(1);
	}

	/**
	 * Function which will disable the field pinned status.
	 */
	public function unMarkPinned()
	{
		$this->updatePinStatus();
	}

	/**
	 * Function to get the instance of the Menu Item model given the valuemap array.
	 *
	 * @param array $valueMap
	 *
	 * @return Settings_Vtiger_MenuItem_Model instance
	 */
	public static function getInstanceFromArray($valueMap)
	{
		return new self($valueMap);
	}

	/**
	 * Function to get the instance of the Menu Item model, given name and Menu instance.
	 *
	 * @param string                          $name
	 * @param bool|Settings_Vtiger_Menu_Model $menuModel
	 *
	 * @return Settings_Vtiger_MenuItem_Model instance
	 */
	public static function getInstance($name, $menuModel = false)
	{
		$query = (new \App\Db\Query())->from(self::$itemsTable)->where(['name' => $name]);
		if ($menuModel) {
			$query->andWhere(['blockid' => $menuModel->getId()]);
		}
		$rowData = $query->one();
		if ($rowData) {
			$menuItem = self::getInstanceFromArray($rowData);
			if ($menuModel) {
				$menuItem->setMenuFromInstance($menuModel);
			} else {
				$menuItem->setMenu($rowData['blockid']);
			}

			return $menuItem;
		}
		return false;
	}

	/**
	 * Function to get the instance of the Menu Item model, given item id and Menu instance.
	 *
	 * @param int $id
	 *
	 * @return Settings_Vtiger_MenuItem_Model|null
	 */
	public static function getInstanceById(int $id)
	{
		return self::getAll()[$id] ?? null;
	}

	/**
	 * Static function to get the list of all the items of the given Menu, all items if Menu is not specified.
	 *
	 * @param int $blockId
	 * @param int $active
	 *
	 * @return Settings_Vtiger_MenuItem_Model[]
	 */
	public static function getAll(int $blockId = 0, int $active = self::ACTIVE): array
	{
		$key = $active;
		$cacheName = 'MenuItemAll';
		if (!\App\Cache::has($cacheName, $key)) {
			$menuItemModels = [];
			$dataReader = (new App\Db\Query())->from(self::$itemsTable)->where(['active' => $active])->orderBy('sequence')->createCommand()->query();
			while ($rowData = $dataReader->read()) {
				$fieldId = (int) $rowData[self::$itemId];
				$menuItem = self::getInstanceFromArray($rowData);
				$menuItemModels[0][$fieldId] = $menuItem;
				$menuItemModels[$menuItem->getBlockId()][$fieldId] = $menuItem;
			}
			$dataReader->close();
			\App\Cache::save($cacheName, $key, $menuItemModels);
		}
		$menuItemModels = \App\Cache::get($cacheName, $key);
		return $menuItemModels[$blockId] ?? [];
	}

	/**
	 * Function to get the pinned items.
	 *
	 * @return \Settings_Vtiger_MenuItem_Model[]
	 */
	public static function getPinnedItems(): array
	{
		$menuItems = self::getAll();
		foreach ($menuItems as $key => $menuItem) {
			if (!$menuItem->isPermitted() || 1 !== (int) $menuItem->get('pinned')) {
				unset($menuItems[$key]);
			}
		}
		return $menuItems;
	}
}
