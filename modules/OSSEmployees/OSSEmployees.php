<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
include_once 'modules/Vtiger/CRMEntity.php';

class OSSEmployees extends Vtiger_CRMEntity
{

	var $table_name = 'vtiger_ossemployees';
	var $table_index = 'ossemployeesid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_ossemployeescf', 'ossemployeesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_ossemployees', 'vtiger_ossemployeescf', 'vtiger_entity_stats');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossemployees' => 'ossemployeesid',
		'vtiger_ossemployeescf' => 'ossemployeesid',
		'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'No.' => Array('ossemployees', 'ossemployees_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'No.' => 'ossemployees_no',
		'LBL_LASTNAME' => 'last_name',
		'LBL_NAME' => 'name',
		'LBL_BUSINESSPHONE' => 'business_phone',
		'Assigned To' => 'assigned_user_id',
	);
	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'assigned_user_id';
	// For Popup listview and UI type support
	var $search_fields = Array(
		'No.' => Array('ossemployees', 'ossemployees_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	var $search_fields_name = Array(
		'No.' => 'ossemployees_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);
	// For Popup window record selection
	var $popup_fields = Array('last_name');
	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	// For Alphabetical search
	var $def_basicsearch_col = 'last_name';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'last_name';
	// Required Information for enabling Import feature
	var $required_fields = Array('assigned_user_id' => 1);
	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'assigned_user_id');

	/**
	 * Function to get Employees hierarchy of the given Employees
	 * @param  integer   $id      - employeeid
	 * returns Employees hierarchy in array format
	 */
	public function getEmployeeHierarchy($id)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');

		$log->debug("Entering getEmployeeHierarchy(" . $id . ") method ...");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$listview_header = Array();
		$listview_entries = array();

		foreach ($this->list_fields_name as $fieldname => $colname) {
			if (getFieldVisibilityPermission('OSSEmployees', $current_user->id, $colname) == '0') {
				$listview_header[] = \includes\Language::translate($fieldname);
			}
		}

		$rows_list = Array();
		$encountered_accounts = array($id);
		$rows_list = $this->__getParentEmployees($id, $rows_list, $encountered_accounts);
		$rows_list = $this->__getChildEmployees($id, $rows_list, $rows_list[$id]['depth']);
		foreach ($rows_list as $employees_id => $account_info) {
			$account_info_data = array();

			$hasRecordViewAccess = (vtlib\Functions::userIsAdministrator($current_user)) || (isPermitted('OSSEmployees', 'DetailView', $employees_id) == 'yes');
			foreach ($this->list_fields_name as $fieldname => $colname) {
				if (!$hasRecordViewAccess && $colname != 'name') {
					$account_info_data[] = '';
				} else if (getFieldVisibilityPermission('OSSEmployees', $current_user->id, $colname) == '0') {
					$data = $account_info[$colname];
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
						$account_depth = str_repeat(" .. ", $account_info['depth'] * 2);
						$data = $account_depth . $data;
					} else if ($colname == 'parentid' || $colname == 'projectid' || $colname == 'ticketid' || $colname == 'relategid') {
						$data = '<a href="index.php?module=' . vtlib\Functions::getCRMRecordType($data) . '&action=DetailView&record=' . $data . '">' . vtlib\Functions::getCRMRecordLabel($data) . '</a>';
					}
					$account_info_data[] = $data;
				}
			}
			$listview_entries[$employees_id] = $account_info_data;
		}
		$hierarchy = array('header' => $listview_header, 'entries' => $listview_entries);
		$log->debug("Exiting getEmployeeHierarchy method ...");
		return $hierarchy;
	}

	public function __getParentEmployees($id, &$parent_accounts, &$encountered_accounts)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering __getParentEmployees(" . $id . "," . $parent_accounts . ") method ...");
		$query = "SELECT parentid FROM vtiger_ossemployees " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid" .
			" WHERE vtiger_crmentity.deleted = 0 and vtiger_ossemployees.ossemployeesid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		if ($adb->num_rows($res) > 0 &&
			$adb->query_result($res, 0, 'parentid') != '' && $adb->query_result($res, 0, 'parentid') != 0 &&
			!in_array($adb->query_result($res, 0, 'parentid'), $encountered_accounts)) {

			$parentid = $adb->query_result($res, 0, 'parentid');
			$encountered_accounts[] = $parentid;
			$this->__getParentEmployees($parentid, $parent_accounts, $encountered_accounts);
		}
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_ossemployees.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			" FROM vtiger_ossemployees" .
			" INNER JOIN vtiger_crmentity " .
			" ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid" .
			" LEFT JOIN vtiger_groups" .
			" ON vtiger_groups.groupid = vtiger_crmentity.smownerid" .
			" LEFT JOIN vtiger_users" .
			" ON vtiger_users.id = vtiger_crmentity.smownerid" .
			" WHERE vtiger_crmentity.deleted = 0 and vtiger_ossemployees.ossemployeesid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		$parent_account_info = array();
		$depth = 0;
		$immediate_parentid = $adb->query_result($res, 0, 'parentid');
		if (isset($parent_accounts[$immediate_parentid])) {
			$depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
		}
		$parent_account_info['depth'] = $depth;
		foreach ($this->list_fields_name as $fieldname => $columnname) {
			if ($columnname == 'assigned_user_id') {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, 'user_name');
			} else {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, $columnname);
			}
		}
		$parent_accounts[$id] = $parent_account_info;
		$log->debug("Exiting __getParentEmployees method ...");
		return $parent_accounts;
	}

	public function __getChildEmployees($id, &$child_accounts, $depth)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering __getChildEmployees(" . $id . "," . $child_accounts . "," . $depth . ") method ...");
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_ossemployees.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			" FROM vtiger_ossemployees" .
			" INNER JOIN vtiger_crmentity " .
			" ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid" .
			" LEFT JOIN vtiger_groups" .
			" ON vtiger_groups.groupid = vtiger_crmentity.smownerid" .
			" LEFT JOIN vtiger_users" .
			" ON vtiger_users.id = vtiger_crmentity.smownerid" .
			" WHERE vtiger_crmentity.deleted = 0 and parentid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		$num_rows = $adb->num_rows($res);
		if ($num_rows > 0) {
			$depth = $depth + 1;
			for ($i = 0; $i < $num_rows; $i++) {
				$child_acc_id = $adb->query_result($res, $i, 'ossemployeesid');
				if (array_key_exists($child_acc_id, $child_accounts)) {
					continue;
				}
				$child_account_info = array();
				$child_account_info['depth'] = $depth;
				foreach ($this->list_fields_name as $fieldname => $columnname) {
					if ($columnname == 'assigned_user_id') {
						$child_account_info[$columnname] = $adb->query_result($res, $i, 'user_name');
					} else {
						$child_account_info[$columnname] = $adb->query_result($res, $i, $columnname);
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildEmployees($child_acc_id, $child_accounts, $depth);
			}
		}
		$log->debug("Exiting __getChildEmployees method ...");
		return $child_accounts;
	}
	/* function addWidgetTo($moduleNames, $widgetType='DETAILVIEWWIDGET', $widgetName='DetailViewBlockEMPLOYEEHOLIDAY') {
	  if (empty($moduleNames)) return;

	  if (is_string($moduleNames)) $moduleNames = array($moduleNames);

	  foreach($moduleNames as $moduleName) {
	  $module = vtlib\Module::getInstance($moduleName);
	  if($module) {
	  $module->addLink($widgetType, $widgetName, "modules/OSSEmployees/OSSEmployeesHoliday.php");
	  }
	  }
	  }
	 */

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function get_osstimecontrol($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_osstimecontrol(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);



		$record = Vtiger_Record_Model::getInstanceById($id);
		$userId = $record->get('assigned_user_id');



		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id', 'readwrite') == '0') {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name, vtiger_users.id,
				vtiger_osstimecontrol.name, vtiger_osstimecontrol.osstimecontrolid as crmid, vtiger_osstimecontrol.osstimecontrol_status, vtiger_osstimecontrol.payment,
				vtiger_osstimecontrol.osstimecontrol_no, vtiger_osstimecontrol.date_start, vtiger_osstimecontrol.due_date, vtiger_osstimecontrol.time_end, vtiger_osstimecontrol.sum_time, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime
				FROM vtiger_osstimecontrol
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_osstimecontrol.osstimecontrolid
				LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE  vtiger_crmentity.deleted = 0 && vtiger_crmentity.`smownerid`= " . $userId;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		$log->debug("Exiting get_osstimecontrol method ...");
		return $return_value;
	}

	public function vtlib_handler($modulename, $event_type)
	{
		$adb = PearDatabase::getInstance();
		if ($event_type == 'module.postinstall') {
			//block with fields in summary
			$tabid = \includes\Modules::getModuleId($modulename);
			$adb->query("UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `tabid` = $tabid && `columnname` IN ('ossemployees_no','employee_status','name','last_name','pesel','id_card','employee_education','parentid','business_mail');", true);

			\includes\fields\RecordNumber::setNumber($modulename, 'P', '1');
			// block with comments
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('OSSEmployees'));
			}
		} else if ($event_type == 'module.disabled') {

		} else if ($event_type == 'module.enabled') {

		} else if ($event_type == 'module.preuninstall') {

		} else if ($event_type == 'module.preupdate') {

		} else if ($event_type == 'module.postupdate') {
			
		}
	}
}
