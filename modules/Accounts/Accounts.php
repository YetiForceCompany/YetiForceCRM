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
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'Account Name' => ['vtiger_account' => 'accountname'],
		'Assigned To' => ['vtiger_crmentity' => 'smownerid'],
		'FL_STATUS' => ['vtiger_account' => 'accounts_status'],
		'Type' => ['vtiger_account' => 'accounttype'],
		'Vat ID' => ['vtiger_account' => 'vat_id'],
	];
	public $list_fields_name = [
		'Account Name' => 'accountname',
		'Assigned To' => 'assigned_user_id',
		'FL_STATUS' => 'accounts_status',
		'Type' => 'accounttype',
		'Vat ID' => 'vat_id',
	];
	public $list_link_field = 'accountname';
	public $search_fields = [
		'Account Name' => ['vtiger_account' => 'accountname'],
		'Assigned To' => ['vtiger_crmentity' => 'smownerid'],
		'FL_STATUS' => ['vtiger_account' => 'accounts_status'],
		'Type' => ['vtiger_account' => 'accounttype'],
		'Vat ID' => ['vtiger_account' => 'vat_id'],
	];
	public $search_fields_name = [
		'Account Name' => 'accountname',
		'Assigned To' => 'assigned_user_id',
		'FL_STATUS' => 'accounts_status',
		'Type' => 'accounttype',
		'Vat ID' => 'vat_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['accountname', 'website', 'phone', 'assigned_user_id'];
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
		if ($secModule === false) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	/**
	 * Function to get Account hierarchy of the given Account.
	 *
	 * @param int $id - accountid
	 *                returns Account hierarchy in array format
	 */
	public function getAccountHierarchy($id, $listColumns = false)
	{
		\App\Log::trace('Entering getAccountHierarchy(' . $id . ') method ...');

		$listview_header = [];
		$listview_entries = [];

		$listColumns = $listColumns ? $listColumns : AppConfig::module('Accounts', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}

		$hierarchyFields = [];
		foreach ($listColumns as $fieldLabel => $fieldName) {
			if (\App\Field::getFieldPermission('Accounts', $fieldName)) {
				$listview_header[] = $fieldLabel;
			}
			$field = \App\Field::getFieldInfo($fieldName, 'Accounts');
			$hierarchyFields[] = $field;
		}
		$this->hierarchyFields = $hierarchyFields;
		$accountsList = [];

		// Get the accounts hierarchy from the top most account in the hierarch of the current account, including the current account
		$encountered_accounts = [$id];
		$accountsList = $this->__getParentAccounts($id, $accountsList, $encountered_accounts);

		$baseId = current(array_keys($accountsList));
		$accountsList = [$baseId => $accountsList[$baseId]];

		// Get the accounts hierarchy (list of child accounts) based on the current account
		$accountsList[$baseId] = $this->__getChildAccounts($baseId, $accountsList[$baseId], $accountsList[$baseId]['depth']);

		// Create array of all the accounts in the hierarchy
		$accountHierarchy = $this->getHierarchyData($id, $accountsList[$baseId], $baseId, $listview_entries);

		$accountHierarchy = ['header' => $listview_header, 'entries' => $listview_entries];
		\App\Log::trace('Exiting getAccountHierarchy method ...');

		return $accountHierarchy;
	}

	/**
	 * Function to create array of all the accounts in the hierarchy.
	 *
	 * @param int   $id              - Id of the record highest in hierarchy
	 * @param array $accountInfoBase
	 * @param int   $accountId       - accountid
	 * @param array $listviewEntries
	 *                               returns All the parent accounts of the given accountid in array format
	 */
	public function getHierarchyData($id, $accountInfoBase, $accountId, &$listviewEntries)
	{
		\App\Log::trace('Entering getHierarchyData(' . $id . ',' . $accountId . ') method ...');

		$hasRecordViewAccess = \App\Privilege::isPermitted('Accounts', 'DetailView', $accountId);
		foreach ($this->hierarchyFields as &$field) {
			$fieldName = $field['fieldname'];
			$rawData = '';
			// Permission to view account is restricted, avoid showing field values (except account name)
			if (\App\Field::getFieldPermission('Accounts', $fieldName)) {
				$data = \App\Purifier::encodeHtml($accountInfoBase[$fieldName]);
				if ($fieldName == 'accountname') {
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
					$account_depth = str_repeat(' .. ', $accountInfoBase['depth']);
					$data = $account_depth . $data;
				} elseif ($fieldName == 'assigned_user_id' || $fieldName == 'shownerid') {
				} else {
					$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($field['fieldid']);
					$rawData = $data;
					$data = $fieldModel->getDisplayValue($data);
				}
				$accountInfoData[] = ['data' => $data, 'fieldname' => $fieldName, 'rawData' => $rawData];
			}
		}
		$listviewEntries[$accountId] = $accountInfoData;
		if (is_array($accountInfoBase)) {
			foreach ($accountInfoBase as $accId => $accountInfo) {
				if (is_array($accountInfo) && (int) $accId) {
					$listviewEntries = $this->getHierarchyData($id, $accountInfo, $accId, $listviewEntries);
				}
			}
		}
		\App\Log::trace('Exiting getHierarchyData method ...');

		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the upper accounts of a given Account.
	 *
	 * @param int   $id              - accountid
	 * @param array $parent_accounts - Array of all the parent accounts
	 *                               returns All the parent accounts of the given accountid in array format
	 */
	public function __getParentAccounts($id, &$parent_accounts, &$encountered_accounts, $depthBase = 0)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Entering __getParentAccounts(' . $id . ') method ...');

		if ($depthBase == AppConfig::module('Accounts', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting __getParentAccounts method ... - exceeded maximum depth of hierarchy');

			return $parent_accounts;
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = 'SELECT vtiger_account.*, vtiger_accountaddress.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM vtiger_account' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid' .
			' INNER JOIN vtiger_accountaddress ON vtiger_account.accountid = vtiger_accountaddress.accountaddressid ' .
			' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and vtiger_account.accountid = ?';
		$res = $adb->pquery($query, [$id]);

		if ($adb->getRowCount($res) > 0) {
			$row = $adb->getRow($res);
			$parentid = $row['parentid'];
			if ($parentid != '' && $parentid != 0 && !in_array($parentid, $encountered_accounts)) {
				$encountered_accounts[] = $parentid;
				$this->__getParentAccounts($parentid, $parent_accounts, $encountered_accounts, $depthBase + 1);
			}
			$parent_account_info = [];
			$depth = 0;
			if (isset($parent_accounts[$parentid])) {
				$depth = $parent_accounts[$parentid]['depth'] + 1;
			}
			$parent_account_info['depth'] = $depth;
			foreach ($this->hierarchyFields as &$field) {
				$fieldName = $field['fieldname'];

				if ($fieldName == 'assigned_user_id') {
					$parent_account_info[$fieldName] = $row['user_name'];
				} elseif ($fieldName == 'shownerid') {
					$sharedOwners = Vtiger_SharedOwner_UIType::getSharedOwners($row['accountid']);
					if (!empty($sharedOwners)) {
						$sharedOwners = implode(',', array_map('\App\Fields\Owner::getLabel', $sharedOwners));
						$parent_account_info[$fieldName] = $sharedOwners;
					}
				} else {
					$parent_account_info[$fieldName] = $row[$field['columnname']];
				}
			}
			$parent_accounts[$id] = $parent_account_info;
		}
		\App\Log::trace('Exiting __getParentAccounts method ...');

		return $parent_accounts;
	}

	/**
	 * Function to Recursively get all the child accounts of a given Account.
	 *
	 * @param int   $id             - accountid
	 * @param array $child_accounts - Array of all the child accounts
	 * @param int   $depth          - Depth at which the particular account has to be placed in the hierarchy
	 *                              returns All the child accounts of the given accountid in array format
	 */
	public function __getChildAccounts($id, &$child_accounts, $depthBase)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Entering __getChildAccounts(' . $id . ',' . $depthBase . ') method ...');

		if (empty($id) || $depthBase == AppConfig::module('Accounts', 'MAX_HIERARCHY_DEPTH')) {
			\App\Log::error('Exiting __getChildAccounts method ... - exceeded maximum depth of hierarchy');

			return $child_accounts;
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = 'SELECT vtiger_account.*, vtiger_accountaddress.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM vtiger_account' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid' .
			' INNER JOIN vtiger_accountaddress ON vtiger_account.accountid = vtiger_accountaddress.accountaddressid ' .
			' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and parentid = ?';
		$res = $adb->pquery($query, [$id]);

		if ($adb->getRowCount($res) > 0) {
			$depth = $depthBase + 1;
			while ($row = $adb->getRow($res)) {
				$child_acc_id = $row['accountid'];
				$child_account_info = [];
				$child_account_info['depth'] = $depth;
				foreach ($this->hierarchyFields as &$field) {
					$fieldName = $field['fieldname'];
					if ($fieldName == 'assigned_user_id') {
						$child_account_info[$fieldName] = $row['user_name'];
					} elseif ($fieldName == 'shownerid') {
						$sharedOwners = Vtiger_SharedOwner_UIType::getSharedOwners($child_acc_id);
						if (!empty($sharedOwners)) {
							$sharedOwners = implode(',', array_map('\App\Fields\Owner::getLabel', $sharedOwners));
							$child_account_info[$fieldName] = $sharedOwners;
						}
					} else {
						$child_account_info[$fieldName] = $row[$field['columnname']];
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildAccounts($child_acc_id, $child_accounts[$child_acc_id], $depth);
			}
		}
		\App\Log::trace('Exiting __getChildAccounts method ...');

		return $child_accounts;
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		if (empty($return_module) || empty($return_id)) {
			return;
		}

		if ($return_module === 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $return_id])->execute();
		} elseif ($return_module === 'Products') {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['crmid' => $id, 'productid' => $return_id])->execute();
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function saveRelatedModule($module, $crmid, $with_module, $with_crmids, $relatedName = false)
	{
		if (!is_array($with_crmids)) {
			$with_crmids = [$with_crmids];
		}
		if (!in_array($with_module, ['Products', 'Campaigns'])) {
			parent::saveRelatedModule($module, $crmid, $with_module, $with_crmids, $relatedName);
		} else {
			foreach ($with_crmids as $with_crmid) {
				if ($with_module == 'Products') {
					App\Db::getInstance()->createCommand()->insert('vtiger_seproductsrel', [
						'crmid' => $crmid,
						'productid' => $with_crmid,
						'setype' => $module,
						'rel_created_user' => \App\User::getCurrentUserId(),
						'rel_created_time' => date('Y-m-d H:i:s'),
					])->execute();
				} elseif ($with_module == 'Campaigns') {
					$checkResult = (new \App\Db\Query())->from('vtiger_campaign_records')->where(['campaignid' => $with_crmid, 'crmid' => $crmid])->exists();
					if ($checkResult) {
						continue;
					}
					App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
						'campaignid' => $with_crmid,
						'crmid' => $crmid,
						'campaignrelstatusid' => 1,
					])->execute();
				}
			}
		}
	}

	// Function to get related contact ids for an account record

	public function getRelatedContactsIds($id = null)
	{
		if ($id === null) {
			$id = $this->id;
		}
		$query = (new \App\Db\Query())->select('contactid')->from('vtiger_contactdetails')
			->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
			->where(['vtiger_contactdetails.parentid' => $id, 'vtiger_crmentity.deleted' => 0]);
		$entityIds = $query->column();
		if (empty($entityIds)) {
			$entityIds = [];
		}

		return $entityIds;
	}
}
