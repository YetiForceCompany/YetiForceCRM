<?php

/**
 * 
 * @package YetiForce.ServiceContracts
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts extends CRMEntity
{

	public $table_name = 'vtiger_servicecontracts';
	public $table_index = 'servicecontractsid';
	public $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_servicecontractscf', 'servicecontractsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_servicecontracts', 'vtiger_servicecontractscf', 'vtiger_entity_stats');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_servicecontracts' => 'servicecontractsid',
		'vtiger_servicecontractscf' => 'servicecontractsid',
		'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Subject' => Array('servicecontracts', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Contract No' => Array('servicecontracts', 'contract_no'),
		'Used Units' => Array('servicecontracts', 'used_units'),
		'Total Units' => Array('servicecontracts', 'total_units')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
		'Contract No' => 'contract_no',
		'Used Units' => 'used_units',
		'Total Units' => 'total_units'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'assigned_user_id', 'contract_no', 'used_units', 'total_units'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Subject' => Array('servicecontracts', 'subject'),
		'Contract No' => Array('servicecontracts', 'contract_no'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
		'Used Units' => Array('servicecontracts', 'used_units'),
		'Total Units' => Array('servicecontracts', 'total_units')
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Subject' => 'subject',
		'Contract No' => 'contract_no',
		'Assigned To' => 'assigned_user_id',
		'Used Units' => 'used_units',
		'Total Units' => 'total_units'
	);
	// For Popup window record selection
	public $popup_fields = Array('subject');
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Required Information for enabling Import feature
	public $required_fields = Array('assigned_user_id' => 1);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('subject', 'assigned_user_id');
	// Callback function list during Importing
	public $special_functions = Array('set_import_assigned_user');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Get list view query.
	 */
	public function getListQuery($module, $where = '')
	{
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

		// Select Custom Field Table Columns if present
		if (!empty($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
			" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
			" WHERE uitype='10' && vtiger_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for ($i = 0; $i < $linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index =" .
				"$this->table_name.$columnname";
		}

		$current_user = vglobal('current_user');
		$query .= $this->getNonAdminAccessControlQuery($module, $current_user);
		$query .= sprintf('WHERE vtiger_crmentity.deleted = 0 %s', $where);
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	public function getListViewSecurityParameter($module)
	{
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		$sec_query = '';
		$tabid = \App\Module::getModuleId($module);

		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {

			$sec_query .= " && (vtiger_crmentity.smownerid in($current_user->id) || vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . " && tabid=" . $tabid . "
					)
					OR
						(";

			// Build the query based on the group association of current user.
			if (sizeof($current_user_groups) > 0) {
				$sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") || ";
			}
			$sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
						)";
			$sec_query .= ")
				)";
		}
		return $sec_query;
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{

		$matrix = $queryplanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityServiceContracts', array('vtiger_groupsServiceContracts', 'vtiger_usersServiceContracts'));
		$matrix->setDependency('vtiger_servicecontracts', array('vtiger_servicecontractscf', 'vtiger_crmentityServiceContracts'));
		if (!$queryplanner->requireTable('vtiger_servicecontracts', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_servicecontracts", "servicecontractsid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityServiceContracts", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityServiceContracts on vtiger_crmentityServiceContracts.crmid = vtiger_servicecontracts.servicecontractsid  and vtiger_crmentityServiceContracts.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_servicecontractscf")) {
			$query .= " left join vtiger_servicecontractscf on vtiger_servicecontractscf.servicecontractsid = vtiger_servicecontracts.servicecontractsid";
		}
		if ($queryplanner->requireTable("vtiger_groupsServiceContracts")) {
			$query .= " left join vtiger_groups as vtiger_groupsServiceContracts on vtiger_groupsServiceContracts.groupid = vtiger_crmentityServiceContracts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersServiceContracts")) {
			$query .= " left join vtiger_users as vtiger_usersServiceContracts on vtiger_usersServiceContracts.id = vtiger_crmentityServiceContracts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_contactdetailsRelServiceContracts")) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsRelServiceContracts on vtiger_contactdetailsRelServiceContracts.contactid = vtiger_servicecontracts.sc_related_to";
		}
		if ($queryplanner->requireTable("vtiger_accountRelServiceContracts")) {
			$query .= " left join vtiger_account as vtiger_accountRelServiceContracts on vtiger_accountRelServiceContracts.accountid = vtiger_servicecontracts.sc_related_to";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByServiceContracts")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByServiceContracts on vtiger_lastModifiedByServiceContracts.id = vtiger_crmentityServiceContracts.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyServiceContracts")) {
			$query .= " left join vtiger_users as vtiger_createdbyServiceContracts on vtiger_createdbyServiceContracts.id = vtiger_crmentityServiceContracts.smcreatorid ";
		}
		return $query;
	}

	/**
	 * Create query to export the records.
	 */
	public function create_export_query($where)
	{
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('ServiceContracts', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and " .
			"vtiger_users.status='Active'";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
			" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
			" WHERE uitype='10' && vtiger_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for ($i = 0; $i < $linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = " .
				"$this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule, $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != '')
			$query .= " WHERE ($where) && $where_auto";
		else
			$query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	public function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
	{
		$select_clause = sprintf("SELECT %s.%s AS recordid, vtiger_users_last_import.deleted,%s", $this->table_name, $this->table_index, $table_cols);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " INNER JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}

		$query = $select_clause . $from_clause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . get_on_clause($field_values, $ui_type_arr, $module) .
			$where_clause .
			" ORDER BY $table_cols," . $this->table_name . "." . $this->table_index . " ASC";

		return $query;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{

		require_once('include/utils/utils.php');
		$adb = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {
			$moduleInstance = vtlib\Module::getInstance($moduleName);

			$accModuleInstance = vtlib\Module::getInstance('Accounts');
			$accModuleInstance->setRelatedList($moduleInstance, 'Service Contracts', array('add'), 'getDependentsList');

			$conModuleInstance = vtlib\Module::getInstance('Contacts');
			$conModuleInstance->setRelatedList($moduleInstance, 'Service Contracts', array('add'), 'getDependentsList');

			$helpDeskInstance = vtlib\Module::getInstance("HelpDesk");
			$helpDeskInstance->setRelatedList($moduleInstance, "Service Contracts", Array('ADD', 'SELECT'));

			// Initialize module sequence for the module
			\App\Fields\RecordNumber::setNumber($moduleName, 'SERCON', 1);
			// Make the picklist value 'Complete' for status as non-editable
			$adb->query("UPDATE vtiger_contract_status SET presence=0 WHERE contract_status='Complete'");

			// Mark the module as Standard module
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($moduleName));
		} else if ($eventType == 'module.disabled') {
			App\EventHandler::setInActive('ServiceContracts_ServiceContractsHandler_Handler');
		} else if ($eventType == 'module.enabled') {
			App\EventHandler::setActive('ServiceContracts_ServiceContractsHandler_Handler');
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	public function save_related_module($module, $crmid, $with_module, $with_crmids, $relatedName = false)
	{
		if (!is_array($with_crmids))
			$with_crmids = Array($with_crmids);
		foreach ($with_crmids as $with_crmid) {
			if ($with_module == 'HelpDesk') {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
				$this->updateHelpDeskRelatedTo($crmid, $with_crmid);
				$this->updateServiceContractState($crmid);
			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid, $relatedName);
			}
		}
	}

	// Function to Update the parent_id of HelpDesk with sc_related_to of ServiceContracts if the parent_id is not set.
	public function updateHelpDeskRelatedTo($focusId, $entityIds)
	{

		if (!is_array($entityIds))
			$entityIds = array($entityIds);
		$selectTicketsQuery = sprintf('SELECT ticketid FROM vtiger_troubletickets
								WHERE (parent_id IS NULL || parent_id = 0)
									AND ticketid IN (%s)', generateQuestionMarks($entityIds));
		$selectTicketsResult = $this->db->pquery($selectTicketsQuery, [$entityIds]);
		$noOfTickets = $this->db->num_rows($selectTicketsResult);
		for ($i = 0; $i < $noOfTickets; ++$i) {
			$ticketId = $this->db->query_result($selectTicketsResult, $i, 'ticketid');
			$serviceContractsRelateToTypeResult = $this->db->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid =
				(SELECT sc_related_to FROM vtiger_servicecontracts WHERE servicecontractsid = ?)', array($focusId));
			$serviceContractsRelateToType = $this->db->query_result($serviceContractsRelateToTypeResult, 0, 'setype');
			if ($serviceContractsRelateToType == 'Accounts') {
				$updateQuery = "UPDATE vtiger_troubletickets, vtiger_servicecontracts SET parent_id=vtiger_servicecontracts.sc_related_to" .
					" WHERE vtiger_servicecontracts.sc_related_to IS NOT NULL && vtiger_servicecontracts.sc_related_to != 0" .
					" && vtiger_servicecontracts.servicecontractsid = ? && vtiger_troubletickets.ticketid = ?";
				$this->db->pquery($updateQuery, array($focusId, $ticketId));
			} elseif ($serviceContractsRelateToType == 'Contacts') {
				$updateQuery = "UPDATE vtiger_troubletickets, vtiger_servicecontracts SET contact_id=vtiger_servicecontracts.sc_related_to" .
					" WHERE vtiger_servicecontracts.sc_related_to IS NOT NULL && vtiger_servicecontracts.sc_related_to != 0" .
					" && vtiger_servicecontracts.servicecontractsid = ? && vtiger_troubletickets.ticketid = ?";
				$this->db->pquery($updateQuery, array($focusId, $ticketId));
			}
		}
	}

	// Function to Compute and Update the Used Units and Progress of the Service Contract based on all the related Trouble tickets.
	public function updateServiceContractState($focusId)
	{
		$this->id = $focusId;
		$this->retrieve_entity_info($focusId, 'ServiceContracts');

		$contractTicketsResult = $this->db->pquery("SELECT relcrmid FROM vtiger_crmentityrel
														WHERE module = 'ServiceContracts'
														AND relmodule = 'HelpDesk' AND crmid = ?
													UNION
														SELECT crmid FROM vtiger_crmentityrel
														WHERE relmodule = 'ServiceContracts'
														AND module = 'HelpDesk' AND relcrmid = ?", array($focusId, $focusId));

		$noOfTickets = $this->db->num_rows($contractTicketsResult);
		$ticketFocus = CRMEntity::getInstance('HelpDesk');
		$totalUsedUnits = 0;
		for ($i = 0; $i < $noOfTickets; ++$i) {
			$ticketId = $this->db->query_result($contractTicketsResult, $i, 'relcrmid');
			$ticketFocus->id = $ticketId;
			if (isRecordExists($ticketId)) {
				$ticketFocus->retrieve_entity_info($ticketId, 'HelpDesk');
				if (strtolower($ticketFocus->column_fields['ticketstatus']) == 'closed') {
					$totalUsedUnits += $this->computeUsedUnits($ticketFocus->column_fields);
				}
			}
		}
		$this->updateUsedUnits($totalUsedUnits);

		$this->calculateProgress();
	}

	// Function to Upate the Used Units of the Service Contract based on the given Ticket id.
	public function computeUsedUnits($ticketData, $operator = '+')
	{
		$trackingUnit = strtolower($this->column_fields['tracking_unit']);
		$workingHoursPerDay = 24;

		$usedUnits = 0;
		if ($trackingUnit == 'incidents') {
			$usedUnits = 1;
		} elseif ($trackingUnit == 'days') {
			if (!empty($ticketData['days'])) {
				$usedUnits = $ticketData['days'];
			} elseif (!empty($ticketData['hours'])) {
				$usedUnits = $ticketData['hours'] / $workingHoursPerDay;
			}
		} elseif ($trackingUnit == 'hours') {
			if (!empty($ticketData['hours'])) {
				$usedUnits = $ticketData['hours'];
			} elseif (!empty($ticketData['days'])) {
				$usedUnits = $ticketData['days'] * $workingHoursPerDay;
			}
		}
		return $usedUnits;
	}

	/**
	 * Function to Upate the Used Units of the Service Contract.
	 * @param float $usedUnits
	 */
	public function updateUsedUnits($usedUnits)
	{
		$this->column_fields['used_units'] = $usedUnits;
		\App\Db::getInstance()->createCommand()->update($this->table_name, ['used_units' => $usedUnits], ['servicecontractsid' => $this->id])->execute();
	}

	/**
	 * Function to Calculate the End Date, Planned Duration, Actual Duration and Progress of a Service Contract
	 */
	public function calculateProgress()
	{
		$db = \App\Db::getInstance();
		$params = [];

		$startDate = $this->column_fields['start_date'];
		$dueDate = $this->column_fields['due_date'];
		$endDate = $this->column_fields['end_date'];

		$usedUnits = \vtlib\Functions::formatDecimal($this->column_fields['used_units']);
		$totalUnits = \vtlib\Functions::formatDecimal($this->column_fields['total_units']);

		$contractStatus = $this->column_fields['contract_status'];

		// Update the End date if the status is Complete or if the Used Units reaches/exceeds Total Units
		// We need to do this first to make sure Actual duration is computed properly
		if ($contractStatus === 'Complete' || (!empty($usedUnits) && !empty($totalUnits) && $usedUnits >= $totalUnits)) {
			if (empty($endDate)) {
				$endDate = date('Y-m-d');
				$db->createCommand()->update($this->table_name, ['end_date' => $endDate], ['servicecontractsid' => $this->id])->execute();
			}
		} else {
			$endDate = null;
			$db->createCommand()->update($this->table_name, ['end_date' => $endDate], ['servicecontractsid' => $this->id])->execute();
		}

		// Calculate the Planned Duration based on Due date and Start date. (in days)
		if (!empty($dueDate) && !empty($startDate)) {
			$start = new \DateTime($startDate);
			$end = new \DateTime($dueDate);
			$interval = $start->diff($end)->format('%a');
			$params['planned_duration'] = $interval;
		} else {
			$params['planned_duration'] = '';
		}

		// Calculate the Actual Duration based on End date and Start date. (in days)
		if (!empty($endDate) && !empty($startDate)) {
			$start = new \DateTime($startDate);
			$end = new \DateTime($endDate);
			$interval = $start->diff($end)->format('%a');
			$params['actual_duration'] = $interval;
		} else {
			$params['actual_duration'] = '';
		}
		// Update the Progress based on Used Units and Total Units (in percentage)
		if (!empty($usedUnits) && !empty($totalUnits)) {
			$params['progress'] = floatval(($usedUnits * 100) / $totalUnits);
		} else {
			$params['progress'] = null;
		}
		$db->createCommand()->update($this->table_name, $params, ['servicecontractsid' => $this->id])->execute();
	}

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	public function delete_related_module($module, $crmid, $with_module, $with_crmid)
	{
		parent::delete_related_module($module, $crmid, $with_module, $with_crmid);
		if ($with_module == 'HelpDesk') {
			$this->updateServiceContractState($crmid);
		}
	}

	/** Function to unlink an entity with given Id from another entity */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		global $currentModule;
		if ($relatedName == 'getManyToMany') {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		} else {
			parent::deleteRelatedFromDB(vglobal('currentModule'), $id, $returnModule, $returnId);
			$dataReader = (new \App\Db\Query())->select(['tabid', 'tablename', 'columnname'])
					->from('vtiger_field')
					->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $currentModule, 'relmodule' => $returnModule])])
					->createCommand()->query();
			while ($row = $dataReader->read()) {
				App\Db::getInstance()->createCommand()
					->update($row['tablename'], [$row['columnname'] => null], [$row['columnname'] => $returnId, CRMEntity::getInstance(App\Module::getModuleName($row['tabid']))->table_index => $id])
					->execute();
			}
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("Documents" => "vtiger_senotesrel", "Attachments" => "vtiger_seattachmentsrel");

		$tbl_field_arr = Array("vtiger_senotesrel" => "notesid", "vtiger_seattachmentsrel" => "attachmentsid");

		$entity_tbl_field_arr = Array("vtiger_senotesrel" => "crmid", "vtiger_seattachmentsrel" => "crmid");

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", array($transferId, $entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; $i++) {
						$id_field_value = $adb->query_result($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", array($entityId, $transferId, $id_field_value));
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace("Exiting transferRelatedRecords...");
	}
}
