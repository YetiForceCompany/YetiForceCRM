<?php
/**
 * Partners CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class Partners extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_partners';
	public $table_index = 'partnersid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_partnerscf', 'partnersid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_partners', 'u_yf_partnerscf', 'u_yf_partners_address', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_partners' => 'partnersid',
		'u_yf_partnerscf' => 'partnersid',
		'u_yf_partners_address' => 'partneraddressid',
		'vtiger_entity_stats' => 'crmid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['partners', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
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
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => 'Partners'])->execute();

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Partners']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
		}
	}

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param bool|string $secModule secondary module name
	 *
	 * @return array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'u_yf_partners' => 'partnersid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'u_yf_partners' => 'partnersid'],
		];
		if (false === $secModule) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	/**
	 * Function to get hierarchy.
	 *
	 * @param int   $id
	 * @param mixed $getRawData
	 * @param mixed $getLinks
	 *
	 * @return array hierarchy in array format
	 * @YTTODO to rebuild
	 */
	public function getHierarchy($id, $getRawData = false, $getLinks = true)
	{
		$listviewHeader = [];
		$listviewEntries = [];
		$moduleName = 'Partners';
		$listColumns = App\Config::module($moduleName, 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission($moduleName, $colname)) {
				$listviewHeader[] = App\Language::translate($fieldname, $moduleName);
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
	 * Function to create array of all the data in the hierarchy.
	 *
	 * @param int   $id              - Id of the record highest in hierarchy
	 * @param array $baseInfo
	 * @param int   $recordId        - id
	 * @param array $listviewEntries
	 * @param mixed $getRawData
	 * @param mixed $getLinks
	 *
	 * @return array
	 * @YTTODO to rebuild
	 */
	public function getHierarchyData($id, $baseInfo, $recordId, &$listviewEntries, $getRawData = false, $getLinks = true)
	{
		$moduleName = 'Partners';
		$currentUser = \App\User::getCurrentUserModel();
		$hasRecordViewAccess = $currentUser->isAdmin() || \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		$listColumns = App\Config::module($moduleName, 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		$infoData = [];
		foreach ($listColumns as $colname) {
			if (\App\Field::getFieldPermission($moduleName, $colname)) {
				$data = \App\Purifier::encodeHtml($baseInfo[$colname]);
				if (false === $getRawData && 'subject' === $colname) {
					if ($recordId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=' . $moduleName . '&action=DetailView&record=' . $recordId . '">' . $data . '</a>';
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

		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the upper records of a given.
	 *
	 * @param int   $id          - partnersid
	 * @param array $parent      - Array of all the parent records
	 *                           returns All the parent  f the given partnersid in array format
	 * @param mixed $encountered
	 * @param mixed $depthBase
	 * @YTTODO to rebuild
	 */
	public function getParent($id, &$parent, &$encountered, $depthBase = 0)
	{
		if ($depthBase == App\Config::module('Partners', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParent method ... - exceeded maximum depth of hierarchy');

			return $parent;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select([
			'u_#__partners.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('u_#__partners')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__partners.partnersid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__partners.partnersid' => $id])
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
			$listColumns = App\Config::module('Partners', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->list_fields_name;
			}
			foreach ($listColumns as $columnname) {
				if ('assigned_user_id' === $columnname) {
					$parentInfo[$columnname] = $row['user_name'];
				} else {
					$parentInfo[$columnname] = $row[$columnname];
				}
			}

			$parent[$id] = $parentInfo;
		}

		return $parent;
	}

	/**
	 * Function to Recursively get all the child entries of a given record.
	 *
	 * @param int   $id        - partnersid
	 * @param array $childRow  - Array of all the child records
	 * @param int   $depthBase - Depth at which the particular record has to be placed in the hierarchy
	 *                         returns All the child records of the given partnersid in array format
	 * @YTTODO to rebuild
	 */
	public function getChild($id, &$childRow, $depthBase)
	{
		if (empty($id) || $depthBase == App\Config::module('Partners', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChild method ... - exceeded maximum depth of hierarchy');

			return $childRow;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())->select([
			'u_#__partners.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('u_#__partners')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__partners.partnersid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__partners.parentid' => $id])
			->createCommand()->query();
		$listColumns = App\Config::module('Partners', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childAccId = $row['partnersid'];
				$childInfo = [];
				$childInfo['depth'] = $depth;
				foreach ($listColumns as $columnname) {
					if ('assigned_user_id' === $columnname) {
						$childInfo[$columnname] = $row['user_name'];
					} else {
						$childInfo[$columnname] = $row[$columnname];
					}
				}
				$childRow[$childAccId] = $childInfo;
				$this->getChild($childAccId, $childRow[$childAccId], $depth);
			}
			$dataReader->close();
		}

		return $childRow;
	}
}
