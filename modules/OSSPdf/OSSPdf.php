<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 *************************************************************************************************************************************/
require_once('include/CRMEntity.php');
require_once('include/Tracker.php');

class OSSPdf extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity
	var $table_name = 'vtiger_osspdf';
	var $table_index= 'osspdfid';
	var $column_fields = Array();
	var $focus;
	
	var $id;

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_osspdfcf', 'osspdfid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_osspdf', 'vtiger_osspdfcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_osspdf'   => 'osspdfid',
	    'vtiger_osspdfcf' => 'osspdfid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_mod_name'=> Array('osspdf', 'title'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_mod_name'=> 'title',
		'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'title';

	// For Popup listview and UI type supporb
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_mod_name'=> Array('osspdf', 'title')
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_mod_name'=> 'title'
	);

	// For Popup window record selection
	var $popup_fields = Array('title');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'title';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'title';

	// Required Information for enabling Import feature
	var $required_fields = Array('title'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'title';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'title', 'osspdf_enable_footer', 'osspdf_enable_header', 'osspdf_pdf_orientation');
	
	function __construct() {
		$currentModule = vglobal('currentModule');
                $log = vglobal('log');
                $this->column_fields = getColumnFields($currentModule);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
		
	}

	function getSortOrder() {
		$currentModule = vglobal('currentModule');

		$sortorder = $this->default_sort_order;
		if($_REQUEST['sorder']) $sortorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else if($_SESSION[$currentModule.'_Sort_Order']) 
			$sortorder = $_SESSION[$currentModule.'_Sort_Order'];

		return $sortorder;
	}

	function getOrderBy() {
		$currentModule = vglobal('currentModule');
		
		$use_default_order_by = '';		
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}
		
		$orderby = $use_default_order_by;
		if($_REQUEST['order_by']) $orderby = $this->db->sql_escape_string($_REQUEST['order_by']);
		else if($_SESSION[$currentModule.'_Order_By'])
			$orderby = $_SESSION[$currentModule.'_Order_By'];
		return $orderby;
	}

	function save_module($module) {
	
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord, $query='') {
		// $srcrecord could be empty
	}

	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	function getListQuery($module, $usewhere='') {
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";
		
		// Keep track of tables joined to avoid duplicates
		$joinedTables = array();

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		$joinedTables[] = $this->table_name;
		$joinedTables[] = 'vtiger_crmentity';
		
		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
			$joinedTables[] = $this->customFieldTable[0]; 
		}
		$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$joinedTables[] = 'vtiger_users';
		$joinedTables[] = 'vtiger_groups';
		
		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($module), true);
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);
		
		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			$other =  CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			if(!in_array($other->table_name, $joinedTables)) {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
				$joinedTables[] = $other->table_name;
			}
		}
                
		$current_user = vglobal('current_user');
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE vtiger_crmentity.deleted = 0 ".$usewhere;
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	function getListViewSecurityParameter($module) {
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	
		$sec_query = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN 
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role 
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid 
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid 
						WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					) 
					OR vtiger_crmentity.smownerid IN 
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per 
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					) 
					OR 
						(";
		
					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$sec_query .= " vtiger_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$sec_query .= " vtiger_groups.groupid IN 
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid 
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		$current_user = vglobal('current_user');
		$thismodule = $_REQUEST['module'];
		
		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, "detail_view");
		
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name 
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index"; 
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";
		
		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thismodule), true);
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			
			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			
			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Initialize this instance for importing.
	 */
	function initImport($module) {
		$this->db = PearDatabase::getInstance();
		$this->initImportableFields($module);
	}

	/**
	 * Create list query to be shown at the last step of the import.
	 * Called From: modules/Import/UserLastImport.php
	 */
	function create_import_query($module) {
		$current_user = vglobal('current_user');
		$query = "SELECT vtiger_crmentity.crmid, case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name, $this->table_name.* FROM $this->table_name
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index
			LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_users_last_import.assigned_user_id='$current_user->id'
			AND vtiger_users_last_import.bean_type='$module'
			AND vtiger_users_last_import.deleted=0";
		return $query;
	}

	/**
	 * Delete the last imported records.
	 */
	function undo_import($module, $user_id) {
		$db = PearDatabase::getInstance();
		$count = 0;
		$query1 = "select bean_id from vtiger_users_last_import where assigned_user_id=? AND bean_type='$module' AND deleted=0";
		$result1 = $db->pquery($query1, array($user_id), true) or die("Error getting last import for undo: ".mysql_error()); 
		while ( $row1 = $db->fetchByAssoc($result1))
		{
			$query2 = "update vtiger_crmentity set deleted=1 where crmid=?";
			$result2 = $db->pquery($query2, array($row1['bean_id']), true) or die("Error undoing last import: ".mysql_error()); 
			$count++;			
		}
		return $count;
	}
	
	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will set the assigned user id for import record.
	 */
	function set_import_assigned_user()
	{
		$current_user = vglobal('current_user');
                $db = PearDatabase::getInstance();
		$record_user = $this->column_fields["assigned_user_id"];
		
		if($record_user != $current_user->id){
			$sqlresult = $db->pquery("select id from vtiger_users where id = ? union select groupid as id from vtiger_groups where groupid = ?", array($record_user, $record_user), true);
			if($this->db->num_rows($sqlresult)!= 1) {
				$this->column_fields["assigned_user_id"] = $current_user->id;
			} else {			
				$row = $db->fetchByAssoc($sqlresult, -1, false);
				if (isset($row['id']) && $row['id'] != -1) {
					$this->column_fields["assigned_user_id"] = $row['id'];
				} else {
					$this->column_fields["assigned_user_id"] = $current_user->id;
				}
			}
		}
	}
	
	/** 
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, vtiger_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index"; 
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		
		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);
					
		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";	
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}	
		
		
		$query = $select_clause . $from_clause .
					" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") AS temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";
					
		return $query;		
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		$db = PearDatabase::getInstance();
		if($event_type == 'module.postinstall') {
		//nadanie numeracji w module
		$modfocus = CRMEntity::getInstance($modulename);
		$modfocus->setModuleSeqNumber('configure', $modulename, $req_str= '', $req_no='1');	
		
		
		$sql = "INSERT INTO vtiger_osspdf_config (`conf_id`,`name`,`value`) VALUES ('GENERALCONFIGURATION','ifsave','no'),('GENERALCONFIGURATION','ifattach','no')";
		$wykonaj = $db->query( $sql, true );

	
        include_once( 'vtlib/Vtiger/Module.php' );
        $names = array('Emails','PBXManager','ModComments','SMSNotifier','OSSPdf');
        foreach( $names as $id ) {
			$in .= "'".$id."',";
		}
		$in = trim($in,',');
        $wynik = $db->query( "select tabid,name from vtiger_tab where isentitytype='1' and presence<> '2' and name not in ( $in )", true );
        for( $i=0; $i< $db->num_rows($wynik); $i++ )
        {
            $name = $db->query_result( $wynik, $i, "name" );
            $sql = "INSERT INTO vtiger_osspdf_config (`conf_id`,`name`,`value`) VALUES ('GENERALCONFIGURATION','$name','default')";
            $wyn = $db->query( $sql, true );
            if( $name != 'OSSPdf' )
            {
                $modCommentsModule = Vtiger_Module::getInstance( $name );
                $mod = '$MODULE$';
                $cat = '$CATEGORY$';
                $record = '$RECORD$';
                $tekst = "PDFselectedRecords('$mod','$cat');";
                
                
                $tekst = "javascript:QuickGenerate('$mod','$cat','$record');";
                //$modCommentsModule->addLink('DETAILVIEWBASIC', 'Generate default PDF' , $tekst, 'Smarty/templates/modules/OSSPdf/gen_domysle_dok.png');
                
                $tekst = "javascript:QuickGenerateMail('$mod','$cat','$record');";
                
                $modCommentsModule->addLink('DETAILVIEWSIDEBARWIDGET', 'Pdf', 'module=OSSPdf&view=ExportPDFRecords&fromdetailview=true');
                $modCommentsModule->addLink('LISTVIEWSIDEBARWIDGET', 'Pdf', 'module=OSSPdf&view=ListViewExportPDFRecords&usingmodule='.$name);
            }
        }
    
        $fieldid = $db->getUniqueID('vtiger_settings_field');
        $blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
        $seq_res = $db->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", array($blockid), true);
        if ($db->num_rows($seq_res) > 0) {
            $cur_seq = $db->query_result($seq_res, 0, 'max_seq');
            if ($cur_seq != null)
                $seq = $cur_seq + 1;
        }

        $db->pquery("INSERT INTO vtiger_settings_field ( fieldid, blockid, name, iconpath, description, linkto, sequence )  VALUES ( ?,?,?,?,?,?,? )", 
        Array($fieldid, $blockid, 'PDF', 'Smarty/templates/modules/OSSValidation/currency_update_mini.png', "LBL_OSSPDF_INFO", "index.php?module=OSSPdf&view=Index&parent=Settings", $seq), true);

		$mods = Vtiger_Module::getInstance( "OSSPdf" );
		$mods->addLink('HEADERSCRIPT', 'PDFUtils', "layouts/vlayout/modules/{$modulename}/resources/PDFUtils.js");
		// block export of module
			$sql = "UPDATE vtiger_tab SET customized=0 WHERE name='$modulename' LIMIT 1;";
			$db->query( $sql, true );
		
			// TODO Handle post installation actions
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
            $mods = Vtiger_Module::getInstance( "OSSPdf" );
            $mods->deleteLink('HEADERSCRIPT', 'PDFUtils', "layouts/vlayout/modules/{$modulename}/resources/PDFUtils.js");
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
            $mods = Vtiger_Module::getInstance( "OSSPdf" );
            $mods->addLink('HEADERSCRIPT', 'PDFUtils', "layouts/vlayout/modules/{$modulename}/resources/PDFUtils.js");
		} else if($event_type == 'module.preuninstall') {		
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
			$db->query( "UPDATE vtiger_field SET sequence = '16' WHERE columnname = 'osspdf_enable_numbering'" );
			$res = $db->query( "SHOW FULL FIELDS FROM vtiger_osspdf_constraints WHERE FIELD = 'relid'" );
			if ($db->num_rows($res) == 0) {
				$db->query( "DROP TABLE vtiger_osspdf_constraints" );
				$db->query( "DROP TABLE vtiger_osspdf_constraints_records" );
				$db->query( "CREATE TABLE `vtiger_osspdf_constraints` (
							  `id` int(19) NOT NULL AUTO_INCREMENT,
							  `relid` int(19) NOT NULL,
							  `fieldname` varchar(255) NOT NULL,
							  `comparator` varchar(255) NOT NULL,
							  `val` varchar(255) NOT NULL,
							  `required` tinyint(19) NOT NULL,
							  `field_type` varchar(100) NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8" );
			}
		}
	}
	
	/** 
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }
	
	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }
}
