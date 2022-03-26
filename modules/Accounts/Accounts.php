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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Accounts.php,v 1.53 2005/04/28 08:06:45 rank Exp $
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */
// Contributor(s): YetiForce S.A.

class Accounts extends CRMEntity
{
	public $table_name = 'vtiger_account';
	public $table_index = 'accountid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_account', 'vtiger_accountaddress', 'vtiger_accountscf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_account' => 'accountid', 'vtiger_accountaddress' => 'accountaddressid', 'vtiger_accountscf' => 'accountid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_accountscf', 'accountid'];
	public $entity_table = 'vtiger_crmentity';
	public $column_fields = [];

	public $list_fields_name = [
		'Account Name' => 'accountname',
		'Assigned To' => 'assigned_user_id',
		'FL_STATUS' => 'accounts_status',
		'Type' => 'accounttype',
		'Vat ID' => 'vat_id',
	];
	public $search_fields = [
		'Account Name' => ['vtiger_account' => 'accountname'],
		'Assigned To' => ['vtiger_crmentity' => 'smownerid'],
		'FL_STATUS' => ['vtiger_account' => 'accounts_status'],
		'Type' => ['vtiger_account' => 'accounttype'],
		'Vat ID' => ['vtiger_account' => 'vat_id'],
	];
	public $search_fields_name = [];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'accountname'];
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = ['accountname', 'account_type', 'industry', 'annualrevenue', 'phone', 'email1', 'rating', 'website', 'fax'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'accountname';

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
			'Contacts' => ['vtiger_contactdetails' => ['parentid', 'contactid'], 'vtiger_account' => 'accountid'],
			'HelpDesk' => ['vtiger_troubletickets' => ['parent_id', 'ticketid'], 'vtiger_account' => 'accountid'],
			'Products' => ['vtiger_seproductsrel' => ['crmid', 'productid'], 'vtiger_account' => 'accountid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_account' => 'accountid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_account' => 'accountid'],
			'Assets' => ['vtiger_assets' => ['parent_id', 'assetsid'], 'vtiger_account' => 'accountid'],
			'Project' => ['vtiger_project' => ['linktoaccountscontacts', 'projectid'], 'vtiger_account' => 'accountid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_account' => 'accountid'],
		];
		if (false === $secModule) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	/**
	 * Function to get Account hierarchy of the given Account.
	 *
	 * @param int   $id          - accountid
	 *                           returns Account hierarchy in array format
	 * @param mixed $listColumns
	 */
	public function getAccountHierarchy($id, $listColumns = false)
	{
		\App\Log::trace('Entering getAccountHierarchy(' . $id . ') method ...');

		$listViewHeader = [];
		$listViewEntries = [];
		$listColumns = $listColumns ?: App\Config::module('Accounts', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}

		$hierarchyFields = [];
		foreach ($listColumns as $fieldLabel => $fieldName) {
			if (\App\Field::getFieldPermission('Accounts', $fieldName)) {
				$listViewHeader[] = $fieldLabel;
			}
			$field = \App\Field::getFieldInfo($fieldName, 'Accounts');
			$hierarchyFields[] = $field;
		}
		$this->hierarchyFields = $hierarchyFields;
		$accountsList = [];

		// Get the accounts hierarchy from the top most account in the hierarch of the current account, including the current account
		$encounteredAccounts = [$id];
		$accountsList = $this->__getParentAccounts($id, $accountsList, $encounteredAccounts);
		$baseId = current(array_keys($accountsList));
		$accountsList = [$baseId => $accountsList[$baseId] ?? []];
		// Get the accounts hierarchy (list of child accounts) based on the current account
		$accountsList[$baseId] = $this->__getChildAccounts($baseId, $accountsList[$baseId], $accountsList[$baseId]['depth']);
		$this->getHierarchyData($id, $accountsList[$baseId], $baseId, $listViewEntries);
		\App\Log::trace('Exiting getAccountHierarchy method ...');
		return ['header' => $listViewHeader, 'entries' => $listViewEntries];
	}

	/**
	 * Function to create array of all the accounts in the hierarchy.
	 *
	 * @param int   $id              - Id of the record highest in hierarchy
	 * @param array $accountInfoBase
	 * @param int   $accountId       - accountid
	 * @param array $listViewEntries
	 *                               returns All the parent accounts of the given accountid in array format
	 */
	public function getHierarchyData($id, $accountInfoBase, $accountId, &$listViewEntries)
	{
		\App\Log::trace('Entering getHierarchyData(' . $id . ',' . $accountId . ') method ...');
		$hasRecordViewAccess = \App\Privilege::isPermitted('Accounts', 'DetailView', $accountId);
		foreach ($this->hierarchyFields as &$field) {
			$fieldName = $field['fieldname'];
			$rawData = '';
			// Permission to view account is restricted, avoid showing field values (except account name)
			if (\App\Field::getFieldPermission('Accounts', $fieldName)) {
				$data = \App\Purifier::encodeHtml($accountInfoBase[$fieldName]);
				if ('accountname' == $fieldName) {
					if ($accountId != $id) {
						if ($hasRecordViewAccess) {
							$data = '<a href="index.php?module=Accounts&view=Detail&record=' . $accountId . '">' . $data . '</a>';
						} else {
							$data = '<span>' . $data . '&nbsp;<span class="fas fa-exclamation-circle"></span></span>';
						}
					} else {
						$data = '<strong>' . $data . '</strong>';
					}
					// - to show the hierarchy of the Accounts
					$accountDepth = str_repeat(' .. ', $accountInfoBase['depth']);
					$data = $accountDepth . $data;
				} elseif ('assigned_user_id' !== $fieldName && 'shownerid' !== $fieldName) {
					$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($field['fieldid']);
					$rawData = $data;
					$data = $fieldModel->getDisplayValue($data);
				}
				$accountInfoData[] = ['data' => $data, 'fieldname' => $fieldName, 'rawData' => $rawData];
			}
		}
		$listViewEntries[$accountId] = $accountInfoData;
		if (\is_array($accountInfoBase)) {
			foreach ($accountInfoBase as $accId => $accountInfo) {
				if (\is_array($accountInfo) && (int) $accId) {
					$listViewEntries = $this->getHierarchyData($id, $accountInfo, $accId, $listViewEntries);
				}
			}
		}
		\App\Log::trace('Exiting getHierarchyData method ...');

		return $listViewEntries;
	}

	/**
	 * Function to Recursively get all the upper accounts of a given Account.
	 *
	 * @param int   $id                  - accountid
	 * @param array $parentAccounts      - Array of all the parent accounts
	 *                                   returns All the parent accounts of the given accountid in array format
	 * @param mixed $encounteredAccounts
	 * @param mixed $depthBase
	 */
	public function __getParentAccounts($id, &$parentAccounts, &$encounteredAccounts, $depthBase = 0)
	{
		\App\Log::trace('Entering __getParentAccounts(' . $id . ') method ...');
		if ($depthBase == App\Config::module('Accounts', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting __getParentAccounts method ... - exceeded maximum depth of hierarchy');
			return $parentAccounts;
		}
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$row = (new App\Db\Query())->select(['vtiger_account.*', 'vtiger_accountaddress.*', 'user_name' => new \yii\db\Expression('CASE when (vtiger_users.user_name not like ' . App\Db::getInstance()->quoteValue('') . ") THEN $userNameSql ELSE vtiger_groups.groupname END")])
			->from('vtiger_account')
			->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_accountaddress', 'vtiger_accountaddress.accountaddressid = vtiger_account.accountid')
			->leftJoin('vtiger_groups', 'vtiger_crmentity.smownerid = vtiger_groups.groupid')
			->leftJoin('vtiger_users', 'vtiger_crmentity.smownerid = vtiger_users.id')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_account.accountid' => $id])->one();
		if ($row) {
			$parentId = $row['parentid'];
			if ('' != $parentId && 0 != $parentId && !\in_array($parentId, $encounteredAccounts)) {
				$encounteredAccounts[] = $parentId;
				$this->__getParentAccounts($parentId, $parentAccounts, $encounteredAccounts, $depthBase + 1);
			}
			$parentAccountInfo = [];
			$depth = 0;
			if (isset($parentAccounts[$parentId])) {
				$depth = $parentAccounts[$parentId]['depth'] + 1;
			}
			$parentAccountInfo['depth'] = $depth;
			foreach ($this->hierarchyFields as &$field) {
				$fieldName = $field['fieldname'];

				if ('assigned_user_id' == $fieldName) {
					$parentAccountInfo[$fieldName] = $row['user_name'];
				} elseif ('shownerid' == $fieldName) {
					$sharedOwners = \App\Fields\SharedOwner::getById($row['accountid']);
					if (!empty($sharedOwners)) {
						$sharedOwners = implode(',', array_map('\App\Fields\Owner::getLabel', $sharedOwners));
						$parentAccountInfo[$fieldName] = $sharedOwners;
					}
				} else {
					$parentAccountInfo[$fieldName] = $row[$field['columnname']];
				}
			}
			$parentAccounts[$id] = $parentAccountInfo;
		}
		\App\Log::trace('Exiting __getParentAccounts method ...');
		return $parentAccounts;
	}

	/**
	 * Function to Recursively get all the child accounts of a given Account.
	 *
	 * @param int   $id            - accountid
	 * @param array $childAccounts - Array of all the child accounts
	 * @param int   $depth         - Depth at which the particular account has to be placed in the hierarchy
	 *                             returns All the child accounts of the given accountid in array format
	 * @param mixed $depthBase
	 */
	public function __getChildAccounts($id, &$childAccounts, $depthBase)
	{
		\App\Log::trace('Entering __getChildAccounts(' . $id . ',' . $depthBase . ') method ...');
		if (empty($id) || $depthBase == App\Config::module('Accounts', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting __getChildAccounts method ... - exceeded maximum depth of hierarchy');
			return $childAccounts;
		}

		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new App\Db\Query())
			->select(['vtiger_account.*', 'vtiger_accountaddress.*', 'user_name' => new \yii\db\Expression('CASE when (vtiger_users.user_name not like ' . App\Db::getInstance()->quoteValue('') . ") THEN $userNameSql ELSE vtiger_groups.groupname END")])
			->from('vtiger_account')
			->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_accountaddress', 'vtiger_account.accountid = vtiger_accountaddress.accountaddressid')
			->leftJoin('vtiger_groups', 'vtiger_crmentity.smownerid = vtiger_groups.groupid')
			->leftJoin('vtiger_users', 'vtiger_crmentity.smownerid = vtiger_users.id')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_account.parentid' => $id])->createCommand()->query();
		if ($dataReader->count() > 0) {
			$depth = $depthBase + 1;
			while ($row = $dataReader->read()) {
				$childAccId = $row['accountid'];
				$childAccountInfo = [];
				$childAccountInfo['depth'] = $depth;
				foreach ($this->hierarchyFields as &$field) {
					$fieldName = $field['fieldname'];
					if ('assigned_user_id' == $fieldName) {
						$childAccountInfo[$fieldName] = $row['user_name'];
					} elseif ('shownerid' == $fieldName) {
						$sharedOwners = \App\Fields\SharedOwner::getById($childAccId);
						if (!empty($sharedOwners)) {
							$sharedOwners = implode(',', array_map('\App\Fields\Owner::getLabel', $sharedOwners));
							$childAccountInfo[$fieldName] = $sharedOwners;
						}
					} else {
						$childAccountInfo[$fieldName] = $row[$field['columnname']];
					}
				}
				$childAccounts[$childAccId] = $childAccountInfo;
				$this->__getChildAccounts($childAccId, $childAccounts[$childAccId], $depth);
			}
		}
		\App\Log::trace('Exiting __getChildAccounts method ...');
		return $childAccounts;
	}
}
