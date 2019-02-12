<?php
/**
 * IStorages CRMEntity Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class IStorages extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_istorages';
	public $table_index = 'istorageid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_istoragescf', 'istorageid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_istorages', 'u_yf_istoragescf', 'u_yf_istorages_address'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_istorages' => 'istorageid',
		'u_yf_istoragescf' => 'istorageid',
		'u_yf_istorages_address' => 'istorageaddressid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'subject' => ['istorages', 'subject'],
		'Assigned To' => ['crmentity', 'smownerid'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'subject' => ['istorages', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup window record selection
	public $popup_fields = ['subject'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Function to get storages hierarchy of the given Storage.
	 *
	 * @param int $id - istorageid
	 *                returns Storage hierarchy in array format
	 */
	public function getHierarchy($id, $getRawData = false, $getLinks = true)
	{
		\App\Log::trace('Entering getHierarchy(' . $id . ') method ...');

		$listviewHeader = [];
		$listviewEntries = [];

		$listColumns = AppConfig::module('IStorages', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('IStorages', $colname)) {
				$listviewHeader[] = \App\Language::translate($fieldname);
			}
		}
		$iStoragesList = [];

		// Get the iStorages hierarchy from the top most iStorage in the hierarch of the current iStorage, including the current iStorage
		$encounteredIStorages = [$id];
		$iStoragesList = $this->getParentIStorages($id, $iStoragesList, $encounteredIStorages);

		$baseId = current(array_keys($iStoragesList));
		$iStoragesList = [$baseId => $iStoragesList[$baseId]];

		// Get the iStorages hierarchy (list of child iStorages) based on the current iStorage
		$iStoragesList[$baseId] = $this->getChildIStorages($baseId, $iStoragesList[$baseId], $iStoragesList[$baseId]['depth']);
		$this->getHierarchyData($id, $iStoragesList[$baseId], $baseId, $listviewEntries, $getRawData, $getLinks);
		// Create array of all the iStorages in the hierarchy
		$iStorageHierarchy = ['header' => $listviewHeader, 'entries' => $listviewEntries];
		\App\Log::trace('Exiting getHierarchy method ...');

		return $iStorageHierarchy;
	}

	/**
	 * Function to create array of all the storages in the hierarchy.
	 *
	 * @param int   $id               - Id of the record highest in hierarchy
	 * @param array $iStorageInfoBase
	 * @param int   $iStorageId       - istorageid
	 * @param array $listviewEntries
	 *                                returns All the parent storages of the given Storage in array format
	 */
	public function getHierarchyData($id, $iStorageInfoBase, $iStorageId, &$listviewEntries, $getRawData = false, $getLinks = true)
	{
		\App\Log::trace('Entering getHierarchyData(' . $id . ',' . $iStorageId . ') method ...');

		$hasRecordViewAccess = \App\Privilege::isPermitted('IStorages', 'DetailView', $iStorageId);
		$listColumns = AppConfig::module('IStorages', 'COLUMNS_IN_HIERARCHY');

		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}

		foreach ($listColumns as $colname) {
			// Permission to view storage is restricted, avoid showing field values (except storage name)
			if (\App\Field::getFieldPermission('IStorages', $colname)) {
				$data = \App\Purifier::encodeHtml($iStorageInfoBase[$colname]);
				if ($getRawData === false && $colname == 'subject') {
					if ($iStorageId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=IStorages&action=DetailView&record=' . $iStorageId . '">' . $data . '</a>';
							} else {
								$data = '<span>' . $data . '&nbsp;<span class="fas fa-exclamation-circle"></span></span>';
							}
						}
					} else {
						$data = '<strong>' . $data . '</strong>';
					}
					// - to show the hierarchy of the Storages
					$iStorageDepth = str_repeat(' .. ', $iStorageInfoBase['depth']);
					$data = $iStorageDepth . $data;
				}
				$iStorageInfoData[] = $data;
			}
		}

		$listviewEntries[$iStorageId] = $iStorageInfoData;

		foreach ($iStorageInfoBase as $accId => $iStorageInfo) {
			if (is_array($iStorageInfo) && (int) $accId) {
				$listviewEntries = $this->getHierarchyData($id, $iStorageInfo, $accId, $listviewEntries, $getRawData, $getLinks);
			}
		}

		\App\Log::trace('Exiting getHierarchyData method ...');

		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the upper storages of a given Storage.
	 *
	 * @param int   $id              - istorageid
	 * @param array $parentIStorages - Array of all the parent storages
	 *                               returns All the parent Storages of the given istorageid in array format
	 */
	public function getParentIStorages($id, &$parentIStorages, &$encounteredIStorages, $depthBase = 0)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Entering getParentIStorages(' . $id . ') method ...');

		if ($depthBase == AppConfig::module('IStorages', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParentIStorages method ... - exceeded maximum depth of hierarchy');

			return $parentIStorages;
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = 'SELECT u_yf_istorages.*, u_yf_istorages_address.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM u_yf_istorages' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = u_yf_istorages.istorageid' .
			' INNER JOIN u_yf_istorages_address ON u_yf_istorages.istorageid = u_yf_istorages_address.istorageaddressid ' .
			' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and u_yf_istorages.istorageid = ?';
		$res = $adb->pquery($query, [$id]);

		if ($adb->getRowCount($res) > 0) {
			$row = $adb->getRow($res);
			$parentid = $row['parentid'];

			if ($parentid != '' && $parentid != 0 && !in_array($parentid, $encounteredIStorages)) {
				$encounteredIStorages[] = $parentid;
				$this->getParentIStorages($parentid, $parentIStorages, $encounteredIStorages, $depthBase + 1);
			}

			$parentIStorageInfo = [];
			$depth = 0;

			if (isset($parentIStorages[$parentid])) {
				$depth = $parentIStorages[$parentid]['depth'] + 1;
			}

			$parentIStorageInfo['depth'] = $depth;
			$listColumns = AppConfig::module('IStorages', 'COLUMNS_IN_HIERARCHY');

			if (empty($listColumns)) {
				$listColumns = $this->list_fields_name;
			}

			foreach ($listColumns as $columnname) {
				if ($columnname == 'assigned_user_id') {
					$parentIStorageInfo[$columnname] = $row['user_name'];
				} else {
					$parentIStorageInfo[$columnname] = $row[$columnname];
				}
			}

			$parentIStorages[$id] = $parentIStorageInfo;
		}
		\App\Log::trace('Exiting __getIStorafAccounts method ...');

		return $parentIStorages;
	}

	/**
	 * Function to Recursively get all the child storages of a given Storage.
	 *
	 * @param int   $id             - istorageid
	 * @param array $childIStorages - Array of all the child storages
	 * @param int   $depth          - Depth at which the particular storage has to be placed in the hierarchy
	 *                              returns All the child storages of the given istorageid in array format
	 */
	public function getChildIStorages($id, &$childIStorages, $depthBase)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Entering getChildIStorages(' . $id . ',' . $depthBase . ') method ...');

		if (empty($id) || $depthBase == AppConfig::module('IStorages', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChildIStorages method ... - exceeded maximum depth of hierarchy');

			return $childIStorages;
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = 'SELECT u_yf_istorages.*, u_yf_istorages_address.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM u_yf_istorages' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = u_yf_istorages.istorageid' .
			' INNER JOIN u_yf_istorages_address ON u_yf_istorages.istorageid = u_yf_istorages_address.istorageaddressid ' .
			' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and parentid = ?';

		$res = $adb->pquery($query, [$id]);
		$listColumns = AppConfig::module('IStorages', 'COLUMNS_IN_HIERARCHY');

		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}

		if ($adb->getRowCount($res) > 0) {
			$depth = $depthBase + 1;
			while ($row = $adb->getRow($res)) {
				$childAccId = $row['istorageid'];
				$childIStorageInfo = [];
				$childIStorageInfo['depth'] = $depth;

				foreach ($listColumns as $columnname) {
					if ($columnname == 'assigned_user_id') {
						$childIStorageInfo[$columnname] = $row['user_name'];
					} else {
						$childIStorageInfo[$columnname] = $row[$columnname];
					}
				}

				$childIStorages[$childAccId] = $childIStorageInfo;
				$this->getChildIStorages($childAccId, $childIStorages[$childAccId], $depth);
			}
		}

		\App\Log::trace('Exiting getChildIStorages method ...');

		return $childIStorages;
	}
}
