<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */

class HelpDesk extends CRMEntity
{
	public $table_name = 'vtiger_troubletickets';
	public $table_index = 'ticketid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_troubletickets', 'vtiger_ticketcf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_troubletickets' => 'ticketid', 'vtiger_ticketcf' => 'ticketid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ticketcf', 'ticketid'];
	public $column_fields = [];
	//Pavani: Assign value to entity_table
	public $entity_table = 'vtiger_crmentity';
	public $list_fields = [
		//Module Sequence Numbering
		//'Ticket ID'=>Array('crmentity'=>'crmid'),
		'Ticket No' => ['troubletickets' => 'ticket_no'],
		// END
		'Subject' => ['troubletickets' => 'title'],
		'Related To' => ['troubletickets' => 'parent_id'],
		'Contact Name' => ['troubletickets' => 'contact_id'],
		'Status' => ['troubletickets' => 'status'],
		'Priority' => ['troubletickets' => 'priority'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_TOTAL_TIME_H' => ['troubletickets', 'sum_time'],
	];
	public $list_fields_name = [
		'Ticket No' => 'ticket_no',
		'Subject' => 'title',
		'Related To' => 'parent_id',
		'Status' => 'status',
		'Priority' => 'priority',
		'Assigned To' => 'assigned_user_id',
		'FL_TOTAL_TIME_H' => 'sum_time',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['ticket_no', 'ticket_title', 'parent_id', 'ticketstatus', 'ticketpriorities', 'assigned_user_id', 'sum_time'];
	public $list_link_field = 'ticket_title';
	public $search_fields = [
		//'Ticket ID' => Array('vtiger_crmentity'=>'crmid'),
		'Ticket No' => ['vtiger_troubletickets' => 'ticket_no'],
		'Title' => ['vtiger_troubletickets' => 'title'],
	];
	public $search_fields_name = [
		'Ticket No' => 'ticket_no',
		'Title' => 'ticket_title',
	];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'update_log'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'ticket_title';

	public function saveRelatedModule($module, $crmid, $with_module, $with_crmid, $relatedName = false)
	{
		if ('ServiceContracts' == $with_module) {
			parent::saveRelatedModule($module, $crmid, $with_module, $with_crmid);
			$serviceContract = CRMEntity::getInstance('ServiceContracts');
			$serviceContract->updateHelpDeskRelatedTo($with_crmid, $crmid);
			$serviceContract->updateServiceContractState($with_crmid);
		} else {
			parent::saveRelatedModule($module, $crmid, $with_module, $with_crmid, $relatedName);
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string This module name
	 * @param array List of Entity Id's from which related records need to be transfered
	 * @param int Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		\App\Log::trace("Entering function transferRelatedRecords (${module}, ${transferEntityIds}, ${entityId})");
		$relTableArr = ['Attachments' => 'vtiger_seattachmentsrel', 'Documents' => 'vtiger_senotesrel'];
		$tblFieldArr = ['vtiger_seattachmentsrel' => 'attachmentsid', 'vtiger_senotesrel' => 'notesid'];
		$entityTblFieldArr = ['vtiger_seattachmentsrel' => 'crmid', 'vtiger_senotesrel' => 'crmid'];
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
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
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
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_troubletickets' => 'ticketid'],
			'Services' => ['vtiger_crmentityrel' => ['crmid', 'relcrmid'], 'vtiger_troubletickets' => 'ticketid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_troubletickets' => 'ticketid'],
		];
		if (false === $secModule) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId)) {
			return;
		}
		if ('Accounts' === $returnModule || 'Vendors' === $returnModule) {
			$dbCommand = App\Db::getInstance()->createCommand();
			$dbCommand->update('vtiger_troubletickets', ['parent_id' => null], ['ticketid' => $id])->execute();
			$dbCommand->delete('vtiger_seticketsrel', ['ticketid' => $id])->execute();
		} elseif ('Products' === $returnModule) {
			App\Db::getInstance()->createCommand()->update('vtiger_troubletickets', ['product_id' => null], ['ticketid' => $id])->execute();
		} elseif ('ServiceContracts' === $returnModule && 'getManyToMany' !== $relatedName) {
			parent::unlinkRelationship($id, $returnModule, $returnId);
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	/**
	 * Function to get records hierarchy in array format.
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
		$listColumns = \App\Config::module('HelpDesk', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('HelpDesk', $colname)) {
				$listviewHeader[] = App\Language::translate($fieldname, 'HelpDesk');
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
	 * Function to create array of all the records in the hierarchy.
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
		$hasRecordViewAccess = $currentUser->isAdminUser() || \App\Privilege::isPermitted('HelpDesk', 'DetailView', $recordId);
		$listColumns = \App\Config::module('HelpDesk', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		$infoData = [];
		foreach ($listColumns as $colname) {
			if (\App\Field::getFieldPermission('HelpDesk', $colname)) {
				$data = \App\Purifier::encodeHtml($baseInfo[$colname]);
				if (false === $getRawData && 'ticket_no' === $colname) {
					if ($recordId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=HelpDesk&action=DetailView&record=' . $recordId . '">' . $data . '</a>';
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
			if (is_array($rowInfo) && (int) $accId) {
				$listviewEntries = $this->getHierarchyData($id, $rowInfo, $accId, $listviewEntries, $getRawData, $getLinks);
			}
		}
		\App\Log::trace('Exiting getHierarchyData method ...');
		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the upper records of a given.
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
		if ($depthBase === \App\Config::module('HelpDesk', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParent method ... - exceeded maximum depth of hierarchy');
			return $parent;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select([
			'vtiger_troubletickets.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_troubletickets.ticketid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_troubletickets.ticketid' => $id])
			->one();
		if ($row) {
			$parentid = $row['parentid'];
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
			$listColumns = \App\Config::module('HelpDesk', 'COLUMNS_IN_HIERARCHY');
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
		\App\Log::trace('Exiting getParent method ...');
		return $parent;
	}

	/**
	 * Function to Recursively get all the child records of a given record.
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
		if (empty($id) || $depthBase === \App\Config::module('HelpDesk', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChild method ... - exceeded maximum depth of hierarchy');
			return $childRow;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())->select([
			'vtiger_troubletickets.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_troubletickets.ticketid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_troubletickets.parentid' => $id])
			->createCommand()->query();
		$listColumns = \App\Config::module('HelpDesk', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childAccId = $row['ticketid'];
				$childSalesProcessesInfo = [];
				$childSalesProcessesInfo['depth'] = $depth;
				foreach ($listColumns as $columnname) {
					if ('assigned_user_id' === $columnname) {
						$childSalesProcessesInfo[$columnname] = $row['user_name'];
					}else {
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
