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

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Project Name' => ['project', 'projectname'],
		'Start Date' => ['project', 'startdate'],
		'Status' => ['project', 'projectstatus'],
		'Type' => ['project', 'projecttype'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Total time [Sum]' => ['project', 'sum_time_all'],
	];
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
	public $relationFields = ['projectname', 'startdate', 'projectstatus', 'projecttype', 'assigned_user_id', 'sum_time'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'projectname';
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
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'Project Name' => 'projectname',
		'Related to' => 'linktoaccountscontacts',
		'Start Date' => 'startdate',
		'Status' => 'projectstatus',
		'Type' => 'projecttype',
		'SINGLE_SSalesProcesses' => 'ssalesprocessesid',
	];
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
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
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
		} elseif ($eventType === 'module.postupdate') {
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
	 * Function to unlink an entity with given Id from another entity.
	 *
	 * @param int    $id
	 * @param string $returnModule
	 * @param int    $returnId
	 * @param bool   $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if ($relatedName === 'getManyToMany') {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		} else {
			parent::deleteRelatedFromDB($id, $returnModule, $returnId);
			$dataReader = (new \App\Db\Query())->select(['tabid', 'tablename', 'columnname'])
				->from('vtiger_field')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $this->moduleName, 'relmodule' => $returnModule])])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				App\Db::getInstance()->createCommand()
					->update($row['tablename'], [$row['columnname'] => null], [$row['columnname'] => $returnId, CRMEntity::getInstance(App\Module::getModuleName($row['tabid']))->table_index => $id])
					->execute();
			}
			$dataReader->close();
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
		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$relTableArr = ['ProjectTask' => 'vtiger_projecttask', 'ProjectMilestone' => 'vtiger_projectmilestone',
			'Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel', ];

		$tblFieldArr = ['vtiger_projecttask' => 'projecttaskid', 'vtiger_projectmilestone' => 'projectmilestoneid',
			'vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid', ];

		$entityTblFieldArr = ['vtiger_projecttask' => 'projectid', 'vtiger_projectmilestone' => 'projectid',
			'vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid', ];

		foreach ($transferEntityIds as $transferId) {
			foreach ($relTableArr as $relTable) {
				$idField = $tblFieldArr[$relTable];
				$entityIdField = $entityTblFieldArr[$relTable];
				// IN clause to avoid duplicate entries
				$subQuery = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $entityId]);
				$query = (new \App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $transferId])->andWhere(['not in', $idField, $subQuery]);
				$dataReader = $query->createCommand()->query();
				while ($idFieldValue = $dataReader->readColumn(0)) {
					\App\Db::getInstance()->createCommand()->update($relTable, [$entityIdField => $entityId], [$entityIdField => $transferId, $idField => $idFieldValue])->execute();
				}
				$dataReader->close();
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
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
				if ($getRawData === false && $colname === 'projectname') {
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
			if (is_array($rowInfo) && (int) $accId) {
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
			if ($parentid !== '' && $parentid != 0 && !in_array($parentid, $encountered)) {
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
				if ($columnname === 'assigned_user_id') {
					$parentInfo[$columnname] = $row['user_name'];
				} elseif ($columnname === 'projecttype') {
					$parentInfo[$columnname] = \App\Language::translate($row[$columnname], 'Project');
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
					if ($columnname === 'assigned_user_id') {
						$childSalesProcessesInfo[$columnname] = $row['user_name'];
					} elseif ($columnname === 'projecttype') {
						$childSalesProcessesInfo[$columnname] = \App\Language::translate($row[$columnname], 'Project');
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
