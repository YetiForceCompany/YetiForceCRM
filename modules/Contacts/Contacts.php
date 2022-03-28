<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Contacts/Contacts.php,v 1.70 2005/04/27 11:21:49 rank Exp $
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ******************************************************************************
 * Contributor(s): YetiForce S.A.
 */

// Contact is used to store customer information.
class Contacts extends CRMEntity
{
	public $table_name = 'vtiger_contactdetails';
	public $table_index = 'contactid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_contactdetails', 'vtiger_contactaddress', 'vtiger_contactsubdetails', 'vtiger_contactscf', 'vtiger_customerdetails', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_contactdetails' => 'contactid', 'vtiger_contactaddress' => 'contactaddressid', 'vtiger_contactsubdetails' => 'contactsubscriptionid', 'vtiger_contactscf' => 'contactid', 'vtiger_customerdetails' => 'customerid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_contactscf', 'contactid'];
	public $column_fields = [];

	public $list_fields_name = [
		'First Name' => 'firstname',
		'Last Name' => 'lastname',
		'Member Of' => 'parent_id',
		'Email' => 'email',
		'Office Phone' => 'phone',
		'Assigned To' => 'assigned_user_id',
	];
	public $search_fields = [
		'First Name' => ['contactdetails' => 'firstname'],
		'Last Name' => ['contactdetails' => 'lastname'],
		'Member Of' => ['contactdetails' => 'parent_id'],
		'Assigned To' => ['crmentity' => 'smownerid'],
	];
	public $search_fields_name = [];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'lastname', 'createdtime', 'modifiedtime'];
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = ['firstname', 'lastname', 'title', 'email', 'department', 'phone', 'mobile'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'lastname';

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
			'Products' => ['vtiger_seproductsrel' => ['crmid', 'productid'], 'vtiger_contactdetails' => 'contactid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_contactdetails' => 'contactid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_contactdetails' => 'contactid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_contactdetails' => 'contactid'],
		];
		if (false === $secModule) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	/**
	 * Function to get contacts hierarchy.
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
		$listColumns = App\Config::module('Contacts', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		foreach ($listColumns as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('Contacts', $colname)) {
				$listviewHeader[] = App\Language::translate($fieldname, 'Contacts');
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
	 * Function to create array of all the contacts in the hierarchy.
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
		$hasRecordViewAccess = $currentUser->isAdminUser() || \App\Privilege::isPermitted('Contacts', 'DetailView', $recordId);
		$listColumns = App\Config::module('Contacts', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		$infoData = [];
		foreach ($listColumns as $colname) {
			if (\App\Field::getFieldPermission('Contacts', $colname)) {
				$data = \App\Purifier::encodeHtml($baseInfo[$colname]);
				if (false === $getRawData && 'firstname' === $colname) {
					if ($recordId != $id) {
						if ($getLinks) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=Contacts&action=DetailView&record=' . $recordId . '">' . $data . '</a>';
							} else {
								$data = '<span>' . $data . '&nbsp;<span class="fas fa-exclamation-circle"></span></span>';
							}
						}
					} else {
						$data = '<strong>' . $data . '</strong>';
					}
					$rowDepth = str_repeat(' .. ', $baseInfo['depth']);
					$data = $rowDepth . $data;
				} elseif ('parent_id' === $colname) {
					$data = \App\Record::getLabel($data);
				}
			}
			$infoData[] = $data;
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
		if ($depthBase == App\Config::module('Contacts', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getParent method ... - exceeded maximum depth of hierarchy');

			return $parent;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select([
			'vtiger_contactdetails.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name not like '') THEN {$userNameSql} ELSE vtiger_groups.groupname END as user_name"),
		])->from('vtiger_contactdetails')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_contactdetails.contactid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_contactdetails.contactid' => $id])
			->one();
		if ($row) {
			$parentid = $row['reportsto'];
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
			$listColumns = App\Config::module('Contacts', 'COLUMNS_IN_HIERARCHY');
			if (empty($listColumns)) {
				$listColumns = $this->list_fields_name;
			}
			foreach ($listColumns as $columnname) {
				if ('assigned_user_id' === $columnname) {
					$parentInfo[$columnname] = $row['user_name'];
				} elseif ('parent_id' === $columnname) {
					$parentInfo[$columnname] = $row['parentid'];
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
		if (empty($id) || $depthBase == App\Config::module('Contacts', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting getChild method ... - exceeded maximum depth of hierarchy');

			return $childRow;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())->select([
			'vtiger_contactdetails.*',
			new \yii\db\Expression("CASE when (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name"),
		])->from('vtiger_contactdetails')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_contactdetails.contactid')
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->leftJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_contactdetails.reportsto' => $id])
			->createCommand()->query();
		$listColumns = App\Config::module('Contacts', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childId = $row['contactid'];
				$childContactsInfo = [];
				$childContactsInfo['depth'] = $depth;
				foreach ($listColumns as $columnname) {
					if ('assigned_user_id' === $columnname) {
						$childContactsInfo[$columnname] = $row['user_name'];
					} elseif ('parent_id' === $columnname) {
						$childContactsInfo[$columnname] = $row['parentid'];
					} else {
						$childContactsInfo[$columnname] = $row[$columnname];
					}
				}
				$childRow[$childId] = $childContactsInfo;
				$this->getChild($childId, $childRow[$childId], $depth);
			}
			$dataReader->close();
		}
		\App\Log::trace('Exiting getChild method ...');

		return $childRow;
	}
}
