<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/*
 * Vtiger Settings MenuItem Model Class
 */

class Settings_Vtiger_MenuItem_Model extends Vtiger_Base_Model
{

	protected static $itemsTable = 'vtiger_settings_field';
	protected static $itemId = 'fieldid';
	public static $transformedUrlMapping = array(
		'index.php?module=Administration&action=index&parenttab=Settings' => 'index.php?module=Users&parent=Settings&view=List',
		'index.php?module=Settings&action=listroles&parenttab=Settings' => 'index.php?module=Roles&parent=Settings&view=Index',
		'index.php?module=Settings&action=ListProfiles&parenttab=Settings' => 'index.php?module=Profiles&parent=Settings&view=List',
		'index.php?module=Settings&action=listgroups&parenttab=Settings' => 'index.php?module=Groups&parent=Settings&view=List',
		'index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings' => 'index.php?module=SharingAccess&parent=Settings&view=Index',
		'index.php?module=Settings&action=DefaultFieldPermissions&parenttab=Settings' => 'index.php?module=FieldAccess&parent=Settings&view=Index',
		'index.php?module=Settings&action=ListLoginHistory&parenttab=Settings' => 'index.php?module=LoginHistory&parent=Settings&view=List',
		'index.php?module=Settings&action=ModuleManager&parenttab=Settings' => 'index.php?module=ModuleManager&parent=Settings&view=List',
		'index.php?module=PickList&action=PickList&parenttab=Settings' => 'index.php?parent=Settings&module=Picklist&view=Index',
		'index.php?module=Settings&action=listemailtemplates&parenttab=Settings' => 'index.php?module=Emails&view=ListTemplates',
		'index.php?module=Settings&action=listwordtemplates&parenttab=Settings' => 'index.php?module=Settings&submodule=ModuleManager&view=WordTemplates',
		'index.php?module=Settings&action=listnotificationschedulers&parenttab=Settings' => 'index.php?module=Settings&submodule=Vtiger&view=Schedulers',
		'index.php?module=Settings&action=listinventorynotifications&parenttab=Settings' => 'index.php?module=Settings&submodule=Notifications&view=InventoryAlerts',
		'index.php?module=Settings&action=OrganizationConfig&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=CompanyDetails',
		'index.php?module=Settings&action=EmailConfig&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail',
		'index.php?module=Settings&action=CurrencyListView&parenttab=Settings' => 'index.php?parent=Settings&module=Currency&view=List',
		'index.php?module=Settings&action=TaxConfig&parenttab=Settings' => 'index.php?module=Vtiger&parent=Settings&view=TaxIndex',
		'index.php?module=Settings&action=ProxyServerConfig&parenttab=Settings' => 'index.php?module=Settings&submodule=Server&view=ProxyConfig',
		'index.php?module=Settings&action=OrganizationTermsandConditions&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit',
		'index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings' => 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering',
		'index.php?module=com_vtiger_workflow&action=workflowlist&parenttab=Settings' => 'index.php?module=Workflows&parent=Settings&view=List',
		'index.php?module=com_vtiger_workflow&action=workflowlist' => 'index.php?module=Workflows&parent=Settings&view=List',
		'index.php?module=ConfigEditor&action=index' => 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail',
		'index.php?module=Tooltip&action=QuickView&parenttab=Settings' => 'index.php?module=Settings&submodule=Tooltip&view=Index',
		'index.php?module=CustomerPortal&action=index&parenttab=Settings' => 'index.php?module=CustomerPortal&parent=Settings&view=Index',
		'index.php?module=Settings&action=Announcements&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit',
		'index.php?module=PickList&action=PickListDependencySetup&parenttab=Settings' => 'index.php?parent=Settings&module=PickListDependency&view=List',
		'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker' => 'index.php?module=Settings&submodule=ModTracker&view=Index',
		'index.php?module=CronTasks&action=ListCronJobs&parenttab=Settings' => 'index.php?module=CronTasks&parent=Settings&view=List',
		'index.php?module=Webforms&action=index&parenttab=Settings' => 'index.php?module=Webforms&parent=Settings&view=List',
		'index.php?module=ExchangeConnector&action=index&parenttab=Settings' => 'index.php?module=ExchangeConnector&parent=Settings&view=Index'
	);

	/**
	 * Function to get the Id of the menu item
	 * @return <Number> - Menu Item Id
	 */
	public function getId()
	{
		return $this->get(self::$itemId);
	}

	/**
	 * Function to get the Menu to which the Item belongs
	 * @return Settings_Vtiger_Menu_Model instance
	 */
	public function getMenu()
	{
		return $this->menu;
	}

	/**
	 * Function to set the Menu to which the Item belongs, given Menu Id
	 * @param <Number> $menuId
	 * @return Settings_Vtiger_MenuItem_Model
	 */
	public function setMenu($menuId)
	{
		$this->menu = Settings_Vtiger_Menu_Model::getInstanceById($menuId);
		return $this;
	}

	/**
	 * Function to set the Menu to which the Item belongs, given Menu Model instance
	 * @param <Settings_Vtiger_Menu_Model> $menu - Settings Menu Model instance
	 * @return Settings_Vtiger_MenuItem_Model
	 */
	public function setMenuFromInstance($menu)
	{
		$this->menu = $menu;
		return $this;
	}

	/**
	 * Function to get the url to get to the Settings Menu Item
	 * @return <String> - Menu Item landing url
	 */
	public function getUrl()
	{
		$url = $this->get('linkto');
		$url = decode_html($url);
		if (isset(self::$transformedUrlMapping[$url])) {
			$url = self::$transformedUrlMapping[$url];
		}
		if (!empty($this->menu)) {
			$url .= '&block=' . $this->getMenu()->getId() . '&fieldid=' . $this->getId();
		}
		return $url;
	}

	/**
	 * Function to get the module name, to which the Settings Menu Item belongs to
	 * @return <String> - Module to which the Menu Item belongs
	 */
	public function getModuleName()
	{
		return 'Settings:Vtiger';
	}

	/**
	 *  Function to get the pin and unpin action url
	 */
	public function getPinUnpinActionUrl()
	{
		return 'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&fieldid=' . $this->getId();
	}

	/**
	 * Function to verify whether menuitem is pinned or not
	 * @return <Boolean> true to pinned, false to not pinned.
	 */
	public function isPinned()
	{
		$pinStatus = $this->get('pinned');
		return $pinStatus == '1' ? true : false;
	}

	/**
	 * Function which will update the pin status 
	 * @param <Boolean> $pinned - true to enable , false to disable
	 */
	private function updatePinStatus($pinned = false)
	{
		$db = PearDatabase::getInstance();
		$pinnedStaus = 0;
		if ($pinned) {
			$pinnedStaus = 1;
		}
		$db->update(self::$itemsTable, ['pinned' => $pinnedStaus], self::$itemId . ' = ?', [$this->getId()]);
	}

	/**
	 * Function which will enable the field as pinned
	 */
	public function markPinned()
	{
		$this->updatePinStatus(1);
	}

	/**
	 * Function which will disable the field pinned status
	 */
	public function unMarkPinned()
	{
		$this->updatePinStatus();
	}

	/**
	 * Function to get the instance of the Menu Item model given the valuemap array
	 * @param <Array> $valueMap
	 * @return Settings_Vtiger_MenuItem_Model instance
	 */
	public static function getInstanceFromArray($valueMap)
	{
		return new self($valueMap);
	}

	/**
	 * Function to get the instance of the Menu Item model, given name and Menu instance
	 * @param <String> $name
	 * @param <Settings_Vtiger_Menu_Model> $menuModel
	 * @return Settings_Vtiger_MenuItem_Model instance
	 */
	public static function getInstance($name, $menuModel = false)
	{
		$db = PearDatabase::getInstance();

		$sql = sprintf('SELECT * FROM %s WHERE name = ?', self::$itemsTable);
		$params = [$name];

		if ($menuModel) {
			$sql .= ' WHERE blockid = ?';
			$params[] = $menuModel->getId();
		}
		$result = $db->pquery($sql, $params);

		if ($db->num_rows($result) > 0) {
			$rowData = $db->query_result_rowdata($result, 0);
			$menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
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
	 * Function to get the instance of the Menu Item model, given item id and Menu instance
	 * @param <String> $name
	 * @param <Settings_Vtiger_Menu_Model> $menuModel
	 * @return Settings_Vtiger_MenuItem_Model instance
	 */
	public static function getInstanceById($id, $menuModel = false)
	{
		$db = PearDatabase::getInstance();

		$sql = sprintf('SELECT * FROM %s WHERE %s = ?', self::$itemsTable, self::$itemId);
		$params = array($id);

		if ($menuModel) {
			$sql .= ' WHERE blockid = ?';
			$params[] = $menuModel->getId();
		}
		$result = $db->pquery($sql, $params);

		if ($db->num_rows($result) > 0) {
			$rowData = $db->query_result_rowdata($result, 0);
			$menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
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
	 * Static function to get the list of all the items of the given Menu, all items if Menu is not specified
	 * @param <Settings_Vtiger_Menu_Model> $menuModel
	 * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
	 */
	public static function getAll($menuModel = false, $onlyActive = true)
	{
		$skipMenuItemList = array('LBL_AUDIT_TRAIL', 'LBL_SYSTEM_INFO', 'LBL_PROXY_SETTINGS', 'LBL_DEFAULT_MODULE_VIEW',
			'LBL_FIELDFORMULAS', 'LBL_FIELDS_ACCESS', 'LBL_MAIL_MERGE', 'NOTIFICATIONSCHEDULERS',
			'INVENTORYNOTIFICATION', 'ModTracker', 'LBL_WORKFLOW_LIST', 'LBL_TOOLTIP_MANAGEMENT', 'Webforms Configuration Editor');

		$db = PearDatabase::getInstance();
		$sql = sprintf('SELECT * FROM %s', self::$itemsTable);
		$params = [];

		$conditionsSqls = [];
		if ($menuModel != false) {
			$conditionsSqls[] = 'blockid = ?';
			$params[] = $menuModel->getId();
		}
		if ($onlyActive) {
			$conditionsSqls[] = 'active = 0';
		}
		if (count($conditionsSqls) > 0) {
			$sql .= sprintf(' WHERE %s', implode(' && ', $conditionsSqls));
		}
		$sql .= ' && name NOT IN (' . generateQuestionMarks($skipMenuItemList) . ')';

		$sql .= ' ORDER BY sequence';
		$result = $db->pquery($sql, array_merge($params, $skipMenuItemList));

		$menuItemModels = [];
		while ($rowData = $db->getRow($result)) {
			$fieldId = $rowData[self::$itemId];
			$menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
			if ($menuModel) {
				$menuItem->setMenuFromInstance($menuModel);
			} else {
				$menuItem->setMenu($rowData['blockid']);
			}
			$menuItemModels[$fieldId] = $menuItem;
		}
		return $menuItemModels;
	}

	/**
	 * Function to get the pinned items 
	 * @param array of fieldids.
	 * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
	 */
	public static function getPinnedItems($fieldList = array())
	{
		$skipMenuItemList = array('LBL_AUDIT_TRAIL', 'LBL_SYSTEM_INFO', 'LBL_PROXY_SETTINGS', 'LBL_DEFAULT_MODULE_VIEW',
			'LBL_FIELDFORMULAS', 'LBL_FIELDS_ACCESS', 'LBL_MAIL_MERGE', 'NOTIFICATIONSCHEDULERS',
			'INVENTORYNOTIFICATION', 'ModTracker', 'LBL_WORKFLOW_LIST', 'LBL_TOOLTIP_MANAGEMENT', 'Webforms Configuration Editor');

		$db = PearDatabase::getInstance();

		$query = sprintf('SELECT * FROM %s WHERE pinned = 1 && active = 0', self::$itemsTable);
		if (!empty($fieldList)) {
			if (!is_array($fieldList)) {
				$fieldList = array($fieldList);
			}
			$query .=' && ' . self::$itemsId . ' IN (' . generateQuestionMarks($fieldList) . ')';
		}
		$query .= ' && name NOT IN (' . generateQuestionMarks($skipMenuItemList) . ')';

		$result = $db->pquery($query, array_merge($fieldList, $skipMenuItemList));
		$noOfMenus = $db->num_rows($result);

		$menuItemModels = array();
		for ($i = 0; $i < $noOfMenus; ++$i) {
			$fieldId = $db->query_result($result, $i, self::$itemId);
			$rowData = $db->query_result_rowdata($result, $i);
			$menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
			$menuItem->setMenu($rowData['blockid']);
			$menuItemModels[$fieldId] = $menuItem;
		}
		return $menuItemModels;
	}

	/**
	 * used only in old layout 
	 * @param type $url
	 * @return type modulename 
	 */
	public function getModuleNameFromUrl($url)
	{
		$query_str = parse_url(htmlspecialchars_decode($url), PHP_URL_QUERY);
		parse_str($query_str, $query_params);
		if ($query_params[parent]) {
			return ("$query_params[parent]:$query_params[module]");
		}
		return $query_params[module];
	}

	/**
	 * Function to get name module
	 * @return type module name
	 */
	public function getModule()
	{
		$urlParams = vtlib\Functions::getQueryParams($this->getUrl());
		return $urlParams['module'];
	}
}
