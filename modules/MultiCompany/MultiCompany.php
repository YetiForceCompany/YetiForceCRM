<?php
/**
 * MultiCompany CRMEntity Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class MultiCompany extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_multicompany';
	public $table_index = 'multicompanyid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_multicompanycf', 'multicompanyid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_multicompany', 'u_yf_multicompanycf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_multicompany' => 'multicompanyid',
		'u_yf_multicompanycf' => 'multicompanyid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_COMPANY_NAME' => 'company_name',
		'FL_STATUS' => 'mulcomp_status',
		'FL_EMAIL_1' => 'email1',
		'FL_PHONE' => 'phone',
		'FL_VATID' => 'vat',
		'AddressLevel5' => 'addresslevel5a',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'FL_COMPANY_NAME' => ['multicompany', 'company_name'],
		'FL_STATUS' => ['multicompany', 'mulcomp_status'],
		'FL_EMAIL_1' => ['multicompany', 'email1'],
		'FL_PHONE' => ['multicompany', 'phone'],
		'FL_VATID' => ['multicompany', 'vat'],
		'AddressLevel5' => ['multicompany', 'addresslevel5a'],
		'Assigned To' => ['crmentity', 'smownerid'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['company_name'];
	// For Alphabetical search
	public $def_basicsearch_col = 'company_name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'company_name';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['company_name', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Function to get sales hierarchy of the given Sale.
	 *
	 * @param int   $id
	 *                          returns hierarchy in array format
	 * @param mixed $getRawData
	 * @param mixed $getLinks
	 * @YTTODO to rebuild
	 */
	public function getHierarchy($id, $getRawData = false, $getLinks = true)
	{
		$listviewHeader = [];
		$listviewEntries = [];
		$listColumns = App\Config::module('MultiCompany', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('MultiCompany', $colname)) {
				$listviewHeader[] = App\Language::translate($fieldname, 'MultiCompany');
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
	 * @param int   $id              - Id of the record highest in hierarchy
	 * @param array $baseInfo
	 * @param int   $recordId        - id
	 * @param array $listviewEntries
	 *                               returns All the parent sales of the given Sale in array format
	 * @param mixed $getRawData
	 * @param mixed $getLinks
	 * @YTTODO to rebuild
	 */
	public function getHierarchyData($id, $baseInfo, $recordId, &$listviewEntries, $getRawData = false, $getLinks = true)
	{
		\App\Log::trace('Entering getHierarchyData(' . $id . ',' . $recordId . ') method ...');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$hasRecordViewAccess = $currentUser->isAdminUser() || \App\Privilege::isPermitted('MultiCompany', 'DetailView', $recordId);
		$listColumns = App\Config::module('MultiCompany', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		$infoData = [];
		foreach ($listColumns as $colname) {
			// Permission to view sales is restricted, avoid showing field values (except sales name)
			if (\App\Field::getFieldPermission('MultiCompany', $colname)) {
				$data = \App\Purifier::encodeHtml($baseInfo[$colname]);
				if (false === $getRawData && 'subject' === $colname) {
					if ($recordId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=MultiCompany&action=DetailView&record=' . $recordId . '">' . $data . '</a>';
							} else {
								$data = '<span>' . $data . '&nbsp;<span class="fas fa-exclamation-circle"></span></span>';
							}
						}
					} else {
						$data = '<strong>' . $data . '</strong>';
					}
					// - to show the hierarchy of the Sales
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
	 * Function to Recursively get all the upper sales of a given.
	 *
	 * @param int   $id          - multicompanyid
	 * @param array $parent      - Array of all the parent sales
	 *                           returns All the parent  f the given multicompanyid in array format
	 * @param mixed $encountered
	 * @param mixed $depthBase
	 * @YTTODO to rebuild
	 */
	public function getParent($id, &$parent, &$encountered, $depthBase = 0)
	{
		\App\Log::trace('Entering getParent(' . $id . ') method ...');
		if ($depthBase == App\Config::module('MultiCompany', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParent method ... - exceeded maximum depth of hierarchy');

			return $parent;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select([
			'u_#__multicompany.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('u_#__multicompany')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__multicompany.multicompanyid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__multicompany.multicompanyid' => $id])
			->one();
		if ($row) {
			$parentid = $row['parent_id'];
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
			$listColumns = App\Config::module('MultiCompany', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->list_fields_name;
			}
			foreach ($listColumns as $columnname) {
				if ('assigned_user_id' === $columnname) {
					$parentInfo[$columnname] = $row['user_name'];
				} elseif ('mulcomp_status' === $columnname) {
					$parentInfo[$columnname] = \App\Language::translate($row[$columnname], 'MultiCompany');
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
	 * Function to Recursively get all the child sales of a given Sale.
	 *
	 * @param int   $id        - multicompanyid
	 * @param array $childRow  - Array of all the child sales
	 * @param int   $depthBase - Depth at which the particular sales has to be placed in the hierarchy
	 *                         returns All the child sales of the given multicompanyid in array format
	 * @YTTODO to rebuild
	 */
	public function getChild($id, &$childRow, $depthBase)
	{
		\App\Log::trace('Entering getChild(' . $id . ',' . $depthBase . ') method ...');
		if (empty($id) || $depthBase == App\Config::module('MultiCompany', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChild method ... - exceeded maximum depth of hierarchy');

			return $childRow;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())->select([
			'u_#__multicompany.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('u_#__multicompany')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__multicompany.multicompanyid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__multicompany.parent_id' => $id])
			->createCommand()->query();
		$listColumns = App\Config::module('MultiCompany', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childAccId = $row['multicompanyid'];
				$childSalesProcessesInfo = [];
				$childSalesProcessesInfo['depth'] = $depth;
				foreach ($listColumns as $columnname) {
					if ('assigned_user_id' === $columnname) {
						$childSalesProcessesInfo[$columnname] = $row['user_name'];
					} elseif ('mulcomp_status' === $columnname) {
						$childSalesProcessesInfo[$columnname] = \App\Language::translate($row[$columnname], 'MultiCompany');
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
