<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Project extends CRMEntity
{
	public $table_name = 'vtiger_project';
	public $table_index = 'projectid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_projectcf', 'projectid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_project', 'vtiger_projectcf', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_project' => 'projectid',
		'vtiger_projectcf' => 'projectid',
		'vtiger_entity_stats' => 'crmid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Project Name' => 'projectname',
		'Start Date' => 'startdate',
		'Status' => 'projectstatus',
		'Type' => 'projecttype',
		'Assigned To' => 'assigned_user_id',
		'Total time [Sum]' => 'sum_time',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Project Name' => ['project', 'projectname'],
		'Related to' => ['project', 'linktoaccountscontacts'],
		'Start Date' => ['project', 'startdate'],
		'Status' => ['project', 'projectstatus'],
		'Type' => ['project', 'projecttype'],
		'SINGLE_SSalesProcesses' => ['project', 'ssalesprocessesid'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['projectname'];
	// For Alphabetical search
	public $def_basicsearch_col = 'projectname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'projectname';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'projectname', 'assigned_user_id'];

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 * @param mixed $moduleName
	 * @param mixed $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			$moduleInstance = vtlib\Module::getInstance($moduleName);

			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			// Add Project module to the related list of Accounts module
			$accountsModuleInstance = vtlib\Module::getInstance('Accounts');
			$accountsModuleInstance->setRelatedList($moduleInstance, 'Projects', ['ADD', 'SELECT'], 'getDependentsList');

			// Add Project module to the related list of Accounts module
			$contactsModuleInstance = vtlib\Module::getInstance('Contacts');
			$contactsModuleInstance->setRelatedList($moduleInstance, 'Projects', ['ADD', 'SELECT'], 'getDependentsList');

			// Add Project module to the related list of HelpDesk module
			$helpDeskModuleInstance = vtlib\Module::getInstance('HelpDesk');
			$helpDeskModuleInstance->setRelatedList($moduleInstance, 'Projects', ['SELECT'], 'getRelatedList');

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Project']);
				}
			}
		} elseif ('module.postupdate' === $eventType) {
			// Add Comments widget to Project module
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Project']);
				}
			}
		}
	}

	public static function registerLinks()
	{
	}

	/**
	 * Function to get project hierarchy in array format.
	 *
	 * @param int  $id
	 * @param bool $getRawData
	 * @param bool $getLinks
	 *
	 * @return array
	 */
	public function getHierarchy(int $id, bool $getRawData = false, bool $getLinks = true): array
	{
		$listviewHeader = [];
		$listviewEntries = [];
		$listColumns = \App\Config::module('Project', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('Project', $colname)) {
				$listviewHeader[] = App\Language::translate($fieldname, 'Project');
			}
		}
		$rows = [];
		$encountered = [$id];
		$rows = $this->getParent($id, $rows, $encountered);
		$baseId = current(array_keys($rows));
		$rows = [$baseId => $rows[$baseId]];
		$rows[$baseId] = $this->getChild($baseId, $rows[$baseId], $rows[$baseId]['depth']);
		$this->getHierarchyData($id, $rows[$baseId], $baseId, $listviewEntries, $getRawData, $getLinks);
		return ['header' => $listviewHeader, 'entries' => $listviewEntries];
	}

	/**
	 * Function to create array of all the sales in the hierarchy.
	 *
	 * @param int   $id
	 * @param array $baseInfo
	 * @param int   $recordId
	 * @param array $listviewEntries
	 * @param bool  $getRawData
	 * @param bool  $getLinks
	 *
	 * @throws ReflectionException
	 *
	 * @return array
	 */
	public function getHierarchyData(int $id, array $baseInfo, int $recordId, array &$listviewEntries, bool $getRawData = false, bool $getLinks = true): array
	{
		\App\Log::trace('Entering getHierarchyData(' . $id . ',' . $recordId . ') method ...');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$hasRecordViewAccess = $currentUser->isAdminUser() || \App\Privilege::isPermitted('Project', 'DetailView', $recordId);
		$listColumns = \App\Config::module('Project', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		$infoData = [];
		foreach ($listColumns as $colname) {
			if (\App\Field::getFieldPermission('Project', $colname)) {
				$data = \App\Purifier::encodeHtml($baseInfo[$colname]);
				if (false === $getRawData && 'projectname' === $colname) {
					if ($recordId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=Project&action=DetailView&record=' . $recordId . '">' . $data . '</a>';
							} else {
								$data = '<span>' . $data . '&nbsp;<span class="fas fa-exclamation-circle"></span></span>';
							}
						}
					} else {
						$data = '<strong>' . $data . '</strong>';
					}
					$rowDepth = str_repeat(' .. ', $baseInfo['depth']);
					$data = $rowDepth . $data;
				}
				$infoData[] = $data;
			}
		}
		$listviewEntries[$recordId] = $infoData;
		foreach ($baseInfo as $accId => $rowInfo) {
			if (\is_array($rowInfo) && (int) $accId) {
				$listviewEntries = $this->getHierarchyData($id, $rowInfo, $accId, $listviewEntries, $getRawData, $getLinks);
			}
		}
		\App\Log::trace('Exiting getHierarchyData method ...');
		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the upper projects of a given.
	 *
	 * @param int   $id
	 * @param array $parent
	 * @param int[] $encountered
	 * @param int   $depthBase
	 *
	 * @return array
	 */
	public function getParent(int $id, array &$parent, array &$encountered, int $depthBase = 0): array
	{
		\App\Log::trace('Entering getParent(' . $id . ') method ...');
		if ($depthBase === \App\Config::module('Project', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParent method ... - exceeded maximum depth of hierarchy');

			return $parent;
		}
		$moduleModel = Vtiger_Module_Model::getInstance('Project');
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select([
			'vtiger_project.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('vtiger_project')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_project.projectid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_project.projectid' => $id])
			->one();
		if ($row) {
			$parentid = $row['parentid'];
			if ('' !== $parentid && 0 != $parentid && !\in_array($parentid, $encountered)) {
				$encountered[] = $parentid;
				$this->getParent($parentid, $parent, $encountered, $depthBase + 1);
			}
			$parentInfo = [];
			$depth = 0;
			if (isset($parent[$parentid])) {
				$depth = $parent[$parentid]['depth'] + 1;
			}
			$parentInfo['depth'] = $depth;
			$listColumns = \App\Config::module('Project', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->list_fields_name;
			}
			foreach ($listColumns as $columnname) {
				if ('assigned_user_id' === $columnname) {
					$parentInfo[$columnname] = $row['user_name'];
				} elseif (($fieldModel = $moduleModel->getFieldByColumn($columnname)) && \in_array($fieldModel->getFieldDataType(), ['picklist', 'multipicklist'])) {
					$parentInfo[$columnname] = $fieldModel->getDisplayValue($row[$columnname], false, false, true);
				} else {
					$parentInfo[$columnname] = $row[$columnname];
				}
			}
			$parent[$id] = $parentInfo;
		}
		\App\Log::trace('Exiting getParent method ...');
		return $parent;
	}

	/**
	 * Function to Recursively get all the child projects of a given project.
	 *
	 * @param int   $id
	 * @param array $childRow
	 * @param int   $depthBase
	 *
	 * @return array
	 */
	public function getChild(int $id, array &$childRow, int $depthBase): array
	{
		\App\Log::trace('Entering getChild(' . $id . ',' . $depthBase . ') method ...');
		if (empty($id) || $depthBase === \App\Config::module('Project', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChild method ... - exceeded maximum depth of hierarchy');

			return $childRow;
		}
		$moduleModel = Vtiger_Module_Model::getInstance('Project');
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())->select([
			'vtiger_project.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('vtiger_project')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_project.projectid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_project.parentid' => $id])
			->createCommand()->query();
		$listColumns = \App\Config::module('Project', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childAccId = $row['projectid'];
				$childSalesProcessesInfo = [];
				$childSalesProcessesInfo['depth'] = $depth;
				foreach ($listColumns as $columnname) {
					if ('assigned_user_id' === $columnname) {
						$childSalesProcessesInfo[$columnname] = $row['user_name'];
					} elseif (($fieldModel = $moduleModel->getFieldByColumn($columnname)) && \in_array($fieldModel->getFieldDataType(), ['picklist', 'multipicklist'])) {
						$childSalesProcessesInfo[$columnname] = $fieldModel->getDisplayValue($row[$columnname], false, false, true);
					} else {
						$childSalesProcessesInfo[$columnname] = $row[$columnname];
					}
				}
				$childRow[$childAccId] = $childSalesProcessesInfo;
				$this->getChild($childAccId, $childRow[$childAccId], $depth);
			}
			$dataReader->close();
		}
		\App\Log::trace('Exiting getChild method ...');
		return $childRow;
	}
}
