<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */

// Note is used to store customer information.
class Documents extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_notes";
	var $table_index = 'notesid';
	var $default_note_name_dom = array('Meeting vtiger_notes', 'Reminder');
	var $tab_name = Array('vtiger_crmentity', 'vtiger_notes', 'vtiger_notescf');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_notes' => 'notesid', 'vtiger_senotesrel' => 'notesid', 'vtiger_notescf' => 'notesid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_notescf', 'notesid');
	var $column_fields = Array();
	var $sortby_fields = Array('title', 'modifiedtime', 'filename', 'createdtime', 'lastname', 'filedownloadcount', 'smownerid');
	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('', '', '', '');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Title' => Array('notes' => 'title'),
		'File Name' => Array('notes' => 'filename'),
		'Modified Time' => Array('crmentity' => 'modifiedtime'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
		'Folder Name' => Array('attachmentsfolder' => 'folderid')
	);
	var $list_fields_name = Array(
		'Title' => 'notes_title',
		'File Name' => 'filename',
		'Modified Time' => 'modifiedtime',
		'Assigned To' => 'assigned_user_id',
		'Folder Name' => 'folderid'
	);
	var $search_fields = Array(
		'Title' => Array('notes' => 'notes_title'),
		'File Name' => Array('notes' => 'filename'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
		'Folder Name' => Array('attachmentsfolder' => 'foldername')
	);
	var $search_fields_name = Array(
		'Title' => 'notes_title',
		'File Name' => 'filename',
		'Assigned To' => 'assigned_user_id',
		'Folder Name' => 'folderid'
	);
	var $list_link_field = 'notes_title';
	var $old_filename = '';
	var $mandatory_fields = Array('notes_title', 'createdtime', 'modifiedtime', 'filename', 'filesize', 'filetype', 'filedownloadcount', 'assigned_user_id');
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'DESC';

	public function save_module($module)
	{
		$log = LoggerManager::getInstance();
		$adb = PearDatabase::getInstance();
		$insertion_mode = $this->mode;
		if (isset($this->parentid) && $this->parentid != '')
			$relid = $this->parentid;
		//inserting into vtiger_senotesrel
		if (isset($relid) && $relid != '') {
			$this->insertintonotesrel($relid, $this->id);
		}
		$filetype_fieldname = $this->getFileTypeFieldName();
		$filename_fieldname = $this->getFile_FieldName();

		if ($this->column_fields[$filetype_fieldname] == 'I') {
			if ($_FILES[$filename_fieldname]['name'] != '') {
				$errCode = $_FILES[$filename_fieldname]['error'];
				if ($errCode == 0) {
					foreach ($_FILES as $fileindex => $files) {
						$fileInstance = \includes\fields\File::loadFromRequest($files);
						if ($fileInstance->validate()) {
							$filename = $_FILES[$filename_fieldname]['name'];
							$filename = \vtlib\Functions::fromHTML(preg_replace('/\s+/', '_', $filename));
							$filetype = $_FILES[$filename_fieldname]['type'];
							$filesize = $_FILES[$filename_fieldname]['size'];
							$filelocationtype = 'I';
							$filename = ltrim(basename(" " . $filename)); //allowed filename like UTF-8 characters
						}
					}
				}
			} elseif ($this->mode == 'edit') {
				$fileres = $adb->pquery("select filetype, filesize,filename,filedownloadcount,filelocationtype from vtiger_notes where notesid=?", array($this->id));
				if ($adb->num_rows($fileres) > 0) {
					$filename = $adb->query_result($fileres, 0, 'filename');
					$filetype = $adb->query_result($fileres, 0, 'filetype');
					$filesize = $adb->query_result($fileres, 0, 'filesize');
					$filedownloadcount = $adb->query_result($fileres, 0, 'filedownloadcount');
					$filelocationtype = $adb->query_result($fileres, 0, 'filelocationtype');
				}
			} elseif ($this->column_fields[$filename_fieldname]) {
				$filename = $this->column_fields[$filename_fieldname];
				$filesize = $this->column_fields['filesize'];
				$filetype = $this->column_fields['filetype'];
				$filelocationtype = $this->column_fields[$filetype_fieldname];
				$filedownloadcount = 0;
			} else {
				$filelocationtype = 'I';
				$filetype = '';
				$filesize = 0;
				$filedownloadcount = null;
			}
		} else if ($this->column_fields[$filetype_fieldname] == 'E') {
			$filelocationtype = 'E';
			$filename = $this->column_fields[$filename_fieldname];
			// If filename does not has the protocol prefix, default it to http://
			// Protocol prefix could be like (https://, smb://, file://, \\, smb:\\,...)
			if (!empty($filename) && !preg_match('/^\w{1,5}:\/\/|^\w{0,3}:?\\\\\\\\/', trim($filename), $match)) {
				$filename = "http://$filename";
			}
			$filetype = '';
			$filesize = 0;
			$filedownloadcount = null;
		}
		$query = "UPDATE vtiger_notes SET filename = ? ,filesize = ?, filetype = ? , filelocationtype = ? , filedownloadcount = ? WHERE notesid = ?";
		$re = $adb->pquery($query, array(decode_html($filename), $filesize, $filetype, $filelocationtype, $filedownloadcount, $this->id));
		//Inserting into attachments table
		if ($filelocationtype == 'I') {
			$this->insertIntoAttachment($this->id, 'Documents');
		} else {
			$query = "delete from vtiger_seattachmentsrel where crmid = ?";
			$qparams = array($this->id);
			$adb->pquery($query, $qparams);
		}
		//set the column_fields so that its available in the event handlers
		$this->column_fields['filename'] = $filename;
		$this->column_fields['filesize'] = $filesize;
		$this->column_fields['filetype'] = $filetype;
		$this->column_fields['filedownloadcount'] = $filedownloadcount;
	}

	/**
	 *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the vtiger_files to be uploaded
	 *      @param string $module  - the current module name
	 */
	public function insertIntoAttachment($id, $module)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;

		foreach ($_FILES as $fileindex => $files) {
			if ($files['name'] != '' && $files['size'] > 0) {
				$files['original_name'] = AppRequest::get($fileindex . '_hidden');
				$file_saved = $this->uploadAndSaveFile($id, $module, $files);
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}

	/**    Function used to get the sort order for Documents listview
	 *      @return string  $sorder - first check the $_REQUEST['sorder'] if request value is empty then check in the $_SESSION['NOTES_SORT_ORDER'] if this session value is empty then default sort order will be returned.
	 */
	public function getSortOrder()
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering getSortOrder() method ...');
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
		else
			$sorder = (($_SESSION['NOTES_SORT_ORDER'] != '') ? ($_SESSION['NOTES_SORT_ORDER']) : ($this->default_sort_order));
		$log->debug('Exiting getSortOrder() method ...');
		return $sorder;
	}

	/**     Function used to get the order by value for Documents listview
	 *       @return string  $order_by  - first check the $_REQUEST['order_by'] if request value is empty then check in the $_SESSION['NOTES_ORDER_BY'] if this session value is empty then default order by will be returned.
	 */
	public function getOrderBy()
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering getOrderBy() method ...');

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
		else
			$order_by = (($_SESSION['NOTES_ORDER_BY'] != '') ? ($_SESSION['NOTES_ORDER_BY']) : ($use_default_order_by));
		$log->debug('Exiting getOrderBy method ...');
		return $order_by;
	}

	/**
	 * Function used to get the sort order for Documents listview
	 * @return String $sorder - sort order for a given folder.
	 */
	public function getSortOrderForFolder($folderId)
	{
		if (AppRequest::has('sorder') && AppRequest::get('folderid') == $folderId) {
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
		} elseif (is_array($_SESSION['NOTES_FOLDER_SORT_ORDER']) &&
			!empty($_SESSION['NOTES_FOLDER_SORT_ORDER'][$folderId])) {
			$sorder = $_SESSION['NOTES_FOLDER_SORT_ORDER'][$folderId];
		} else {
			$sorder = $this->default_sort_order;
		}
		return $sorder;
	}

	/**
	 * Function used to get the order by value for Documents listview
	 * @return String order by column for a given folder.
	 */
	public function getOrderByForFolder($folderId)
	{
		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}
		if (AppRequest::has('order_by') && AppRequest::get('folderid') == $folderId) {
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
		} elseif (is_array($_SESSION['NOTES_FOLDER_ORDER_BY']) &&
			!empty($_SESSION['NOTES_FOLDER_ORDER_BY'][$folderId])) {
			$order_by = $_SESSION['NOTES_FOLDER_ORDER_BY'][$folderId];
		} else {
			$order_by = ($use_default_order_by);
		}
		return $order_by;
	}

	/** Function to export the notes in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Documents Query.
	 */
	public function create_export_query($where)
	{
		$log = LoggerManager::getInstance();
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");
		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Documents", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT $fields_list, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name" .
			" FROM vtiger_notes
				inner join vtiger_crmentity
					on vtiger_crmentity.crmid=vtiger_notes.notesid
				LEFT JOIN `vtiger_trees_templates_data` on vtiger_notes.folderid=`vtiger_trees_templates_data`.tree
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id " .
			" LEFT JOIN vtiger_groups ON vtiger_crmentity.smownerid=vtiger_groups.groupid "
		;
		$query .= getNonAdminAccessControlQuery('Documents', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";
		if ($where != "")
			$query .= "  WHERE ($where) && " . $where_auto;
		else
			$query .= '  WHERE %s';

		$query = sprintf($query, $where_auto);
		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	public function insertintonotesrel($relid, $id)
	{
		$adb = PearDatabase::getInstance();
		$dbQuery = "insert into vtiger_senotesrel values ( ?, ? )";
		$dbresult = $adb->pquery($dbQuery, array($relid, $id));
	}
	/* function save_related_module($module, $crmid, $with_module, $with_crmid){
	  } */


	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, $queryplanner)
	{
		$moduletable = $this->table_name;
		$moduleindex = $this->tab_name_index[$moduletable];
		$query = "from $moduletable
			inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex";
		if ($queryplanner->requireTable("`vtiger_trees_templates_data`")) {
			$query .= " inner join `vtiger_trees_templates_data` on `vtiger_trees_templates_data`.tree=$moduletable.folderid";
		}
		if ($queryplanner->requireTable("vtiger_groups" . $module)) {
			$query .= " left join vtiger_groups as vtiger_groups" . $module . " on vtiger_groups" . $module . ".groupid = vtiger_crmentity.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_users" . $module)) {
			$query .= " left join vtiger_users as vtiger_users" . $module . " on vtiger_users" . $module . ".id = vtiger_crmentity.smownerid";
		}
		$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " left join vtiger_notescf on vtiger_notes.notesid = vtiger_notescf.notesid";
		$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";
		if ($queryplanner->requireTable("vtiger_lastModifiedBy" . $module)) {
			$query .= " left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby ";
		}
		return $query;
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

		$matrix->setDependency("vtiger_crmentityDocuments", array("vtiger_groupsDocuments", "vtiger_usersDocuments", "vtiger_lastModifiedByDocuments"));
		$matrix->setDependency("vtiger_notes", array("vtiger_crmentityDocuments", "`vtiger_trees_templates_data`"));

		if (!$queryplanner->requireTable('vtiger_notes', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_notes", "notesid", $queryplanner);
		$query .= " left join vtiger_notescf on vtiger_notes.notesid = vtiger_notescf.notesid";
		if ($queryplanner->requireTable("vtiger_crmentityDocuments", $matrix)) {
			$query .=" left join vtiger_crmentity as vtiger_crmentityDocuments on vtiger_crmentityDocuments.crmid=vtiger_notes.notesid and vtiger_crmentityDocuments.deleted=0";
		}
		if ($queryplanner->requireTable("`vtiger_trees_templates_data`")) {
			$query .=" left join `vtiger_trees_templates_data` on `vtiger_trees_templates_data`.tree=vtiger_notes.folderid";
		}
		if ($queryplanner->requireTable("vtiger_groupsDocuments")) {
			$query .=" left join vtiger_groups as vtiger_groupsDocuments on vtiger_groupsDocuments.groupid = vtiger_crmentityDocuments.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersDocuments")) {
			$query .=" left join vtiger_users as vtiger_usersDocuments on vtiger_usersDocuments.id = vtiger_crmentityDocuments.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByDocuments")) {
			$query .=" left join vtiger_users as vtiger_lastModifiedByDocuments on vtiger_lastModifiedByDocuments.id = vtiger_crmentityDocuments.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyDocuments")) {
			$query .= " left join vtiger_users as vtiger_createdbyDocuments on vtiger_createdbyDocuments.id = vtiger_crmentityDocuments.smcreatorid ";
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = [];
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	public function unlinkDependencies($module, $id)
	{
		$log = LoggerManager::getInstance();
		/* //Backup Documents Related Records
		  $se_q = 'SELECT crmid FROM vtiger_senotesrel WHERE notesid = ?';
		  $se_res = $this->db->pquery($se_q, array($id));
		  if ($this->db->num_rows($se_res) > 0) {
		  for($k=0;$k < $this->db->num_rows($se_res);$k++)
		  {
		  $se_id = $this->db->query_result($se_res,$k,"crmid");
		  $params = array($id, RB_RECORD_DELETED, 'vtiger_senotesrel', 'notesid', 'crmid', $se_id);
		  $this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		  }
		  }
		  $sql = 'DELETE FROM vtiger_senotesrel WHERE notesid = ?';
		  $this->db->pquery($sql, array($id)); */

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		$log = LoggerManager::getInstance();
		if (empty($returnModule) || empty($returnId))
			return;

		if ($returnModule == 'Accounts') {
			$sql = 'DELETE FROM vtiger_senotesrel WHERE notesid = ? && (crmid = ? || crmid IN (SELECT contactid FROM vtiger_contactdetails WHERE parentid=?))';
			$this->db->pquery($sql, array($id, $returnId, $returnId));
		} else {
			$sql = 'DELETE FROM vtiger_senotesrel WHERE notesid = ? && crmid = ?';
			$this->db->pquery($sql, array($id, $returnId));

			$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? && relmodule=? && relcrmid=?) || (relcrmid=? && module=? && crmid=?)';
			$params = array($id, $returnModule, $returnId, $id, $returnModule, $returnId);
			$this->db->pquery($sql, $params);
		}
	}

// Function to get fieldname for uitype 27 assuming that documents have only one file type field

	public function getFileTypeFieldName()
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$query = 'SELECT fieldname from vtiger_field where tabid = ? and uitype = ?';
		$tabid = \includes\Modules::getModuleId('Documents');
		$filetype_uitype = 27;
		$res = $adb->pquery($query, array($tabid, $filetype_uitype));
		$fieldname = null;
		if (isset($res)) {
			$rowCount = $adb->num_rows($res);
			if ($rowCount > 0) {
				$fieldname = $adb->query_result($res, 0, 'fieldname');
			}
		}
		return $fieldname;
	}

//	public function to get fieldname for uitype 28 assuming that doc has only one file upload type

	public function getFile_FieldName()
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$query = 'SELECT fieldname from vtiger_field where tabid = ? and uitype = ?';
		$tabid = \includes\Modules::getModuleId('Documents');
		$filename_uitype = 28;
		$res = $adb->pquery($query, array($tabid, $filename_uitype));
		$fieldname = null;
		if (isset($res)) {
			$rowCount = $adb->num_rows($res);
			if ($rowCount > 0) {
				$fieldname = $adb->query_result($res, 0, 'fieldname');
			}
		}
		return $fieldname;
	}

	/**
	 * Check the existence of folder by folderid
	 */
	public function isFolderPresent($folderid)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT tree FROM `vtiger_trees_templates_data` WHERE tree = ?", array($folderid));
		if (!empty($result) && $adb->num_rows($result) > 0)
			return true;
		return false;
	}

	/**
	 * Get Folder Default
	 */
	public function getFolderDefault()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT `tree`,`name` FROM
				`vtiger_trees_templates_data` 
			INNER JOIN `vtiger_field` 
				ON `vtiger_trees_templates_data`.`templateid` = `vtiger_field`.`fieldparams` 
			WHERE `vtiger_field`.`columnname` = ? 
				AND `vtiger_field`.`tablename` = ?
				AND `vtiger_trees_templates_data`.`name` = ?;", array('folderid', 'vtiger_notes', 'Default'));
		return $adb->query_result($result, 0, 'tree');
	}

	/**
	 * Customizing the restore procedure.
	 */
	public function restore($modulename, $id)
	{
		parent::restore($modulename, $id);

		$adb = PearDatabase::getInstance();
		$fresult = $adb->pquery("SELECT folderid FROM vtiger_notes WHERE notesid = ?", array($id));
		if (!empty($fresult) && $adb->num_rows($fresult)) {
			$folderid = $adb->query_result($fresult, 0, 'folderid');
			if (!$this->isFolderPresent($folderid)) {
				// Re-link to default folder
				$adb->pquery("UPDATE vtiger_notes set folderid = ? WHERE notesid = ?", array(self::getFolderDefault()));
			}
		}
	}

	public function getRelatedRecord($id, $curTabId, $relTabId, $actions = false)
	{
		global $currentModule, $singlepane_view;
		$thisModule = $currentModule;

		$relatedModule = vtlib\Functions::getModuleName($relTabId);
		$other = CRMEntity::getInstance($relatedModule);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($relatedModule, $other);

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true')
			$returnset = "&return_module=$thisModule&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$thisModule&return_action=CallRelatedList&return_id=$id";

		$joinTables = [];
		$join = '';
		$tables = '';
		foreach ($other->tab_name_index as $table => $index) {
			if ($table == $other->table_name) {
				continue;
			}
			$joinTables[] = $table;
			$join .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $index . ' = ' . $other->table_name . '.' . $other->table_index;
		}

		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$tables .= ", $tname.*";
				if (in_array($tname, $joinTables)) {
					continue;
				}
				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$join .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}
		$query = "SELECT vtiger_crmentity.*, $other->table_name.*";
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name',
				'last_name' => 'vtiger_users.last_name'], 'Users');
		$query .= $tables;
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";
		$query .= ' FROM %s';
		$query .= $join;
		$query .= ' INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.crmid = vtiger_crmentity.crmid';
		$query .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= " WHERE vtiger_crmentity.deleted = 0 && vtiger_senotesrel.notesid = $id";

		$query = sprintf($query, $other->table_name);
		$returnValue = GetRelatedList($thisModule, $relatedModule, $other, $query, $button, $returnset);
		if ($returnValue == null)
			$returnValue = [];
		$returnValue['CUSTOM_BUTTON'] = $button;
		return $returnValue;
	}

	/**
	 * Function to check the module active and user action permissions before showing as link in other modules
	 * like in more actions of detail view.
	 */
	static function isLinkPermitted($linkData)
	{
		$moduleName = 'Documents';
		if (\includes\Modules::isModuleActive($moduleName) && isPermitted($moduleName, 'EditView') == 'yes') {
			return true;
		}
		return false;
	}
}
