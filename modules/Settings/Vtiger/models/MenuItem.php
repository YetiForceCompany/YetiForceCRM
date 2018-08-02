<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// Vtiger Settings MenuItem Model Class

class Settings_Vtiger_MenuItem_Model extends \App\Base
{
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
	 * Url mapping array.
	 *
	 * @var array
	 */
	public static $transformedUrlMapping = [
		'index.php?module=Administration&action=index&parenttab=Settings' => 'index.php?module=Users&parent=Settings&view=List',
		'index.php?module=Settings&action=listroles&parenttab=Settings' => 'index.php?module=Roles&parent=Settings&view=Index',
		'index.php?module=Settings&action=ListProfiles&parenttab=Settings' => 'index.php?module=Profiles&parent=Settings&view=List',
		'index.php?module=Settings&action=listgroups&parenttab=Settings' => 'index.php?module=Groups&parent=Settings&view=List',
		'index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings' => 'index.php?module=SharingAccess&parent=Settings&view=Index',
		'index.php?module=Settings&action=DefaultFieldPermissions&parenttab=Settings' => 'index.php?module=FieldAccess&parent=Settings&view=Index',
		'index.php?module=Settings&action=ListLoginHistory&parenttab=Settings' => 'index.php?module=LoginHistory&parent=Settings&view=List',
		'index.php?module=Settings&action=ModuleManager&parenttab=Settings' => 'index.php?module=ModuleManager&parent=Settings&view=List',
		'index.php?module=PickList&action=PickList&parenttab=Settings' => 'index.php?parent=Settings&module=Picklist&view=Index',
		'index.php?module=Settings&action=listwordtemplates&parenttab=Settings' => 'index.php?module=Settings&submodule=ModuleManager&view=WordTemplates',
		'index.php?module=Settings&action=listnotificationschedulers&parenttab=Settings' => 'index.php?module=Settings&submodule=Vtiger&view=Schedulers',
		'index.php?module=Settings&action=listinventorynotifications&parenttab=Settings' => 'index.php?module=Settings&submodule=Notifications&view=InventoryAlerts',
		'index.php?module=Settings&action=CurrencyListView&parenttab=Settings' => 'index.php?parent=Settings&module=Currency&view=List',
		'index.php?module=Settings&action=TaxConfig&parenttab=Settings' => 'index.php?module=Vtiger&parent=Settings&view=TaxIndex',
		'index.php?module=Settings&action=ProxyServerConfig&parenttab=Settings' => 'index.php?module=Settings&submodule=Server&view=ProxyConfig',
		'index.php?module=Settings&action=OrganizationTermsandConditions&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit',
		'index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings' => 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering',
		'index.php?module=com_vtiger_workflow&action=workflowlist&parenttab=Settings' => 'index.php?module=Workflows&parent=Settings&view=List',
		'index.php?module=com_vtiger_workflow&action=workflowlist' => 'index.php?module=Workflows&parent=Settings&view=List',
		'index.php?module=ConfigEditor&action=index' => 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail',
		'index.php?module=Tooltip&action=QuickView&parenttab=Settings' => 'index.php?module=Settings&submodule=Tooltip&view=Index',
		'index.php?module=Settings&action=Announcements&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit',
		'index.php?module=PickList&action=PickListDependencySetup&parenttab=Settings' => 'index.php?parent=Settings&module=PickListDependency&view=List',
		'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker' => 'index.php?module=Settings&submodule=ModTracker&view=Index',
		'index.php?module=CronTasks&action=ListCronJobs&parenttab=Settings' => 'index.php?module=CronTasks&parent=Settings&view=List',
		'index.php?module=ExchangeConnector&action=index&parenttab=Settings' => 'index.php?module=ExchangeConnector&parent=Settings&view=Index',
	];

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
	 * Function to get the Menu to which the Item belongs.
	 *
	 * @return Settings_Vtiger_Menu_Model instance
	 */
	public function getMenu()
	{
		return $this->menu;
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
	 * Function to get the url to get to the Settings Menu Item.
	 *
	 * @return string - Menu Item landing url
	 */
	public function getUrl()
	{
		$url = $this->get('linkto');
		$url = App\Purifier::decodeHtml($url);
		if (isset(self::$transformedUrlMapping[$url])) {
			$url = self::$transformedUrlMapping[$url];
		}
		if (!empty($this->menu) && $this->get('name') !== 'LBL_SHOP_YETIFORCE') {
			$url .= '&block=' . $this->getMenu()->getId() . '&fieldid=' . $this->getId();
		}
		return $url;
	}

	/**
	 * Function to get the module name, to which the Settings Menu Item belongs to.
	 *
	 * @return string - Module to which the Menu Item belongs
	 */
	public function getModuleName()
	{
		return 'Settings:Vtiger';
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

		return $pinStatus == '1' ? true : false;
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
	 * @param int                             $id
	 * @param bool|Settings_Vtiger_Menu_Model $menuModel
	 *
	 * @return Settings_Vtiger_MenuItem_Model instance
	 */
	public static function getInstanceById($id, $menuModel = false)
	{
		$query = (new \App\Db\Query())->from(self::$itemsTable)->where([self::$itemId => $id]);
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
	 * Static function to get the list of all the items of the given Menu, all items if Menu is not specified.
	 *
	 * @param \Settings_Vtiger_Menu_Model $menuModel
	 * @param bool                        $onlyActive
	 *
	 * @return \Settings_Vtiger_MenuItem_Model[] instances
	 */
	public static function getAll($menuModel = false, $onlyActive = true)
	{
		$conditionsSqls = [];
		if ($menuModel !== false) {
			$conditionsSqls['blockid'] = $menuModel->getId();
		}
		if ($onlyActive) {
			$conditionsSqls['active'] = 0;
		}
		$cacheName = 'getAll:' . implode(':', $conditionsSqls);
		if (\App\Cache::staticHas(__METHOD__, $cacheName)) {
			return \App\Cache::staticGet(__METHOD__, $cacheName);
		}
		$skipMenuItemList = ['LBL_AUDIT_TRAIL', 'LBL_SYSTEM_INFO', 'LBL_PROXY_SETTINGS', 'LBL_DEFAULT_MODULE_VIEW',
			'LBL_FIELDFORMULAS', 'LBL_FIELDS_ACCESS', 'LBL_MAIL_MERGE', 'NOTIFICATIONSCHEDULERS',
			'INVENTORYNOTIFICATION', 'ModTracker', 'LBL_WORKFLOW_LIST', 'LBL_TOOLTIP_MANAGEMENT', ];
		$query = (new App\Db\Query())->from(self::$itemsTable);

		if (count($conditionsSqls) > 0) {
			$query->where($conditionsSqls);
		}
		$dataReader = $query->andWhere(['and', ['NOT IN', 'name', $skipMenuItemList], ['or', ['like', 'admin_access', ',' . App\User::getCurrentUserId() . ','], ['admin_access' => null]]])
			->orderBy('sequence')
			->createCommand()->query();
		$menuItemModels = [];
		while ($rowData = $dataReader->read()) {
			$fieldId = $rowData[self::$itemId];
			$menuItem = self::getInstanceFromArray($rowData);
			if ($menuModel) {
				$menuItem->setMenuFromInstance($menuModel);
			} else {
				$menuItem->setMenu($rowData['blockid']);
			}
			$menuItemModels[$fieldId] = $menuItem;
		}
		$dataReader->close();
		\App\Cache::staticSave(__METHOD__, $cacheName, $menuItemModels);

		return $menuItemModels;
	}

	/**
	 * Function to get the pinned items.
	 *
	 * @param array of fieldids
	 *
	 * @return array - List of <Settings_Vtiger_MenuItem_Model> instances
	 */
	public static function getPinnedItems($fieldList = [])
	{
		$skipMenuItemList = ['LBL_AUDIT_TRAIL', 'LBL_SYSTEM_INFO', 'LBL_PROXY_SETTINGS', 'LBL_DEFAULT_MODULE_VIEW',
			'LBL_FIELDFORMULAS', 'LBL_FIELDS_ACCESS', 'LBL_MAIL_MERGE', 'NOTIFICATIONSCHEDULERS',
			'INVENTORYNOTIFICATION', 'ModTracker', 'LBL_WORKFLOW_LIST', 'LBL_TOOLTIP_MANAGEMENT', ];
		$query = (new App\Db\Query())->from(self::$itemsTable)
			->where(['pinned' => 1, 'active' => 0]);
		if (!empty($fieldList)) {
			$query->andWhere([self::$itemsId => $fieldList]);
		}
		$dataReader = $query->andWhere(['NOT IN', 'name', $skipMenuItemList])
			->createCommand()->query();
		$menuItemModels = [];
		while ($rowData = $dataReader->read()) {
			$menuItem = self::getInstanceFromArray($rowData);
			$menuItem->setMenu($rowData['blockid']);
			$menuItemModels[$rowData[self::$itemId]] = $menuItem;
		}
		$dataReader->close();

		return $menuItemModels;
	}

	/**
	 * Function to get name module.
	 *
	 * @return type module name
	 */
	public function getModule()
	{
		$urlParams = vtlib\Functions::getQueryParams($this->getUrl());
		if (!isset($urlParams['module'])) {
			return false;
		}
		return $urlParams['module'];
	}
}
