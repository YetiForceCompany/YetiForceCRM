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

class HelpDesk_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, App\QueryGenerator $queryGenerator)
	{
		if (\in_array($sourceModule, ['Assets', 'Project', 'ServiceContracts', 'Services'])) {
			$queryGenerator->addNativeCondition([
				'and',
				['not in', 'vtiger_troubletickets.ticketid', (new App\Db\Query())->select(['relcrmid'])->from('vtiger_crmentityrel')->where(['crmid' => $record])],
				['not in', 'vtiger_troubletickets.ticketid', (new App\Db\Query())->select(['crmid'])->from('vtiger_crmentityrel')->where(['relcrmid' => $record])],
			]);
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
		if ('Active' === \App\Record::getState($id)) {
			$listColumns = \App\Config::module('HelpDesk', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->getEntityInstance()->list_fields_name;
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
		}
		return ['header' => $listviewHeader, 'entries' => $listviewEntries];
	}

	/**
	 * Function to return hierarchy ids of given parent record.
	 *
	 * @param int    $id
	 * @param string $selectedRecords
	 *
	 * @return array
	 */
	public function getHierarchyIds(int $id, string $selectedRecords = 'all'): array
	{
		$rows = $recordIds = [];
		$encountered = [$id];
		if ('all' === $selectedRecords) {
			$id = key($this->getParent($id, $rows, $encountered));
			$recordIds[] = $id;
		}
		$recordIds = array_merge($recordIds, $this->getChildIds($id));
		return $recordIds ?? [];
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
			$listColumns = $this->getEntityInstance()->list_fields_name;
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
			if (\is_array($rowInfo) && (int) $accId) {
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
			$listColumns = \App\Config::module('HelpDesk', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->getEntityInstance()->list_fields_name;
			}
			foreach ($listColumns as $columnname) {
				if ('assigned_user_id' === $columnname) {
					$parentInfo[$columnname] = $row['user_name'];
				} elseif ('ticket_title' === $columnname) {
					$parentInfo[$columnname] = $row['title'];
				} elseif ('ticketstatus' === $columnname) {
					$parentInfo[$columnname] = App\Language::translate($row['status'], 'HelpDesk');
				} elseif ('ticketpriorities' === $columnname) {
					$parentInfo[$columnname] = App\Language::translate($row['priority'], 'HelpDesk');
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
			$listColumns = $this->getEntityInstance()->list_fields_name;
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
					} elseif ('ticket_title' === $columnname) {
						$childSalesProcessesInfo[$columnname] = $row['title'];
					} elseif ('ticketstatus' === $columnname) {
						$childSalesProcessesInfo[$columnname] = App\Language::translate($row['status'], 'HelpDesk');
					} elseif ('ticketpriorities' === $columnname) {
						$childSalesProcessesInfo[$columnname] = App\Language::translate($row['priority'], 'HelpDesk');
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

	/**
	 * Function to Recursively get all the child records id of a given record.
	 *
	 * @param int   $id
	 * @param array $childRow
	 * @param int   $depthBase
	 * @param array $childIds
	 *
	 * @return array
	 */
	public function getChildIds(int $id, &$childIds = []): array
	{
		$recordsIds = (new App\Db\Query())->select([
			'vtiger_troubletickets.ticketid',
		])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_troubletickets.ticketid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['and', ['vtiger_crmentity.deleted' => 0], ['vtiger_troubletickets.parentid' => $id], ['not', ['vtiger_troubletickets.ticketid' => $id]]])
			->column();
		if (!empty($recordsIds)) {
			if (\is_array($recordsIds)) {
				foreach ($recordsIds as $recordId) {
					$childIds[] = $recordId;
					$this->getChildIds($recordId, $childIds);
				}
			} else {
				$childIds[] = $recordsIds;
				$this->getChildIds($recordsIds, $childIds);
			}
		}
		return \is_array($childIds) ? $childIds : [$childIds];
	}

	/**
	 * Function to mass update status for given parent record.
	 *
	 * @param int    $recordId
	 * @param string $recordsType
	 * @param string $status
	 *
	 * @return bool
	 */
	public function massUpdateStatus(int $recordId, string $recordsType, string $status): bool
	{
		foreach ($this->getHierarchyIds($recordId, $recordsType) as $recordId) {
			if (\App\Privilege::isPermitted('HelpDesk', 'EditView', $recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'HelpDesk');
				$recordModel->set('ticketstatus', $status);
				$recordModel->save();
			}
		}
		return true;
	}
}
