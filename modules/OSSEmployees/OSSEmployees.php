<?php
/**
 * OSSEmployees CRMEntity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class OSSEmployees extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_ossemployees';
	public $table_index = 'ossemployeesid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ossemployeescf', 'ossemployeesid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_ossemployees', 'vtiger_ossemployeescf', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossemployees' => 'ossemployeesid',
		'vtiger_ossemployeescf' => 'ossemployeesid',
		'vtiger_entity_stats' => 'crmid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_LASTNAME' => ['ossemployees', 'last_name'],
		'LBL_NAME' => ['ossemployees', 'name'],
		'LBL_BUSINESSPHONE' => ['ossemployees', 'business_phone'],
		'LBL_BUSINESSMAIL' => ['ossemployees', 'business_mail'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_POSITION' => ['crmentity', 'position'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_LASTNAME' => 'last_name',
		'LBL_NAME' => 'name',
		'LBL_BUSINESSPHONE' => 'business_phone',
		'LBL_BUSINESSMAIL' => 'business_mail',
		'Assigned To' => 'assigned_user_id',
		'FL_POSITION' => 'position',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['ossemployees_no', 'last_name', 'name', 'business_phone', 'assigned_user_id'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'assigned_user_id';
	// For Popup listview and UI type support
	public $search_fields = [
		'LBL_LASTNAME' => ['ossemployees', 'last_name'],
		'LBL_NAME' => ['ossemployees', 'name'],
		'LBL_BUSINESSPHONE' => ['ossemployees', 'business_phone'],
		'LBL_BUSINESSMAIL' => ['ossemployees', 'business_mail'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_POSITION' => ['crmentity', 'position'],
	];
	public $search_fields_name = [
		'LBL_LASTNAME' => 'last_name',
		'LBL_NAME' => 'name',
		'LBL_BUSINESSPHONE' => 'business_phone',
		'LBL_BUSINESSMAIL' => 'business_mail',
		'Assigned To' => 'assigned_user_id',
		'FL_POSITION' => 'position',
	];
	// For Popup window record selection
	public $popup_fields = ['last_name'];
	// For Alphabetical search
	public $def_basicsearch_col = 'last_name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'last_name';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'assigned_user_id'];

	/**
	 * Function to get Employees hierarchy of the given Employees.
	 *
	 * @param int $id - employeeid
	 *                returns Employees hierarchy in array format
	 */
	public function getEmployeeHierarchy($id)
	{
		\App\Log::trace('Entering getEmployeeHierarchy(' . $id . ') method ...');

		$listview_header = [];
		$listview_entries = [];

		foreach ($this->list_fields_name as $fieldname => $colname) {
			if (\App\Field::getFieldPermission('OSSEmployees', $colname)) {
				$listview_header[] = \App\Language::translate($fieldname);
			}
		}

		$rows_list = [];
		$encountered_accounts = [$id];
		$rows_list = $this->__getParentEmployees($id, $rows_list, $encountered_accounts);
		$rows_list = $this->__getChildEmployees($id, $rows_list, $rows_list[$id]['depth']);
		foreach ($rows_list as $employees_id => $account_info) {
			$account_info_data = [];

			$hasRecordViewAccess = \App\Privilege::isPermitted('OSSEmployees', 'DetailView', $employees_id);
			foreach ($this->list_fields_name as $fieldname => $colname) {
				if (!$hasRecordViewAccess && $colname != 'name') {
					$account_info_data[] = '';
				} elseif (\App\Field::getFieldPermission('OSSEmployees', $colname)) {
					$data = \App\Purifier::encodeHtml($account_info[$colname]);
					if ($colname == 'ossemployees_no') {
						if ($employees_id != $id) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=OSSEmployees&view=Detail&record=' . $employees_id . '">' . $data . '</a>';
							} else {
								$data = '<i>' . $data . '</i>';
							}
						} else {
							$data = '<b>' . $data . '</b>';
						}
						$account_depth = str_repeat(' .. ', $account_info['depth'] * 2);
						$data = $account_depth . $data;
					} elseif ($colname == 'parentid' || $colname == 'projectid' || $colname == 'ticketid' || $colname == 'relategid') {
						$data = '<a href="index.php?module=' . \App\Record::getType($data) . '&action=DetailView&record=' . $data . '">' . vtlib\Functions::getCRMRecordLabel($data) . '</a>';
					}
					$account_info_data[] = $data;
				}
			}
			$listview_entries[$employees_id] = $account_info_data;
		}
		$hierarchy = ['header' => $listview_header, 'entries' => $listview_entries];
		\App\Log::trace('Exiting getEmployeeHierarchy method ...');

		return $hierarchy;
	}

	public function __getParentEmployees($id, &$parent_accounts, &$encountered_accounts)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Entering __getParentEmployees(' . $id . ',' . $parent_accounts . ') method ...');
		$query = 'SELECT parentid FROM vtiger_ossemployees ' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid' .
			' WHERE vtiger_crmentity.deleted = 0 and vtiger_ossemployees.ossemployeesid = ?';
		$params = [$id];
		$res = $adb->pquery($query, $params);
		if ($adb->numRows($res) > 0 &&
			$adb->queryResult($res, 0, 'parentid') != '' && $adb->queryResult($res, 0, 'parentid') != 0 &&
			!in_array($adb->queryResult($res, 0, 'parentid'), $encountered_accounts)) {
			$parentid = $adb->queryResult($res, 0, 'parentid');
			$encountered_accounts[] = $parentid;
			$this->__getParentEmployees($parentid, $parent_accounts, $encountered_accounts);
		}
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = 'SELECT vtiger_ossemployees.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM vtiger_ossemployees' .
			' INNER JOIN vtiger_crmentity ' .
			' ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid' .
			' LEFT JOIN vtiger_groups' .
			' ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users' .
			' ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and vtiger_ossemployees.ossemployeesid = ?';
		$params = [$id];
		$res = $adb->pquery($query, $params);
		$parent_account_info = [];
		$depth = 0;
		$immediate_parentid = $adb->queryResult($res, 0, 'parentid');
		if (isset($parent_accounts[$immediate_parentid])) {
			$depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
		}
		$parent_account_info['depth'] = $depth;
		foreach ($this->list_fields_name as $fieldname => $columnname) {
			if ($columnname == 'assigned_user_id') {
				$parent_account_info[$columnname] = $adb->queryResult($res, 0, 'user_name');
			} else {
				$parent_account_info[$columnname] = $adb->queryResult($res, 0, $columnname);
			}
		}
		$parent_accounts[$id] = $parent_account_info;
		\App\Log::trace('Exiting __getParentEmployees method ...');

		return $parent_accounts;
	}

	public function __getChildEmployees($id, &$child_accounts, $depth)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Entering __getChildEmployees(' . $id . ',' . $child_accounts . ',' . $depth . ') method ...');
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = 'SELECT vtiger_ossemployees.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM vtiger_ossemployees' .
			' INNER JOIN vtiger_crmentity ' .
			' ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid' .
			' LEFT JOIN vtiger_groups' .
			' ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users' .
			' ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and parentid = ?';
		$params = [$id];
		$res = $adb->pquery($query, $params);
		$numRows = $adb->numRows($res);
		if ($numRows > 0) {
			$depth = $depth + 1;
			for ($i = 0; $i < $numRows; ++$i) {
				$child_acc_id = $adb->queryResult($res, $i, 'ossemployeesid');
				if (array_key_exists($child_acc_id, $child_accounts)) {
					continue;
				}
				$child_account_info = [];
				$child_account_info['depth'] = $depth;
				foreach ($this->list_fields_name as $fieldname => $columnname) {
					if ($columnname == 'assigned_user_id') {
						$child_account_info[$columnname] = $adb->queryResult($res, $i, 'user_name');
					} else {
						$child_account_info[$columnname] = $adb->queryResult($res, $i, $columnname);
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildEmployees($child_acc_id, $child_accounts, $depth);
			}
		}
		\App\Log::trace('Exiting __getChildEmployees method ...');

		return $child_accounts;
	}

	public function moduleHandler($modulename, $event_type)
	{
		if ($event_type == 'module.postinstall') {
			//block with fields in summary
			$tabid = \App\Module::getModuleId($modulename);
			\App\Db::getInstance()->createCommand()->update('vtiger_field', ['summaryfield' => 1], ['and', ['tabid' => $tabid],
				['columnname' => ['ossemployees_no', 'employee_status', 'name', 'last_name', 'pesel', 'id_card', 'employee_education', 'parentid', 'business_mail']], ])->execute();
			\App\Fields\RecordNumber::setNumber($modulename, 'P', '1');
			// block with comments
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['OSSEmployees']);
				}
			}
		}
	}
}
