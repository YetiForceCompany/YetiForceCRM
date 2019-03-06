<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

/**
 * Class Competition.
 */
class Competition extends Vtiger_CRMEntity
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table_name = 'u_yf_competition';

	/**
	 * Table index.
	 *
	 * @var string
	 */
	public $table_index = 'competitionid';

	/**
	 * Mandatory table for supporting custom fields.
	 *
	 * @var array
	 */
	public $customFieldTable = ['u_yf_competitioncf', 'competitionid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 *
	 * @var array
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_competition', 'u_yf_competitioncf', 'u_yf_competition_address', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 *
	 * @var array
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_competition' => 'competitionid',
		'u_yf_competitioncf' => 'competitionid',
		'u_yf_competition_address' => 'competitionaddressid',
		'vtiger_entity_stats' => 'crmid',
	];

	/**
	 * Mandatory for Listing (Related listview).
	 *
	 * @var array
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['competition', 'subject'],
		'Assigned To' => ['crmentity', 'smownerid'],
	];

	/**
	 * List fields name.
	 *
	 * @var array
	 */
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * List of fields in the RelationListView.
	 *
	 * @var string[]
	 */
	public $relationFields = ['subject', 'assigned_user_id'];

	/**
	 * Make the field link to detail view.
	 *
	 * @var string
	 */
	public $list_link_field = 'subject';

	/**
	 * For Popup listview and UI type support.
	 *
	 * @var array
	 */
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['competition', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];

	/**
	 * Search fields name.
	 *
	 * @var array
	 */
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * For Popup window record selection.
	 *
	 * @var array
	 */
	public $popup_fields = ['subject'];

	/**
	 * For Alphabetical search.
	 *
	 * @var string
	 */
	public $def_basicsearch_col = 'subject';

	/**
	 * Column value to use on detail view record text display.
	 *
	 * @var string
	 */
	public $def_detailview_recname = 'subject';

	/**
	 * Used when enabling/disabling the mandatory fields for the module.
	 * Refers to vtiger_field.fieldname values.
	 *
	 * @var array
	 */
	public $mandatory_fields = ['subject', 'assigned_user_id'];

	/**
	 * Default order by.
	 *
	 * @var string
	 */
	public $default_order_by = '';

	/**
	 * Default sort order.
	 *
	 * @var string
	 */
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
			\App\Db::getInstance()->update('vtiger_tab', ['customized' => 0], ['name' => 'Competition'])->execute();

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Competition']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId('Competition'));
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string $module            This module name
	 * @param array  $transferEntityIds List of Entity Id's from which related records need to be transfered
	 * @param int    $entityId          Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		\App\Log::trace("Entering function transferRelatedRecords (${module}, ${transferEntityIds}, ${entityId})");
		$relTableArr = ['Campaigns' => 'vtiger_campaign_records'];
		$tblFieldArr = ['vtiger_campaign_records' => 'campaignid'];
		$entityTblFieldArr = ['vtiger_campaign_records' => 'crmid'];
		foreach ($transferEntityIds as $transferId) {
			foreach ($relTableArr as $relTable) {
				$idField = $tblFieldArr[$relTable];
				$entityIdField = $entityTblFieldArr[$relTable];
				// IN clause to avoid duplicate entries
				$subQuery = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $entityId]);
				$query = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $transferId])->andWhere(['not in', $idField, $subQuery]);
				$dataReader = $query->createCommand()->query();
				while ($idFieldValue = $dataReader->readColumn(0)) {
					$dbCommand->update($relTable, [$entityIdField => $entityId], [$entityIdField => $transferId, $idField => $idFieldValue])->execute();
				}
				$dataReader->close();
			}
		}
		\App\Log::trace('Exiting transferRelatedRecords...');
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
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'u_yf_competition' => 'competitionid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'u_yf_competition' => 'competitionid'],
		];
		if (false === $secModule) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	/**
	 * Function to unlink an entity with given Id from another entity.
	 *
	 * @param it     $id
	 * @param string $returnModule
	 * @param int    $returnId
	 * @param string $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId)) {
			return;
		}
		if ('Campaigns' === $returnModule) {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $returnId])->execute();
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	/**
	 * Save related module.
	 *
	 * @param string    $module
	 * @param int       $crmid
	 * @param string    $withModule
	 * @param array|int $withCrmids
	 * @param string    $relatedName
	 */
	public function saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids)) {
			$withCrmids = [$withCrmids];
		}
		foreach ($withCrmids as $withCrmid) {
			if ('Campaigns' === $withModule) {
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $withCrmid,
					'crmid' => $crmid,
					'campaignrelstatusid' => 0,
				])->execute();
			} else {
				parent::saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName);
			}
		}
	}

	/**
	 * Function to get competition hierarchy.
	 *
	 * @param int  $id
	 * @param bool $getRawData
	 * @param bool $getLinks
	 *
	 * @return array
	 */
	public function getHierarchy($id, $getRawData = false, $getLinks = true)
	{
		$listviewHeader = [];
		$listviewEntries = [];
		$listColumns = AppConfig::module('Competition', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('Competition', $colname)) {
				$listviewHeader[] = App\Language::translate($fieldname, 'Competition');
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
	 * Function to create array of all the competition in the hierarchy.
	 *
	 * @param int   $id
	 * @param array $baseInfo
	 * @param int   $recordId        - id
	 * @param array $listviewEntries
	 * @param bool  $getRawData
	 * @param bool  $getLinks
	 *
	 * @return array
	 */
	public function getHierarchyData($id, $baseInfo, $recordId, &$listviewEntries, $getRawData = false, $getLinks = true)
	{
		\App\Log::trace('Entering getHierarchyData(' . $id . ',' . $recordId . ') method ...');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$hasRecordViewAccess = $currentUser->isAdminUser() || \App\Privilege::isPermitted('Competition', 'DetailView', $recordId);
		$listColumns = AppConfig::module('Competition', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		$infoData = [];
		foreach ($listColumns as $colname) {
			if (\App\Field::getFieldPermission('Competition', $colname)) {
				$data = \App\Purifier::encodeHtml($baseInfo[$colname]);
				if (false === $getRawData && 'subject' === $colname) {
					if ($recordId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=Competition&action=DetailView&record=' . $recordId . '">' . $data . '</a>';
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
			}
			$infoData[] = $data;
		}
		$listviewEntries[$recordId] = $infoData;
		foreach ($baseInfo as $accId => $rowInfo) {
			if (is_array($rowInfo) && (int) $accId) {
				$listviewEntries = $this->getHierarchyData($id, $rowInfo, $accId, $listviewEntries, $getRawData, $getLinks);
			}
		}
		\App\Log::trace('Exiting getHierarchyData method ...');

		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the parent.
	 *
	 * @param int   $id
	 * @param array $parent
	 * @param array $encountered
	 * @param int   $depthBase
	 *
	 * @return array
	 */
	public function getParent(int $id, array &$parent, array &$encountered, int $depthBase = 0)
	{
		\App\Log::trace('Entering getParent(' . $id . ') method ...');
		if ($depthBase == AppConfig::module('Competition', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParent method ... - exceeded maximum depth of hierarchy');

			return $parent;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select([
			'u_#__competition.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name not like '') THEN ${userNameSql} ELSE vtiger_groups.groupname END as user_name"),
		])->from('u_#__competition')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__competition.competitionid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__competition.competitionid' => $id])
			->one();
		if ($row) {
			$parentid = $row['parent_id'];
			if ('' !== $parentid && 0 != $parentid && !in_array($parentid, $encountered)) {
				$encountered[] = $parentid;
				$this->getParent($parentid, $parent, $encountered, $depthBase + 1);
			}
			$parentInfo = [];
			$depth = 0;
			if (isset($parent[$parentid])) {
				$depth = $parent[$parentid]['depth'] + 1;
			}
			$parentInfo['depth'] = $depth;
			$listColumns = AppConfig::module('Competition', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->list_fields_name;
			}
			foreach ($listColumns as $columnname) {
				if ('assigned_user_id' === $columnname) {
					$parentInfo[$columnname] = $row['user_name'];
				} elseif ('competition_status' === $columnname) {
					$parentInfo[$columnname] = \App\Language::translate($row[$columnname], 'Competition');
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
	 * Function to Recursively get all the child.
	 *
	 * @param int   $id
	 * @param array $childRow
	 * @param int   $depthBase
	 *
	 * @return array
	 */
	public function getChild(int $id, array &$childRow, int $depthBase)
	{
		\App\Log::trace('Entering getChild(' . $id . ',' . $depthBase . ') method ...');
		if (empty($id) || $depthBase == AppConfig::module('Competition', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChild method ... - exceeded maximum depth of hierarchy');

			return $childRow;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())->select([
			'u_#__competition.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name NOT LIKE '') THEN ${userNameSql} ELSE vtiger_groups.groupname END as user_name"),
		])->from('u_#__competition')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__competition.competitionid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'u_#__competition.parent_id' => $id])
			->createCommand()->query();
		$listColumns = AppConfig::module('Competition', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childId = $row['competitionid'];
				$childCompetitionProcessesInfo = [];
				$childCompetitionProcessesInfo['depth'] = $depth;
				foreach ($listColumns as $columnname) {
					if ('assigned_user_id' === $columnname) {
						$childCompetitionProcessesInfo[$columnname] = $row['user_name'];
					} elseif ('competition_status' === $columnname) {
						$childCompetitionProcessesInfo[$columnname] = \App\Language::translate($row[$columnname], 'Competition');
					} else {
						$childCompetitionProcessesInfo[$columnname] = $row[$columnname];
					}
				}
				$childRow[$childId] = $childCompetitionProcessesInfo;
				$this->getChild($childId, $childRow[$childId], $depth);
			}
			$dataReader->close();
		}
		\App\Log::trace('Exiting getChild method ...');

		return $childRow;
	}
}
