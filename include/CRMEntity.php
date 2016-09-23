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
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/vtigercrm/data/CRMEntity.php,v 1.16 2005/04/29 04:21:31 mickie Exp $
 * Description:  Defines the base class for all data entities used throughout the
 * application.  The base class including its methods and variables is designed to
 * be overloaded with module-specific methods and variables particular to the
 * module's base entity class.
 * ****************************************************************************** */

include_once('config/config.php');
require_once('include/logging.php');
require_once('include/Tracker.php');
require_once('include/utils/utils.php');
require_once('include/utils/UserInfoUtil.php');

class CRMEntity
{

	var $ownedby;

	/** 	Constructor which will set the column_fields in this object
	 */
	public function __construct()
	{
		$this->log = LoggerManager::getInstance(get_class($this));
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields(get_class($this));
	}

	/**
	 * Detect if we are in bulk save mode, where some features can be turned-off
	 * to improve performance.
	 */
	static function isBulkSaveMode()
	{
		global $VTIGER_BULK_SAVE_MODE;
		if (isset($VTIGER_BULK_SAVE_MODE) && $VTIGER_BULK_SAVE_MODE) {
			return true;
		}
		return false;
	}

	static function getInstance($module)
	{
		$modName = $module;
		if ($module == 'Calendar' || $module == 'Events') {
			$module = 'Calendar';
			$modName = 'Activity';
		}

		$instance = Vtiger_Cache::get('CRMEntity', $module);
		if ($instance) {
			return clone $instance;
		}

		// File access security check
		if (!class_exists($modName)) {
			if (AppConfig::performance('LOAD_CUSTOM_FILES') && file_exists("custom/modules/$module/$modName.php")) {
				\vtlib\Deprecated::checkFileAccessForInclusion("custom/modules/$module/$modName.php");
				require_once("custom/modules/$module/$modName.php");
			} else {
				\vtlib\Deprecated::checkFileAccessForInclusion("modules/$module/$modName.php");
				require_once("modules/$module/$modName.php");
			}
		}
		$focus = new $modName();
		$focus->moduleName = $module;
		Vtiger_Cache::set('CRMEntity', $module, clone $focus);
		return $focus;
	}

	/**
	 * Save the inventory data
	 */
	public function saveInventoryData($moduleName)
	{
		$db = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__);

		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$table = $inventory->getTableName('data');

		$db->delete($table, 'id = ?', [$this->id]);
		if (is_array($this->inventoryData)) {
			foreach ($this->inventoryData as $insertData) {
				$insertData['id'] = $this->id;
				$db->insert($table, $insertData);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
	}

	public function saveentity($module, $fileid = '')
	{
		$insertion_mode = $this->mode;

		$columnFields = $this->column_fields;
		$anyValue = false;
		foreach ($columnFields as $value) {
			if (!empty($value)) {
				$anyValue = true;
				break;
			}
		}
		if (!$anyValue) {
			throw new \Exception\AppException(vtranslate('LBL_MANDATORY_FIELD_MISSING'));
		}

		foreach ($this->tab_name as $table_name) {
			if ($table_name == 'vtiger_crmentity') {
				$this->insertIntoCrmEntity($module, $fileid);
			} else {
				$this->insertIntoEntityTable($table_name, $module, $fileid);
			}
		}

		if ($this->isInventory === true && !empty($this->inventoryData)) {
			$this->saveInventoryData($module);
		}

		//Calling the Module specific save code
		$this->save_module($module);

		// vtlib customization: Hook provide to enable generic module relation.
		if (AppRequest::get('createmode') == 'link') {
			$for_module = AppRequest::get('return_module');
			$for_crmid = AppRequest::get('return_id');
			$with_module = $module;
			$with_crmid = $this->id;

			$on_focus = CRMEntity::getInstance($for_module);

			if ($for_module && $for_crmid && $with_module && $with_crmid) {
				relateEntities($on_focus, $for_module, $for_crmid, $with_module, $with_crmid);
			}
		}
		// END
	}

	/**
	 *      This function is used to upload the attachment in the server and save that attachment information in db.
	 *      @param int $id  - entity id to which the file to be uploaded
	 *      @param string $module  - the current module name
	 *      @param array $file_details  - array which contains the file information(name, type, size, tmp_name and error)
	 *      return void
	 */
	public function uploadAndSaveFile($id, $module, $file_details, $attachmentType = 'Attachment')
	{
		$log = LoggerManager::getInstance();
		$log->debug("Entering into uploadAndSaveFile($id,$module,$file_details) method.");

		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$date_var = date('Y-m-d H:i:s');

		//to get the owner id
		$ownerid = $this->column_fields['assigned_user_id'];
		if (!isset($ownerid) || $ownerid == '')
			$ownerid = $current_user->id;

		if (isset($file_details['original_name']) && $file_details['original_name'] != null) {
			$file_name = $file_details['original_name'];
		} else {
			$file_name = $file_details['name'];
		}

		$fileInstance = \includes\fields\File::loadFromRequest($file_details);
		if (!$fileInstance->validate()) {
			return false;
		}
		$binFile = \includes\fields\File::sanitizeUploadFileName($file_name);

		$current_id = $adb->getUniqueID('vtiger_crmentity');

		$filename = ltrim(basename(' ' . $binFile)); //allowed filename like UTF-8 characters
		$filetype = $file_details['type'];
		$filesize = $file_details['size'];
		$filetmp_name = $file_details['tmp_name'];

		//get the file path inwhich folder we want to upload the file
		$upload_file_path = \vtlib\Functions::initStorageFileDirectory($module);

		//upload the file in server
		$upload_status = move_uploaded_file($filetmp_name, $upload_file_path . $current_id . '_' . $binFile);
		if ($upload_status == 'true') {
			//This is only to update the attached filename in the vtiger_notes vtiger_table for the Notes module
			$params = [
				'crmid' => $current_id,
				'smcreatorid' => $current_user->id,
				'smownerid' => $ownerid,
				'setype' => $module . " Image",
				'description' => $this->column_fields['description'],
				'createdtime' => $adb->formatDate($date_var, true),
				'modifiedtime' => $adb->formatDate($date_var, true)
			];
			if ($module == 'Contacts' || $module == 'Products') {
				$params['setype'] = $module . ' Image';
			} else {
				$params['setype'] = $module . ' Attachment';
			}
			$adb->insert('vtiger_crmentity', $params);

			$params = [
				'attachmentsid' => $current_id,
				'name' => $filename,
				'description' => $this->column_fields['description'],
				'type' => $filetype,
				'path' => $upload_file_path
			];
			$adb->insert('vtiger_attachments', $params);

			if (AppRequest::get('mode') == 'edit') {
				if ($id != '' && AppRequest::get('fileid') != '') {
					$delparams = [$id, AppRequest::get('fileid')];
					$adb->delete('vtiger_seattachmentsrel', 'crmid = ? && attachmentsid = ?', $delparams);
				}
			}
			if ($module == 'Documents') {
				$adb->delete('vtiger_seattachmentsrel', 'crmid = ?', [$id]);
			}
			if ($module == 'Contacts') {
				$att_sql = "select vtiger_seattachmentsrel.attachmentsid  from vtiger_seattachmentsrel inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_seattachmentsrel.attachmentsid where vtiger_crmentity.setype='Contacts Image' and vtiger_seattachmentsrel.crmid=?";
				$res = $adb->pquery($att_sql, array($id));
				$attachmentsid = $adb->query_result($res, 0, 'attachmentsid');
				if ($attachmentsid != '') {
					$adb->delete('vtiger_seattachmentsrel', 'crmid = ? && attachmentsid = ?', [$id, $attachmentsid]);
					$adb->delete('vtiger_crmentity', 'crmid = ?', [$attachmentsid]);
					$adb->insert('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $current_id]);
				} else {
					$adb->insert('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $current_id]);
				}
			} else {
				$adb->insert('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $current_id]);
			}

			return true;
		} else {
			$log->debug('Skip the save attachment process.');
			return false;
		}
	}

	/** Function to insert values in the vtiger_crmentity for the specified module
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoCrmEntity($module, $fileid = '')
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = LoggerManager::getInstance();

		if ($fileid != '') {
			$this->id = $fileid;
			$this->mode = 'edit';
		}

		$date_var = date('Y-m-d H:i:s');
		$insertion_mode = $this->mode;

		$ownerid = $this->column_fields['assigned_user_id'];
		if (empty($ownerid)) {
			$ownerid = $current_user->id;
		}

		if ($module == 'Events') {
			$module = 'Calendar';
		}

		if ($this->mode == 'edit') {
			$description_val = \vtlib\Functions::fromHTML($this->column_fields['description'], ($insertion_mode == 'edit') ? true : false);
			$attention_val = \vtlib\Functions::fromHTML($this->column_fields['attention'], ($insertion_mode == 'edit') ? true : false);
			$was_read = ($this->column_fields['was_read'] == 'on') ? true : false;
			\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/user_privileges_' . $current_user->id . '.php');
			require('user_privileges/user_privileges_' . $current_user->id . '.php');
			$tabid = \includes\Modules::getModuleId($module);
			if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
				$columns = [
					'smownerid' => $ownerid,
					'modifiedby' => $current_user->id,
					'description' => $description_val,
					'attention' => $attention_val,
					'modifiedtime' => $adb->formatDate($date_var, true),
					'was_read' => $was_read
				];
			} else {
				$profileList = getCurrentUserProfileList();
				$perm_qry = sprintf('SELECT columnname FROM vtiger_field 
					INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid = vtiger_field.fieldid 
					INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid = vtiger_field.fieldid 
					WHERE vtiger_field.tabid = ? && vtiger_profile2field.visible = 0 
					AND vtiger_profile2field.readonly = 0 
					AND vtiger_profile2field.profileid IN (%s) 
					AND vtiger_def_org_field.visible = 0 and vtiger_field.tablename=\'vtiger_crmentity\' 
					and vtiger_field.presence in (0,2)', generateQuestionMarks($profileList));
				$perm_result = $adb->pquery($perm_qry, [$tabid, $profileList]);
				while ($columnname = $adb->getSingleValue($perm_result)) {
					$columname[] = $columnname;
				}
				if (is_array($columname) && in_array('description', $columname)) {
					$columns = [
						'smownerid' => $ownerid,
						'modifiedby' => $current_user->id,
						'description' => $description_val,
						'attention' => $attention_val,
						'modifiedtime' => $adb->formatDate($date_var, true),
						'was_read' => $was_read
					];
				} else {
					$columns = [
						'smownerid' => $ownerid,
						'modifiedby' => $current_user->id,
						'modifiedtime' => $adb->formatDate($date_var, true)
					];
				}
			}
			$params = [$this->id];
			$adb->update('vtiger_crmentity', $columns, 'crmid = ?', $params);
			$this->column_fields['modifiedtime'] = $adb->formatDate($date_var, true);
			$this->column_fields['modifiedby'] = $current_user->id;
		} else {
			//if this is the create mode and the group allocation is chosen, then do the following
			if (empty($this->newRecord)) {
				$this->id = $adb->getUniqueID('vtiger_crmentity');
			} else {
				$this->id = $this->newRecord;
			}
			if (empty($current_user->id))
				$current_user->id = 0;

			// Customization
			$created_date_var = $adb->formatDate($date_var, true);
			$modified_date_var = $adb->formatDate($date_var, true);
			// Preserve the timestamp
			if (self::isBulkSaveMode()) {
				if (!empty($this->column_fields['createdtime']))
					$created_date_var = $adb->formatDate($this->column_fields['createdtime'], true);
				//NOTE : modifiedtime ignored to support vtws_sync API track changes.
			}
			// END

			$description_val = \vtlib\Functions::fromHTML($this->column_fields['description'], ($insertion_mode == 'edit') ? true : false);
			$attention_val = \vtlib\Functions::fromHTML($this->column_fields['attention'], ($insertion_mode == 'edit') ? true : false);
			$params = [
				'crmid' => $this->id,
				'smcreatorid' => $current_user->id,
				'smownerid' => $ownerid,
				'setype' => $module,
				'description' => $description_val,
				'attention' => $attention_val,
				'modifiedby' => $current_user->id,
				'createdtime' => $created_date_var,
				'modifiedtime' => $modified_date_var
			];
			$adb->insert('vtiger_crmentity', $params);

			$this->column_fields['createdtime'] = $created_date_var;
			$this->column_fields['modifiedtime'] = $modified_date_var;
			$this->column_fields['modifiedby'] = $current_user->id;
		}
	}

	// Function which returns the value based on result type (array / ADODB ResultSet)
	private function resolve_query_result_value($result, $index, $columnname)
	{
		$adb = PearDatabase::getInstance();
		if (is_array($result))
			return $result[$index][$columnname];
		else
			return $adb->query_result($result, $index, $columnname);
	}

	/** Function to insert values in the specifed table for the specified module
	 * @param $table_name -- table name:: Type varchar
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoEntityTable($table_name, $module, $fileid = '')
	{
		$log = LoggerManager::getInstance();
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$log->info("function insertIntoEntityTable " . $module . ' vtiger_table name ' . $table_name);
		$adb = PearDatabase::getInstance();
		$insertion_mode = $this->mode;

		//Checkin whether an entry is already is present in the vtiger_table to update
		if ($insertion_mode == 'edit') {
			$tablekey = $this->tab_name_index[$table_name];
			// Make selection on the primary key of the module table to check.
			$check_query = "select $tablekey from $table_name where $tablekey=?";
			$check_result = $adb->pquery($check_query, array($this->id));

			$num_rows = $adb->num_rows($check_result);

			if ($num_rows <= 0) {
				$insertion_mode = '';
			}
		}

		$tabid = \includes\Modules::getModuleId($module);
		if ($module == 'Calendar' && $this->column_fields["activitytype"] != null && $this->column_fields["activitytype"] != 'Task') {
			$tabid = \includes\Modules::getModuleId('Events');
		}
		if ($insertion_mode == 'edit') {
			$updateColumns = [];
			\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/user_privileges_' . $current_user->id . '.php');
			require('user_privileges/user_privileges_' . $current_user->id . '.php');
			if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
				$sql = sprintf('SELECT * FROM vtiger_field WHERE tabid in (%s) && tablename = ? && presence IN (0,2) GROUP BY columnname', $adb->generateQuestionMarks($tabid));
				$params = [$tabid, $table_name];
			} else {
				$profileList = getCurrentUserProfileList();

				if (count($profileList) > 0) {
					$sql = sprintf('SELECT *
			  			FROM vtiger_field
			  			INNER JOIN vtiger_profile2field
			  			ON vtiger_profile2field.fieldid = vtiger_field.fieldid
			  			INNER JOIN vtiger_def_org_field
			  			ON vtiger_def_org_field.fieldid = vtiger_field.fieldid
			  			WHERE vtiger_field.tabid = ?
			  			AND vtiger_profile2field.visible = 0 && vtiger_profile2field.readonly = 0
			  			AND vtiger_profile2field.profileid IN (%s)
			  			AND vtiger_def_org_field.visible = 0 and vtiger_field.tablename=? and vtiger_field.presence in (0,2) group by columnname', generateQuestionMarks($profileList));

					$params = array($tabid, $profileList, $table_name);
				} else {
					$sql = 'SELECT *
			  			FROM vtiger_field
			  			INNER JOIN vtiger_profile2field
			  			ON vtiger_profile2field.fieldid = vtiger_field.fieldid
			  			INNER JOIN vtiger_def_org_field
			  			ON vtiger_def_org_field.fieldid = vtiger_field.fieldid
			  			WHERE vtiger_field.tabid = ?
			  			AND vtiger_profile2field.visible = 0 && vtiger_profile2field.readonly = 0
			  			AND vtiger_def_org_field.visible = 0 and vtiger_field.tablename=? and vtiger_field.presence in (0,2) group by columnname';

					$params = array($tabid, $table_name);
				}
			}
		} else {
			$table_index_column = $this->tab_name_index[$table_name];
			if ($table_index_column == 'id' && $table_name == 'vtiger_users') {
				$currentuser_id = $adb->getUniqueID("vtiger_users");
				$this->id = $currentuser_id;
			}
			$column = array($table_index_column);
			$value = array($this->id);
			$sql = 'select * from vtiger_field where tabid=? and tablename=? and vtiger_field.presence in (0,2)';
			$params = array($tabid, $table_name);
		}

		$cachekey = "{$insertion_mode}-" . implode(',', $params);
		$insertField = Vtiger_Cache::get('getInsertField', $cachekey);
		if ($insertField === false) {
			$result = $adb->pquery($sql, $params);
			$noofrows = $adb->num_rows($result);

			if (CRMEntity::isBulkSaveMode()) {
				$cacheresult = [];
				for ($i = 0; $i < $noofrows; ++$i) {
					$cacheresult[] = $adb->raw_query_result_rowdata($result, $i);
				}
				Vtiger_Cache::set('getInsertField', $cachekey, $cacheresult);
			}
		} else { // Useful when doing bulk save
			$result = $insertField;
			$noofrows = count($result);
		}

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldname = $this->resolve_query_result_value($result, $i, 'fieldname');
			$columname = $this->resolve_query_result_value($result, $i, 'columnname');
			$uitype = $this->resolve_query_result_value($result, $i, 'uitype');
			$generatedtype = $this->resolve_query_result_value($result, $i, 'generatedtype');
			$typeofdata = $this->resolve_query_result_value($result, $i, 'typeofdata');

			$typeofdata_array = explode("~", $typeofdata);
			$datatype = $typeofdata_array[0];

			$ajaxSave = false;
			if ((AppRequest::get('file') == 'DetailViewAjax' && AppRequest::get('ajxaction') == 'DETAILVIEW' && AppRequest::has('fldName') && AppRequest::get('fldName') != $fieldname) || (AppRequest::get('action') == 'MassEditSave' && !AppRequest::get($fieldname . '_mass_edit_check'))) {
				$ajaxSave = true;
			}

			if ($uitype == 4 && $insertion_mode != 'edit') {
				$fldvalue = '';
				// Bulk Save Mode: Avoid generation of module sequence number, take care later.
				if (!CRMEntity::isBulkSaveMode()) {
					$fldvalue = \includes\fields\RecordNumber::incrementNumber($tabid);
				}
				$this->column_fields[$fieldname] = $fldvalue;
			}
			if (isset($this->column_fields[$fieldname])) {
				if ($uitype == 56) {
					if ($this->column_fields[$fieldname] == 'on' || $this->column_fields[$fieldname] == 1) {
						$fldvalue = '1';
					} else {
						$fldvalue = '0';
					}
				} elseif ($uitype == 15 || $uitype == 16) {

					if ($this->column_fields[$fieldname] == \includes\Language::translate('LBL_NOT_ACCESSIBLE')) {

						//If the value in the request is Not Accessible for a picklist, the existing value will be replaced instead of Not Accessible value.
						$sql = "select $columname from  $table_name where " . $this->tab_name_index[$table_name] . "=?";
						$res = $adb->pquery($sql, array($this->id));
						$pick_val = $adb->query_result($res, 0, $columname);
						$fldvalue = $pick_val;
					} else {
						$fldvalue = $this->column_fields[$fieldname];
					}
				} elseif ($uitype == 33) {
					if (is_array($this->column_fields[$fieldname])) {
						$field_list = implode(' |##| ', $this->column_fields[$fieldname]);
					} else {
						$field_list = $this->column_fields[$fieldname];
					}
					if ($field_list == '') {
						$fldvalue = NULL;
					} else {
						$fldvalue = $field_list;
					}
				} elseif (in_array($uitype, [303, 304, 122])) {
					if (is_array($this->column_fields[$fieldname])) {
						$field_list = implode(',', $this->column_fields[$fieldname]);
					} else {
						$field_list = $this->column_fields[$fieldname];
					}
					$fldvalue = $field_list;
				} elseif ($uitype == 5 || $uitype == 6 || $uitype == 23) {
					//Added to avoid function call getDBInsertDateValue in ajax save
					if (isset($current_user->date_format) && !$ajaxSave) {
						$fldvalue = getValidDBInsertDateValue($this->column_fields[$fieldname]);
					} else {
						$fldvalue = $this->column_fields[$fieldname];
					}
				} elseif ($uitype == 14) {
					$fldvalue = Vtiger_Time_UIType::getDBTimeFromUserValue($this->column_fields[$fieldname]);
				} elseif ($uitype == 7) {
					//strip out the spaces and commas in numbers if given ie., in amounts there may be ,
					$fldvalue = str_replace(",", "", $this->column_fields[$fieldname]); //trim($this->column_fields[$fieldname],",");
				} elseif ($uitype == 26) {
					if (empty($this->column_fields[$fieldname])) {
						$fldvalue = 1; //the documents will stored in default folder
					} else {
						$fldvalue = $this->column_fields[$fieldname];
					}
				} elseif ($uitype == 28) {
					if ($this->column_fields[$fieldname] == null) {
						$fileQuery = $adb->pquery("SELECT filename from vtiger_notes WHERE notesid = ?", array($this->id));
						$fldvalue = null;
						if (isset($fileQuery)) {
							$rowCount = $adb->num_rows($fileQuery);
							if ($rowCount > 0) {
								$fldvalue = decode_html($adb->query_result($fileQuery, 0, 'filename'));
							}
						}
					} else {
						$fldvalue = decode_html($this->column_fields[$fieldname]);
					}
				} elseif ($uitype == 8) {
					$this->column_fields[$fieldname] = rtrim($this->column_fields[$fieldname], ',');
					$ids = explode(',', $this->column_fields[$fieldname]);
					$fldvalue = \includes\utils\Json::encode($ids);
				} elseif ($uitype == 12) {

					// Bulk Sae Mode: Consider the FROM email address as specified, if not lookup
					$fldvalue = $this->column_fields[$fieldname];

					if (empty($fldvalue)) {
						$query = "SELECT email1 FROM vtiger_users WHERE id = ?";
						$res = $adb->pquery($query, array($current_user->id));
						$rows = $adb->num_rows($res);
						if ($rows > 0) {
							$fldvalue = $adb->query_result($res, 0, 'email1');
						}
					}
					// END
				} elseif ($uitype == 72 && !$ajaxSave) {
					// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
					$fldvalue = CurrencyField::convertToDBFormat($this->column_fields[$fieldname], null, true);
				} elseif ($uitype == 71 && !$ajaxSave) {
					$fldvalue = CurrencyField::convertToDBFormat($this->column_fields[$fieldname]);
				} else {
					$fldvalue = $this->column_fields[$fieldname];
				}
				if ($uitype != 33 && $uitype != 8)
					$fldvalue = \vtlib\Functions::fromHTML($fldvalue, ($insertion_mode == 'edit') ? true : false);
			}
			else {
				$fldvalue = '';
			}

			if ($fldvalue == '') {
				$fldvalue = $this->get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
			}

			if ($insertion_mode == 'edit') {
				if ($uitype != '4') {
					$updateColumns[$columname] = $fldvalue;
				}
			} else {
				array_push($column, $columname);
				array_push($value, $fldvalue);
			}
		}

		if ($insertion_mode == 'edit') {
			//Check done by Don. If update is empty the the query fails
			if (count($updateColumns) > 0) {
				$adb->update($table_name, $updateColumns, $this->tab_name_index[$table_name] . ' = ?', [$this->id]);
			}
		} else {
			$params = array_combine($column, $value);
			$adb->insert($table_name, $params);
		}
	}

	/** Function to delete a record in the specifed table
	 * @param $table_name -- table name:: Type varchar
	 * The function will delete a record .The id is obtained from the class variable $this->id and the columnname got from $this->tab_name_index[$table_name]
	 */
	public function deleteRelation($table_name)
	{
		$adb = PearDatabase::getInstance();
		$check_query = "select * from $table_name where " . $this->tab_name_index[$table_name] . "=?";
		$check_result = $adb->pquery($check_query, array($this->id));
		$num_rows = $adb->num_rows($check_result);

		if ($num_rows == 1) {
			$adb->delete($table_name, $this->tab_name_index[$table_name] . ' = ?', [$this->id]);
		}
	}

	/** Function to attachment filename of the given entity
	 * @param $notesid -- crmid:: Type Integer
	 * The function will get the attachmentsid for the given entityid from vtiger_seattachmentsrel table and get the attachmentsname from vtiger_attachments table
	 * returns the 'filename'
	 */
	public function getOldFileName($notesid)
	{
		$log = LoggerManager::getInstance();
		$log->info("in getOldFileName  " . $notesid);
		$adb = PearDatabase::getInstance();
		$query1 = "select * from vtiger_seattachmentsrel where crmid=?";
		$result = $adb->pquery($query1, array($notesid));
		$noofrows = $adb->num_rows($result);
		if ($noofrows != 0)
			$attachmentid = $adb->query_result($result, 0, 'attachmentsid');
		if ($attachmentid != '') {
			$query2 = "select * from vtiger_attachments where attachmentsid=?";
			$filename = $adb->query_result($adb->pquery($query2, array($attachmentid)), 0, 'name');
		}
		return $filename;
	}

	/**
	 * Function returns the column alias for a field
	 * @param <Array> $fieldinfo - field information
	 * @return <String> field value
	 */
	protected function createColumnAliasForField($fieldinfo)
	{
		return strtolower($fieldinfo['tablename'] . $fieldinfo['fieldname']);
	}

	/**
	 * Retrieve record information of the module
	 * @param <Integer> $record - crmid of record
	 * @param <String> $module - module name
	 */
	public function retrieve_entity_info($record, $module)
	{
		$adb = PearDatabase::getInstance();

		if (!isset($record)) {
			throw new \Exception\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
		}
		// INNER JOIN is desirable if all dependent table has entries for the record.
		// LEFT JOIN is desired if the dependent tables does not have entry.
		$join_type = 'LEFT JOIN';

		// Tables which has multiple rows for the same record
		// will be skipped in record retrieve - need to be taken care separately.
		$multirow_tables = NULL;
		if (isset($this->multirow_tables)) {
			$multirow_tables = $this->multirow_tables;
		} else {
			$multirow_tables = array(
				'vtiger_campaignrelstatus',
				'vtiger_attachments',
				//'vtiger_inventoryproductrel',
				'vtiger_email_track'
			);
		}

		// Lookup module field cache
		if ($module == 'Calendar' || $module == 'Events') {
			getColumnFields('Calendar');
			if (VTCacheUtils::lookupFieldInfo_Module('Events'))
				$cachedEventsFields = VTCacheUtils::lookupFieldInfo_Module('Events');
			else
				$cachedEventsFields = [];
			$cachedCalendarFields = VTCacheUtils::lookupFieldInfo_Module('Calendar');
			$cachedModuleFields = array_merge($cachedEventsFields, $cachedCalendarFields);
			$module = 'Calendar';
		} else {
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}
		if ($cachedModuleFields === false) {
			// Pull fields and cache for further use
			$tabid = \includes\Modules::getModuleId($module);

			$sql0 = "SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata,presence FROM vtiger_field WHERE tabid=?";
			// NOTE: Need to skip in-active fields which we will be done later.
			$result0 = $adb->pquery($sql0, array($tabid));
			if ($adb->num_rows($result0)) {
				while ($resultrow = $adb->fetch_array($result0)) {
					// Update cache
					VTCacheUtils::updateFieldInfo(
						$tabid, $resultrow['fieldname'], $resultrow['fieldid'], $resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'], $resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
					);
				}
				// Get only active field information
				$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
			}
		}

		if ($cachedModuleFields) {
			$column_clause = '';
			$from_clause = '';
			$where_clause = '';
			$limit_clause = ' LIMIT 1'; // to eliminate multi-records due to table joins.

			$params = [];
			$required_tables = $this->tab_name_index; // copies-on-write

			foreach ($cachedModuleFields as $fieldinfo) {
				if (in_array($fieldinfo['tablename'], $multirow_tables)) {
					continue;
				}

				// Alias prefixed with tablename+fieldname to avoid duplicate column name across tables
				// fieldname are always assumed to be unique for a module
				$column_clause .= $fieldinfo['tablename'] . '.' . $fieldinfo['columnname'] . ' AS ' . $this->createColumnAliasForField($fieldinfo) . ',';
			}
			$column_clause .= 'vtiger_crmentity.deleted';

			if (isset($required_tables['vtiger_crmentity'])) {
				$from_clause = ' vtiger_crmentity';
				unset($required_tables['vtiger_crmentity']);
				foreach ($required_tables as $tablename => $tableindex) {
					if (in_array($tablename, $multirow_tables)) {
						// Avoid multirow table joins.
						continue;
					}
					$from_clause .= sprintf(' %s %s ON %s.%s=%s.%s', $join_type, $tablename, $tablename, $tableindex, 'vtiger_crmentity', 'crmid');
				}
			}

			$where_clause .= ' vtiger_crmentity.crmid = ? ';
			$params[] = $record;
			if ($module != '') {
				$where_clause .= ' && vtiger_crmentity.setype = ?';
				$params[] = $module;
			}

			$sql = sprintf('SELECT %s FROM %s WHERE %s %s', $column_clause, $from_clause, $where_clause, $limit_clause);

			$result = $adb->pquery($sql, $params);

			if (!$result || $adb->num_rows($result) < 1) {
				throw new \Exception\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
			} else {
				$resultrow = $adb->getRow($result);
				if (!empty($resultrow['deleted'])) {
					throw new \Exception\NoPermittedToRecord('LBL_RECORD_DELETE');
				}
				$showsAdditionalLabels = vglobal('showsAdditionalLabels');
				foreach ($cachedModuleFields as $fieldinfo) {
					$fieldvalue = '';
					$fieldkey = $this->createColumnAliasForField($fieldinfo);
					//Note : value is retrieved with a tablename+fieldname as we are using alias while building query
					if (isset($resultrow[$fieldkey])) {
						$fieldvalue = $resultrow[$fieldkey];
					}
					if ($showsAdditionalLabels && in_array($fieldinfo['uitype'], [10, 51, 73])) {
						$this->column_fields[$fieldinfo['fieldname'] . '_label'] = vtlib\Functions::getCRMRecordLabel($fieldvalue);
					}
					if ($showsAdditionalLabels && in_array($fieldinfo['uitype'], [52, 53])) {
						$this->column_fields[$fieldinfo['fieldname'] . '_label'] = vtlib\Functions::getOwnerRecordLabel($fieldvalue);
					}
					$this->column_fields[$fieldinfo['fieldname']] = $fieldvalue;
				}
			}
		}

		$this->column_fields['record_id'] = $record;
		$this->column_fields['record_module'] = $module;
	}

	/** Function to saves the values in all the tables mentioned in the class variable $tab_name for the specified module
	 * @param $module -- module:: Type varchar
	 */
	public function save($module_name, $fileid = '')
	{
		$log = LoggerManager::getInstance();
		$log->debug("module name is " . $module_name);

		//Event triggering code
		require_once("include/events/include.inc");
		$adb = PearDatabase::getInstance();

		//In Bulk mode stop triggering events
		if (!self::isBulkSaveMode()) {
			$em = new VTEventsManager($adb);
			// Initialize Event trigger cache
			$em->initTriggerCache();
			$entityData = VTEntityData::fromCRMEntity($this);

			$em->triggerEvent('vtiger.entity.beforesave.modifiable', $entityData);
			$em->triggerEvent('vtiger.entity.beforesave', $entityData);
			$em->triggerEvent('vtiger.entity.beforesave.final', $entityData);
		}
		//Event triggering code ends
		//GS Save entity being called with the modulename as parameter
		$this->saveentity($module_name, $fileid);

		if ($em) {
			//Event triggering code
			$em->triggerEvent('vtiger.entity.aftersave', $entityData);
			$em->triggerEvent('vtiger.entity.aftersave.final', $entityData);
			//Event triggering code ends
		}
	}

	public function process_full_list_query($query)
	{
		$this->log->debug("CRMEntity:process_full_list_query");
		$result = & $this->db->query($query, false);


		if ($this->db->getRowCount($result) > 0) {

			while ($row = $this->db->fetchByAssoc($result)) {
				$rowid = $row[$this->table_index];

				if (isset($rowid))
					$this->retrieve_entity_info($rowid, $this->module_name);
				else
					$this->db->println("rowid not set unable to retrieve");



				//clone function added to resolvoe PHP5 compatibility issue in Dashboards
				//If we do not use clone, while using PHP5, the memory address remains fixed but the
				//data gets overridden hence all the rows that come in bear the same value. This in turn
//provides a wrong display of the Dashboard graphs. The data is erroneously shown for a specific month alone
//Added by Richie
				$list[] = clone($this); //added by Richie to support PHP5
			}
		}

		if (isset($list))
			return $list;
		else
			return null;
	}

	/** This function should be overridden in each module.  It marks an item as deleted.
	 * If it is not overridden, then marking this type of item is not allowed
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function mark_deleted($id)
	{
		$current_user = vglobal('current_user');
		$date_var = date("Y-m-d H:i:s");
		$query = "UPDATE vtiger_crmentity set deleted=1,modifiedtime=?,modifiedby=? where crmid=?";
		$this->db->pquery($query, array($this->db->formatDate($date_var, true), $current_user->id, $id), true, "Error marking record deleted: ");
	}

	public function retrieve_by_string_fields($fields_array, $encode = true)
	{
		$where_clause = $this->get_where($fields_array);

		$query = "SELECT * FROM $this->table_name $where_clause";
		$this->log->debug("Retrieve $this->object_name: " . $query);
		$result = & $this->db->requireSingleResult($query, true, "Retrieving record $where_clause:");
		if (empty($result)) {
			return null;
		}

		$row = $this->db->fetchByAssoc($result, -1, $encode);

		foreach ($this->column_fields as $field) {
			if (isset($row[$field])) {
				$this->$field = $row[$field];
			}
		}
		return $this;
	}

	// this method is called during an import before inserting a bean
	// define an associative array called $special_fields
	// the keys are user defined, and don't directly map to the bean's vtiger_fields
	// the value is the method name within that bean that will do extra
	// processing for that vtiger_field. example: 'full_name'=>'get_names_from_full_name'

	public function process_special_fields()
	{
		foreach ($this->special_functions as $func_name) {
			if (method_exists($this, $func_name)) {
				$this->$func_name();
			}
		}
	}

	/**
	 * Function to check if the custom vtiger_field vtiger_table exists
	 * return true or false
	 */
	public function checkIfCustomTableExists($tablename)
	{
		$adb = PearDatabase::getInstance();
		$query = sprintf("SELECT * FROM %s", $adb->sql_escape_string($tablename));
		$result = $this->db->pquery($query, []);
		$testrow = $this->db->getFieldsCount($result);
		if ($testrow > 1) {
			$exists = true;
		} else {
			$exists = false;
		}
		return $exists;
	}

	/**
	 * function to construct the query to fetch the custom vtiger_fields
	 * return the query to fetch the custom vtiger_fields
	 */
	public function constructCustomQueryAddendum($tablename, $module)
	{
		$adb = PearDatabase::getInstance();
		$tabid = \includes\Modules::getModuleId($module);
		$sql1 = "select columnname,fieldlabel from vtiger_field where generatedtype=2 and tabid=? and vtiger_field.presence in (0,2)";
		$result = $adb->pquery($sql1, array($tabid));
		$numRows = $adb->num_rows($result);
		$sql3 = "select ";
		for ($i = 0; $i < $numRows; $i++) {
			$columnName = $adb->query_result($result, $i, "columnname");
			$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
			//construct query as below
			if ($i == 0) {
				$sql3 .= $tablename . "." . $columnName . " '" . $fieldlabel . "'";
			} else {
				$sql3 .= ", " . $tablename . "." . $columnName . " '" . $fieldlabel . "'";
			}
		}
		if ($numRows > 0) {
			$sql3 = $sql3 . ',';
		}
		return $sql3;
	}

	/**
	 * This function returns a full (ie non-paged) list of the current object type.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function get_full_list($order_by = "", $where = "")
	{
		$this->log->debug("get_full_list:  order_by = '$order_by' and where = '$where'");
		$query = $this->create_list_query($order_by, $where);
		return $this->process_full_list_query($query);
	}

	/**
	 * Track the viewing of a detail record.  This leverages get_summary_text() which is object specific
	 * params $user_id - The user that is viewing the record.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function track_view($user_id, $current_module, $id = '')
	{
		$this->log->debug("About to call vtiger_tracker (user_id, module_name, item_id)($user_id, $current_module, $this->id)");

		$tracker = new Tracker();
		$tracker->track_view($user_id, $current_module, $id, '');
	}

	/**
	 * Function to get the column value of a field when the field value is empty ''
	 * @param $columnname -- Column name for the field
	 * @param $fldvalue -- Input value for the field taken from the User
	 * @param $fieldname -- Name of the Field
	 * @param $uitype -- UI type of the field
	 * @return Column value of the field.
	 */
	public function get_column_value($columName, $fldvalue, $fieldname, $uitype, $datatype = '')
	{
		$log = LoggerManager::getInstance();
		$log->debug("Entering function get_column_value ($columName, $fldvalue, $fieldname, $uitype, $datatype='')");

		// Added for the fields of uitype '57' which has datatype mismatch in crmentity table and particular entity table
		if ($uitype == 57 && $fldvalue == '') {
			return 0;
		}
		if (in_array($uitype, [307, 308]) && $fldvalue == '') {
			return null;
		}
		if (is_uitype($uitype, "_date_") && $fldvalue == '' || $uitype == '14') {
			return null;
		}
		if ($datatype == 'I' || $datatype == 'N' || $datatype == 'NN') {
			return 0;
		}
		$log->debug("Exiting function get_column_value");
		return $fldvalue;
	}

	/**
	 * Function to make change to column fields, depending on the current user's accessibility for the fields
	 */
	public function apply_field_security($moduleName = '')
	{
		global $currentModule;
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($moduleName == '') {
			$moduleName = $currentModule;
		}
		require_once('include/utils/UserInfoUtil.php');
		foreach ($this->column_fields as $fieldname => $fieldvalue) {
			$reset_value = false;
			if (getFieldVisibilityPermission($moduleName, $current_user->id, $fieldname) != '0')
				$reset_value = true;

			if ($fieldname == "record_id" || $fieldname == "record_module")
				$reset_value = false;

			/*
			  if (isset($this->additional_column_fields) && in_array($fieldname, $this->additional_column_fields) == true)
			  $reset_value = false;
			 */

			if ($reset_value == true)
				$this->column_fields[$fieldname] = "";
		}
	}

	/**
	 * Function invoked during export of module record value.
	 */
	public function transform_export_value($key, $value)
	{
		// NOTE: The sub-class can override this function as required.
		return $value;
	}

	/**
	 * Function to initialize the importable fields array, based on the User's accessibility to the fields
	 */
	public function initImportableFields($module)
	{
		$adb = PearDatabase::getInstance();
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		require_once('include/utils/UserInfoUtil.php');

		$skip_uitypes = array('4'); // uitype 4 is for Mod numbers
		// Look at cache if the fields information is available.
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);

		if ($cachedModuleFields === false) {
			getColumnFields($module); // This API will initialize the cache as well
			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}

		$colf = [];

		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				// Skip non-supported fields
				if (in_array($fieldinfo['uitype'], $skip_uitypes)) {
					continue;
				} else {
					$colf[$fieldinfo['fieldname']] = $fieldinfo['uitype'];
				}
			}
		}

		foreach ($colf as $key => $value) {
			if (getFieldVisibilityPermission($module, $current_user->id, $key, 'readwrite') == '0')
				$this->importable_fields[$key] = $value;
		}
	}

	/** Function to initialize the required fields array for that particular module */
	public function initRequiredFields($module)
	{
		$adb = PearDatabase::getInstance();

		$tabid = \includes\Modules::getModuleId($module);
		$sql = "select * from vtiger_field where tabid= ? and typeofdata like '%M%' and uitype not in ('53','70') and vtiger_field.presence in (0,2)";
		$result = $adb->pquery($sql, array($tabid));
		$numRows = $adb->num_rows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$fieldName = $adb->query_result($result, $i, "fieldname");
			$this->required_fields[$fieldName] = 1;
		}
	}

	/** Function to delete an entity with given Id */
	public function trash($module, $id)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$recordType = vtlib\Functions::getCRMRecordType($id);
		if ($recordType != $module) {
			throw new \Exception\AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
		if (!self::isBulkSaveMode()) {
			require_once("include/events/include.inc");
			$em = new VTEventsManager($adb);

			// Initialize Event trigger cache
			$em->initTriggerCache();

			$entityData = VTEntityData::fromEntityId($adb, $id);

			$em->triggerEvent("vtiger.entity.beforedelete", $entityData);
		}
		$this->mark_deleted($id);
		$this->unlinkDependencies($module, $id);

		require_once('libraries/freetag/freetag.class.php');
		$freetag = new freetag();
		$freetag->delete_all_object_tags_for_user($current_user->id, $id);

		$this->db->delete('vtiger_tracker', 'user_id = ? && item_id = ?', [$current_user->id, $id]);

		if ($em) {
			$em->triggerEvent("vtiger.entity.afterdelete", $entityData);
		}
	}

	/** Function to unlink all the dependent entities of the given Entity by Id */
	public function unlinkDependencies($module, $id)
	{
		$log = LoggerManager::getInstance();

		$result = $this->db->pquery('SELECT tabid, tablename, columnname FROM vtiger_field WHERE fieldid IN (
			SELECT fieldid FROM vtiger_fieldmodulerel WHERE relmodule=?)', [$module]);

		while ($row = $this->db->fetch_array($result)) {
			$tabId = $row['tabid'];
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];

			$relatedModule = vtlib\Functions::getModuleName($tabId);
			$focusObj = CRMEntity::getInstance($relatedModule);

			//Backup Field Relations for the deleted entity
			$targetTableColumn = $focusObj->tab_name_index[$tableName];
			//While deleting product record the $targetTableColumn should 'id'.
			if ($tableName == 'vtiger_inventoryproductrel') {
				$targetTableColumn = 'id';
			}
			$relQuery = "SELECT $targetTableColumn FROM $tableName WHERE $columnName=?";
			$relResult = $this->db->pquery($relQuery, array($id));
			$numOfRelRecords = $this->db->num_rows($relResult);
			if ($numOfRelRecords > 0) {
				$recordIdsList = [];
				for ($k = 0; $k < $numOfRelRecords; $k++) {
					$recordIdsList[] = $this->db->query_result($relResult, $k, $focusObj->table_index);
				}
				$params = [
					'entityid' => $id,
					'action' => RB_RECORD_UPDATED,
					'rel_table' => $tableName,
					'rel_column' => $columnName,
					'ref_column' => $focusObj->table_index,
					'related_crm_ids' => implode(",", $recordIdsList)
				];
				$this->db->insert('vtiger_relatedlists_rb', $params);
			}
		}
	}

	/** Function to unlink an entity with given Id from another entity */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		global $currentModule;
		$log = LoggerManager::getInstance();
		switch ($relatedName) {
			case 'get_many_to_many':
				$this->deleteRelatedM2M($currentModule, $id, $returnModule, $returnId);
				break;
			case 'get_dependents_list':
				$this->deleteRelatedDependent($currentModule, $id, $returnModule, $returnId);
				break;
			case 'get_related_list':
				$this->deleteRelatedFromDB($currentModule, $id, $returnModule, $returnId);
				break;
			default:
				$this->deleteRelatedDependent($currentModule, $id, $returnModule, $returnId);
				$this->deleteRelatedFromDB($currentModule, $id, $returnModule, $returnId);
				break;
		}
	}

	public function deleteRelatedDependent($module, $crmid, $withModule, $withCrmid)
	{
		$fieldRes = $this->db->pquery('SELECT vtiger_field.tabid, vtiger_field.tablename, vtiger_field.columnname, vtiger_tab.name FROM vtiger_field LEFT JOIN vtiger_tab ON vtiger_tab.`tabid` = vtiger_field.`tabid` WHERE fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module=? && relmodule=?)', [$module, $withModule]);
		$numOfFields = $this->db->getRowCount($fieldRes);
		while ($row = $this->db->getRow($fieldRes)) {
			$focusObj = CRMEntity::getInstance($row['name']);
			$columnName = $row['columnname'];
			$columns = [$columnName => null];
			$where = "$columnName = ? && $focusObj->table_index = ?";
			$this->db->update($row['tablename'], $columns, $where, [$withCrmid, $crmid]);
		}
	}

	public function deleteRelatedM2M($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($module, $withModule);
		$db->delete($referenceInfo['table'], $referenceInfo['base'] . ' = ? && ' . $referenceInfo['rel'] . ' = ?', [$withCrmid, $crmid]);
	}

	public function deleteRelatedFromDB($module, $crmid, $withModule, $withCrmid)
	{
		$where = '(crmid=? && relmodule=? && relcrmid=?) || (relcrmid=? && module=? && crmid=?)';
		$params = [$crmid, $withModule, $withCrmid, $crmid, $withModule, $withCrmid];
		$this->db->delete('vtiger_crmentityrel', $where, $params);
	}

	/** Function to restore a deleted record of specified module with given crmid
	 * @param $module -- module name:: Type varchar
	 * @param $entity_ids -- list of crmids :: Array
	 */
	public function restore($module, $id)
	{
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$db->startTransaction();
		$db->update('vtiger_crmentity', [
			'deleted' => 0,
			'modifiedtime' => date('Y-m-d H:i:s'),
			'modifiedby' => $currentUser->id,
			], 'crmid = ?', [$id]
		);

		//Restore related entities/records
		$this->restoreRelatedRecords($module, $id);

		//Event triggering code
		require_once('include/events/include.inc');
		$em = new VTEventsManager($db);

		// Initialize Event trigger cache
		$em->initTriggerCache();

		$this->id = $id;
		$entityData = VTEntityData::fromCRMEntity($this);
		//Event triggering code
		$em->triggerEvent('vtiger.entity.afterrestore', $entityData);
		//Event triggering code ends

		$db->completeTransaction();
	}

	/** Function to restore all the related records of a given record by id */
	public function restoreRelatedRecords($module, $record)
	{

		$result = $this->db->pquery('SELECT * FROM vtiger_relatedlists_rb WHERE entityid = ?', array($record));
		$numRows = $this->db->num_rows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$action = $this->db->query_result($result, $i, "action");
			$rel_table = $this->db->query_result($result, $i, "rel_table");
			$rel_column = $this->db->query_result($result, $i, "rel_column");
			$ref_column = $this->db->query_result($result, $i, "ref_column");
			$related_crm_ids = $this->db->query_result($result, $i, "related_crm_ids");

			if (strtoupper($action) == RB_RECORD_UPDATED) {
				$related_ids = explode(",", $related_crm_ids);
				if ($rel_table == 'vtiger_crmentity' && $rel_column == 'deleted') {
					$this->db->update($rel_table, [$rel_column => 0], "$ref_column IN (" . generateQuestionMarks($related_ids) . ")", [$related_ids]);
				} else {
					$this->db->update($rel_table, [$rel_column => $record], "$rel_column = 0 && $ref_column IN (" . generateQuestionMarks($related_ids) . ")", [$related_ids]);
				}
			} elseif (strtoupper($action) == RB_RECORD_DELETED) {
				if ($rel_table == 'vtiger_seproductrel') {
					$params = [$rel_column => $record, $ref_column => $related_crm_ids, 'setype' => $module];
					$this->db->insert($rel_table, $params);
				} else {
					$params = [$rel_column => $record, $ref_column => $related_crm_ids];
					$this->db->insert($rel_table, $params);
				}
			}
		}

		//Clean up the the backup data also after restoring
		$this->db->delete('vtiger_relatedlists_rb', 'entityid = ?', [$record]);
	}

	/**
	 * Function to initialize the sortby fields array
	 */
	public function initSortByField($module)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering function initSortByField ($module)");
		// Define the columnname's and uitype's which needs to be excluded
		$exclude_columns = Array('parent_id', 'vendorid', 'access_count');
		$exclude_uitypes = [];

		$tabid = \includes\Modules::getModuleId($module);
		if ($module == 'Calendar') {
			$tabid = array('9', '16');
		}
		$sql = "SELECT columnname FROM vtiger_field " .
			" WHERE (fieldname not like '%\_id' || fieldname in ('assigned_user_id'))" .
			" && tabid in (" . generateQuestionMarks($tabid) . ") and vtiger_field.presence in (0,2)";
		$params = array($tabid);
		if (count($exclude_columns) > 0) {
			$sql .= " && columnname NOT IN (" . generateQuestionMarks($exclude_columns) . ")";
			array_push($params, $exclude_columns);
		}
		if (count($exclude_uitypes) > 0) {
			$sql .= " && uitype NOT IN (" . generateQuestionMarks($exclude_uitypes) . ")";
			array_push($params, $exclude_uitypes);
		}
		$result = $adb->pquery($sql, $params);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$columnname = $adb->query_result($result, $i, 'columnname');
			if (in_array($columnname, $this->sortby_fields))
				continue;
			else
				$this->sortby_fields[] = $columnname;
		}
		if ($tabid == 21 || $tabid == 22)
			$this->sortby_fields[] = 'crmid';
		$log->debug("Exiting initSortByField");
	}
	/* Function to check if the mod number already exits */

	public function checkModuleSeqNumber($table, $column, $no)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery(sprintf("SELECT %s FROM *s WHERE %s = ?", $adb->sql_escape_string($column), $adb->sql_escape_string($table), $adb->sql_escape_string($column)), [$no]);
		$num_rows = $adb->num_rows($result);
		if ($num_rows > 0)
			return true;
		else
			return false;
	}

	// END

	public function updateMissingSeqNumber($module)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entered updateMissingSeqNumber function");

		vtlib_setup_modulevars($module, $this);
		$tabid = \includes\Modules::getModuleId($module);
		if (!\includes\fields\RecordNumber::isModuleSequenceConfigured($tabid))
			return;
		$fieldinfo = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ? && uitype = 4", Array($tabid));

		$returninfo = [];

		if ($fieldinfo && $adb->num_rows($fieldinfo)) {
			$fld_table = $adb->query_result($fieldinfo, 0, 'tablename');
			$fld_column = $adb->query_result($fieldinfo, 0, 'columnname');

			if ($fld_table == $this->table_name) {
				$records = $adb->query("SELECT $this->table_index AS recordid FROM $this->table_name " .
					"WHERE $fld_column = '' || $fld_column is NULL");

				if ($records && $adb->num_rows($records)) {
					$returninfo['totalrecords'] = $adb->num_rows($records);
					$returninfo['updatedrecords'] = 0;
					$moduleData = \includes\fields\RecordNumber::getNumber($tabid);
					$sequenceNumber = $moduleData['sequenceNumber'];
					$prefix = $moduleData['prefix'];
					$postfix = $moduleData['postfix'];
					$oldNumber = $sequenceNumber;
					while ($recordinfo = $adb->getRow($records)) {
						$recordNumber = \includes\fields\RecordNumber::parse($prefix . $sequenceNumber . $postfix);
						$adb->update($fld_table, [$fld_column => $recordNumber], $this->table_index . ' = ?', [$recordinfo['recordid']]);
						$sequenceNumber += 1;
						$returninfo['updatedrecords'] = $returninfo['updatedrecords'] + 1;
					}
					if ($oldNumber != $sequenceNumber) {
						\includes\fields\RecordNumber::updateNumber($sequenceNumber, $tabid);
					}
				}
			} else {
				$log->fatal("Updating Missing Sequence Number FAILED! REASON: Field table and module table mismatching.");
			}
		}
		return $returninfo;
	}
	/* Generic function to get attachments in the related list of a given module */

	public function get_attachments($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		global $currentModule, $singlepane_view;
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);
		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true')
			$returnset = "&return_module=$this_module&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$this_module&return_action=CallRelatedList&return_id=$id";

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name',
				'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "select case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name," .
			"'Documents' ActivityType,vtiger_attachments.type  FileType,crm2.modifiedtime lastmodified,vtiger_crmentity.modifiedtime,
				vtiger_seattachmentsrel.attachmentsid attachmentsid, vtiger_crmentity.smownerid smownerid, vtiger_notes.notesid crmid,
				vtiger_notes.notecontent description,vtiger_notes.*
				from vtiger_notes
				inner join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid
				left join vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid
				inner join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0
				inner join vtiger_crmentity crm2 on crm2.crmid=vtiger_senotesrel.crmid
				LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_seattachmentsrel  on vtiger_seattachmentsrel.crmid =vtiger_notes.notesid
				left join vtiger_attachments on vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				left join vtiger_users on vtiger_crmentity.smownerid= vtiger_users.id
				where crm2.crmid=" . $id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;
		return $return_value;
	}

	/**
	 * For Record View Notification
	 */
	public function isViewed($crmid = false)
	{
		if (!$crmid) {
			$crmid = $this->id;
		}
		if ($crmid) {
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery("SELECT viewedtime,modifiedtime,smcreatorid,smownerid,modifiedby FROM vtiger_crmentity WHERE crmid=?", Array($crmid));
			$resinfo = $adb->fetch_array($result);

			$lastviewed = $resinfo['viewedtime'];
			$modifiedon = $resinfo['modifiedtime'];
			$smownerid = $resinfo['smownerid'];
			$smcreatorid = $resinfo['smcreatorid'];
			$modifiedby = $resinfo['modifiedby'];

			if ($modifiedby == '0' && ($smownerid == $smcreatorid)) {
				/** When module record is created * */
				return true;
			} else if ($smownerid == $modifiedby) {
				/** Owner and Modifier as same. * */
				return true;
			} else if ($lastviewed && $modifiedon) {
				/** Lastviewed and Modified time is available. */
				if ($this->__timediff($modifiedon, $lastviewed) > 0)
					return true;
			}
		}
		return false;
	}

	public function __timediff($d1, $d2)
	{
		list($t1_1, $t1_2) = explode(' ', $d1);
		list($t1_y, $t1_m, $t1_d) = explode('-', $t1_1);
		list($t1_h, $t1_i, $t1_s) = explode(':', $t1_2);

		$t1 = mktime($t1_h, $t1_i, $t1_s, $t1_m, $t1_d, $t1_y);

		list($t2_1, $t2_2) = explode(' ', $d2);
		list($t2_y, $t2_m, $t2_d) = explode('-', $t2_1);
		list($t2_h, $t2_i, $t2_s) = explode(':', $t2_2);

		$t2 = mktime($t2_h, $t2_i, $t2_s, $t2_m, $t2_d, $t2_y);

		if ($t1 == $t2)
			return 0;
		return $t2 - $t1;
	}

	public function markAsViewed($userid)
	{
		$adb = PearDatabase::getInstance();
		$adb->update('vtiger_crmentity', ['viewedtime' => date('Y-m-d H:i:s')], 'crmid = ? && smownerid = ?', [$this->id, $userid]);
	}

	/**
	 * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 * @param String function name
	 */
	public function save_related_module($module, $crmid, $withModule, $withCrmid, $relatedName = false)
	{
		if (!is_array($withCrmid))
			$withCrmid = [$withCrmid];
		switch ($relatedName) {
			case 'get_many_to_many':
				$this->saveRelatedM2M($module, $crmid, $withModule, $withCrmid);
				break;
			case 'get_dependents_list':
				break;
			default:
				$this->saveRelatedToDB($module, $crmid, $withModule, $withCrmid);
				break;
		}
	}

	public function saveRelatedM2M($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($module, $withModule);

		foreach ($withCrmid as $relcrmid) {
			$check = $db->pquery(sprintf('SELECT 1 FROM `%s` WHERE %s = ? && %s = ?', $referenceInfo['table'], $referenceInfo['base'], $referenceInfo['rel']), [$relcrmid, $crmid]);
			// Relation already exists? No need to add again
			if ($check && $db->getRowCount($check))
				continue;
			$db->insert($referenceInfo['table'], [
				$referenceInfo['base'] => $relcrmid,
				$referenceInfo['rel'] => $crmid
			]);
		}
	}

	public function saveRelatedToDB($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		foreach ($withCrmid as $relcrmid) {
			if ($withModule == 'Documents') {
				$checkpresence = $db->pquery('SELECT crmid FROM vtiger_senotesrel WHERE crmid = ? && notesid = ?', [$crmid, $relcrmid]);
				// Relation already exists? No need to add again
				if ($checkpresence && $db->getRowCount($checkpresence))
					continue;

				$db->insert('vtiger_senotesrel', [
					'crmid' => $crmid,
					'notesid' => $relcrmid
				]);
			} else {
				$checkpresence = $db->pquery('SELECT crmid FROM vtiger_crmentityrel WHERE crmid = ? && module = ? && relcrmid = ? && relmodule = ?', [$crmid, $module, $relcrmid, $withModule]
				);
				// Relation already exists? No need to add again
				if ($checkpresence && $db->getRowCount($checkpresence))
					continue;

				$db->insert('vtiger_crmentityrel', [
					'crmid' => $crmid,
					'module' => $module,
					'relcrmid' => $relcrmid,
					'relmodule' => $withModule,
					'rel_created_user' => $currentUserModel->getId(),
					'rel_created_time' => date('Y-m-d H:i:s')
				]);
			}
		}
	}

	/**
	 * Delete the related module record information. Triggered from updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 */
	public function delete_related_module($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		if (!is_array($withCrmid))
			$withCrmid = Array($withCrmid);
		foreach ($withCrmid as $relcrmid) {

			if ($withModule == 'Documents') {
				$db->delete('vtiger_senotesrel', 'crmid=? && notesid=?', [$crmid, $relcrmid]);
			} else {
				$db->delete('vtiger_crmentityrel', '(crmid=? && module=? && relcrmid=? && relmodule=?) || (relcrmid=? && relmodule=? && crmid=? && module=?)', [$crmid, $module, $relcrmid, $withModule, $crmid, $module, $relcrmid, $withModule]
				);
			}
		}
	}

	/**
	 * Default (generic) function to handle the related list for the module.
	 * NOTE: vtlib\Module::setRelatedList sets reference to this function in vtiger_relatedlists table
	 * if function name is not explicitly specified.
	 */
	public function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		global $currentModule, $singlepane_view;

		$current_module = vtlib\Functions::getModuleName($cur_tab_id);
		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($current_module, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;

		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' " .
					" type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$current_module&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\"" .
					" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module, $related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname, $related_module) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true')
			$returnset = "&return_module=$current_module&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$current_module&return_action=CallRelatedList&return_id=$id";

		$query = "SELECT vtiger_crmentity.*, $other->table_name.*";

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name',
				'last_name' => 'vtiger_users.last_name'), 'Users');
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";

		$more_relation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$query .= ", $tname.*";

				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}

		$query .= " FROM $other->table_name";
		$query .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $other->table_name.$other->table_index";
		$query .= " INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid || vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)";
		$query .= $more_relation;
		$query .= " LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " WHERE vtiger_crmentity.deleted = 0 && (vtiger_crmentityrel.crmid = $id || vtiger_crmentityrel.relcrmid = $id)";
		$return_value = GetRelatedList($current_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/**
	 * Default (generic) function to handle the dependents list for the module.
	 * NOTE: UI type '10' is used to stored the references to other modules for a given record.
	 * These dependent records can be retrieved through this function.
	 * For eg: A trouble ticket can be related to an Account or a Contact.
	 * From a given Contact/Account if we need to fetch all such dependent trouble tickets, get_dependents_list function can be used.
	 */
	public function get_dependents_list($id, $cur_tab_id, $relTabId, $actions = false)
	{
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');

		$currentModule = vtlib\Functions::getModuleName($cur_tab_id);
		$relatedModule = vtlib\Functions::getModuleName($relTabId);
		$other = CRMEntity::getInstance($relatedModule);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($relatedModule, $other);

		$singular_modname = 'SINGLE_' . $relatedModule;

		$button = '';
		$row = [];

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true')
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$return_value = null;

		$dependentFieldSql = $this->db->pquery('SELECT tabid, fieldname, columnname, tablename FROM vtiger_field WHERE uitype = 10 AND' .
			' fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE relmodule=? && module=?)', [$currentModule, $relatedModule]);
		if ($dependentFieldSql->rowCount()) {
			$row = $this->db->getRow($dependentFieldSql);
		} else {
			$depProcessFieldSql = $this->db->pquery('SELECT fieldname AS `name`, fieldid AS id, fieldlabel AS label, columnname AS `column`, tablename AS `table`, vtiger_field.*  FROM vtiger_field WHERE `uitype` IN (66,67,68) && `tabid` = ?;', [$relTabId]);
			while ($rowProc = $this->db->getRow($depProcessFieldSql)) {
				$className = Vtiger_Loader::getComponentClassName('Model', 'Field', $relatedModule);
				$fieldModel = new $className();
				foreach ($rowProc as $properName => $propertyValue) {
					$fieldModel->$properName = $propertyValue;
				}
				$moduleList = $fieldModel->getUITypeModel()->getReferenceList();
				if (!empty($moduleList) && in_array($currentModule, $moduleList)) {
					$row = $rowProc;
					break;
				}
			}
		}

		if (!empty($row)) {
			$dependentColumn = $row['columnname'];
			$dependentField = $row['fieldname'];

			$button .= '<input type="hidden" name="' . $dependentColumn . '" id="' . $dependentColumn . '" value="' . $id . '">';
			$button .= '<input type="hidden" name="' . $dependentColumn . '_type" id="' . $dependentColumn . '_type" value="' . $currentModule . '">';
			if ($actions) {
				if (is_string($actions))
					$actions = explode(',', strtoupper($actions));
				if (in_array('ADD', $actions) && isPermitted($relatedModule, 1, '') == 'yes' && getFieldVisibilityPermission($relatedModule, $current_user->id, $dependentField, 'readwrite') == '0') {
					$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname, $relatedModule) . "' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$relatedModule\"' type='submit' name='button'" .
						" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname, $relatedModule) . "'>&nbsp;";
				}
			}
			$query = $this->createDependentQuery($other, $row, $id);
			$return_value = GetRelatedList($currentModule, $relatedModule, $other, $query, $button, $returnset);
		}
		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	public function createDependentQuery($other, $row, $id)
	{
		$dependentColumn = $row['columnname'];
		$dependentTable = $row['tablename'];
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
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name',
				'last_name' => 'vtiger_users.last_name'), 'Users');
		$query .= $tables;
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";
		$query .= sprintf(' FROM %s', $other->table_name);
		$query .= $join;
		$query .= " INNER JOIN $this->table_name ON $this->table_name.$this->table_index = $dependentTable.$dependentColumn";
		$query .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= " WHERE vtiger_crmentity.deleted = 0 && $this->table_name.$this->table_index = $id";
		return $query;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$db = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$relTables = $this->setRelationTables();
		if (key_exists('Documents', $relTables)) {
			$relTables['Attachments'] = ['vtiger_seattachmentsrel' => ['crmid', 'attachmentsid']];
		}
		foreach ($transferEntityIds as &$transferId) {
			// Pick the records related to the entity to be transfered, but do not pick the once which are already related to the current entity.
			$relatedRecords = $db->pquery('SELECT relcrmid, relmodule FROM vtiger_crmentityrel WHERE crmid=? && module=?' .
				' && relcrmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid=? && module=?)', array($transferId, $module, $entityId, $module));
			while ($row = $db->getRow($relatedRecords)) {
				$where = 'relcrmid = ? && relmodule = ? && crmid = ? && module = ?';
				$params = [$row['relcrmid'], $row['relmodule'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['crmid' => $entityId], $where, $params);
			}
			// Pick the records to which the entity to be transfered is related, but do not pick the once to which current entity is already related.
			$parentRecords = $db->pquery('SELECT crmid, module FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?' .
				' && crmid NOT IN (SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?)', array($transferId, $module, $entityId, $module));
			while ($row = $db->getRow($parentRecords)) {
				$where = 'crmid = ? && module = ? && relcrmid = ? && relmodule = ?';
				$params = [$row['crmid'], $row['module'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['relcrmid' => $entityId], $where, $params);
			}

			$db->update('vtiger_modtracker_basic', ['crmid' => $entityId], 'crmid = ? && status <> ?', [$transferId, 7]);
			foreach ($relTables as &$relTable) {
				$idField = current($relTable)[1];
				$entityIdField = current($relTable)[0];
				$relTableName = key($relTable);
				// IN clause to avoid duplicate entries
				$sql = "SELECT $idField FROM $relTableName WHERE $entityIdField = ? " .
					" && $idField NOT IN ( SELECT $idField FROM $relTableName WHERE $entityIdField = ? )";
				$selResult = $db->pquery($sql, [$transferId, $entityId]);
				if ($db->getRowCount($selResult) > 0) {
					while (($idFieldValue = $db->getSingleValue($selResult)) !== false) {
						$db->update($relTableName, [
							$entityIdField => $entityId
							], "$entityIdField = ? and $idField = ?", [$transferId, $idFieldValue]
						);
					}
				}
			}
			$fields = Vtiger_ModulesHierarchy_Model::getRelatedField($module);
			foreach ($fields as &$field) {
				$columnName = $field['columnname'];
				$db->update($field['tablename'], [
					$columnName => $entityId
					], "$columnName = ?", [$transferId]
				);
			}
		}
		$log->debug('Exiting transferRelatedRecords...');
	}
	/*
	 * Function to get the primary query part of a report for which generateReportsQuery Doesnt exist in module
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, $queryPlanner)
	{
		$adb = PearDatabase::getInstance();
		$primary = CRMEntity::getInstance($module);

		vtlib_setup_modulevars($module, $primary);
		$moduletable = $primary->table_name;
		$moduleindex = $primary->table_index;
		$modulecftable = $primary->customFieldTable[0];
		$modulecfindex = $primary->customFieldTable[1];
		$joinTables = [$moduletable, 'vtiger_crmentity'];

		if (isset($modulecftable) && $queryPlanner->requireTable($modulecftable)) {
			$joinTables[] = $modulecftable;
			$cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
		} else {
			$cfquery = '';
		}
		foreach ($primary->tab_name_index as $table => $index) {
			if (in_array($table, $joinTables) || !$queryPlanner->requireTable($table)) {
				continue;
			}
			$joinTables[] = $table;
			$cfquery .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $index . ' = ' . $primary->table_name . '.' . $primary->table_index;
		}

		$relquery = '';
		$matrix = $queryPlanner->newDependencyMatrix();

		$fields_query = $adb->pquery("SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid && vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)", [$module]);

		if ($adb->num_rows($fields_query) > 0) {
			for ($i = 0; $i < $adb->num_rows($fields_query); $i++) {
				$field_name = $adb->query_result($fields_query, $i, 'fieldname');
				$field_id = $adb->query_result($fields_query, $i, 'fieldid');
				$tab_name = $adb->query_result($fields_query, $i, 'tablename');
				$ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", [$field_id]);

				if ($adb->num_rows($ui10_modules_query) > 0) {

					// Capture the forward table dependencies due to dynamic related-field
					$crmentityRelModuleFieldTable = "vtiger_crmentityRel$module$field_id";

					$crmentityRelModuleFieldTableDeps = [];
					for ($j = 0; $j < $adb->num_rows($ui10_modules_query); $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$crmentityRelModuleFieldTableDeps[] = $rel_tab_name . "Rel$module$field_id";
					}

					$matrix->setDependency($crmentityRelModuleFieldTable, $crmentityRelModuleFieldTableDeps);
					$matrix->addDependency($tab_name, $crmentityRelModuleFieldTable);

					if ($queryPlanner->requireTable($crmentityRelModuleFieldTable, $matrix)) {
						$relquery.= " left join vtiger_crmentity as $crmentityRelModuleFieldTable on $crmentityRelModuleFieldTable.crmid = $tab_name.$field_name and vtiger_crmentityRel$module$field_id.deleted=0";
					}

					for ($j = 0; $j < $adb->num_rows($ui10_modules_query); $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;

						$rel_tab_name_rel_module_table_alias = $rel_tab_name . "Rel$module$field_id";

						if ($queryPlanner->requireTable($rel_tab_name_rel_module_table_alias)) {
							$relquery.= " left join $rel_tab_name as $rel_tab_name_rel_module_table_alias  on $rel_tab_name_rel_module_table_alias.$rel_tab_index = $crmentityRelModuleFieldTable.crmid";
						}
					}
				}
			}
		}

		$query = "from $moduletable inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex";

		// Add the pre-joined custom table query
		$query .= " " . "$cfquery";

		if ($queryPlanner->requireTable('vtiger_groups' . $module)) {
			$query .= " left join vtiger_groups as vtiger_groups" . $module . " on vtiger_groups" . $module . ".groupid = vtiger_crmentity.smownerid";
		}

		if ($queryPlanner->requireTable('vtiger_users' . $module)) {
			$query .= " left join vtiger_users as vtiger_users" . $module . " on vtiger_users" . $module . ".id = vtiger_crmentity.smownerid";
		}
		if ($queryPlanner->requireTable('vtiger_lastModifiedBy' . $module)) {
			$query .= " left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby";
		}
		if ($queryPlanner->requireTable('vtiger_createdby' . $module)) {
			$query .= " left join vtiger_users as vtiger_createdby" . $module . " on vtiger_createdby" . $module . ".id = vtiger_crmentity.smcreatorid";
		}

		$query .= "	left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

		// Add the pre-joined relation table query
		$query .= " " . $relquery;

		return $query;
	}
	/*
	 * Function to get the secondary query part of a report for which generateReportsSecQuery Doesnt exist in module
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{
		$adb = PearDatabase::getInstance();
		$secondary = CRMEntity::getInstance($secmodule);

		vtlib_setup_modulevars($secmodule, $secondary);

		$tablename = $secondary->table_name;
		$tableindex = $secondary->table_index;
		$modulecftable = $secondary->customFieldTable[0];
		$modulecfindex = $secondary->customFieldTable[1];

		if (isset($modulecftable) && $queryPlanner->requireTable($modulecftable)) {
			$cfquery = "left join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$tablename.$tableindex";
		} else {
			$cfquery = '';
		}

		$relquery = '';
		$matrix = $queryPlanner->newDependencyMatrix();

		$fields_query = $adb->pquery("SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid && vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)", array($secmodule));

		if ($adb->num_rows($fields_query) > 0) {
			for ($i = 0; $i < $adb->num_rows($fields_query); $i++) {
				$field_name = $adb->query_result($fields_query, $i, 'fieldname');
				$field_id = $adb->query_result($fields_query, $i, 'fieldid');
				$tab_name = $adb->query_result($fields_query, $i, 'tablename');
				$ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", array($field_id));

				if ($adb->num_rows($ui10_modules_query) > 0) {
					// Capture the forward table dependencies due to dynamic related-field
					$crmentityRelSecModuleTable = "vtiger_crmentityRel$secmodule$field_id";

					$crmentityRelSecModuleTableDeps = [];
					for ($j = 0; $j < $adb->num_rows($ui10_modules_query); $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$crmentityRelSecModuleTableDeps[] = $rel_tab_name . "Rel$secmodule";
					}

					$matrix->setDependency($crmentityRelSecModuleTable, $crmentityRelSecModuleTableDeps);
					$matrix->addDependency($tab_name, $crmentityRelSecModuleTable);

					if ($queryPlanner->requireTable($crmentityRelSecModuleTable, $matrix)) {
						$relquery .= " left join vtiger_crmentity as $crmentityRelSecModuleTable on $crmentityRelSecModuleTable.crmid = $tab_name.$field_name and $crmentityRelSecModuleTable.deleted=0";
					}
					for ($j = 0; $j < $adb->num_rows($ui10_modules_query); $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;

						$rel_tab_name_rel_secmodule_table_alias = $rel_tab_name . "Rel$secmodule";

						if ($queryPlanner->requireTable($rel_tab_name_rel_secmodule_table_alias)) {
							$relquery .= " left join $rel_tab_name as $rel_tab_name_rel_secmodule_table_alias on $rel_tab_name_rel_secmodule_table_alias.$rel_tab_index = $crmentityRelSecModuleTable.crmid";
						}
					}
				}
			}
		}

		// Update forward table dependencies
		$matrix->setDependency("vtiger_crmentity$secmodule", array("vtiger_groups$secmodule", "vtiger_users$secmodule", "vtiger_lastModifiedBy$secmodule"));
		$matrix->addDependency($tablename, "vtiger_crmentity$secmodule");

		if (!$queryPlanner->requireTable($tablename, $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "$tablename", "$tableindex", $queryPlanner);

		if ($queryPlanner->requireTable("vtiger_crmentity$secmodule", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentity$secmodule on vtiger_crmentity$secmodule.crmid = $tablename.$tableindex && vtiger_crmentity$secmodule.deleted=0";
		}

		// Add the pre-joined custom table query
		$query .= " " . $cfquery;

		if ($queryPlanner->requireTable("vtiger_groups$secmodule")) {
			$query .= " left join vtiger_groups as vtiger_groups" . $secmodule . " on vtiger_groups" . $secmodule . ".groupid = vtiger_crmentity$secmodule.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_users$secmodule")) {
			$query .= " left join vtiger_users as vtiger_users" . $secmodule . " on vtiger_users" . $secmodule . ".id = vtiger_crmentity$secmodule.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedBy$secmodule")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedBy" . $secmodule . " on vtiger_lastModifiedBy" . $secmodule . ".id = vtiger_crmentity" . $secmodule . ".modifiedby";
		}
		if ($queryPlanner->requireTable("vtiger_createdby$secmodule")) {
			$query .= " left join vtiger_users as vtiger_createdby" . $secmodule . " on vtiger_createdby" . $secmodule . ".id = vtiger_crmentity" . $secmodule . ".modifiedby";
		}

		// Add the pre-joined relation table query
		$query .= " " . $relquery;

		return $query;
	}
	/*
	 * Function to get the security query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for security of the module
	 */

	public function getListViewSecurityParameter($module)
	{
		$tabid = \includes\Modules::getModuleId($module);
		$current_user = vglobal('current_user');
		if ($current_user) {
			require('user_privileges/user_privileges_' . $current_user->id . '.php');
			require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
		}
		$sec_query = '';
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {
			$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid
					in (select vtiger_user2role.userid from vtiger_user2role
							inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid
							inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid
							where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') or vtiger_crmentity.smownerid
					in(select shareduserid from vtiger_tmp_read_user_sharing_per
						where userid=" . $current_user->id . " and tabid=" . $tabid . ") or (";
			if (sizeof($current_user_groups) > 0) {
				$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
			}
			$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid
						from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
		}
		return $sec_query;
	}
	/*
	 * Function to get the relation query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on relating the primary module and secondary module
	 */

	public function getRelationQuery($module, $secmodule, $table_name, $column_name, $queryPlanner)
	{
		$tab = getRelationTables($module, $secmodule);

		foreach ($tab as $key => $value) {
			$tables[] = $key;
			$fields[] = $value;
		}
		$pritablename = $tables[0];
		$sectablename = $tables[1];
		$prifieldname = $fields[0][0];
		$secfieldname = $fields[0][1];
		$tmpname = $pritablename . 'tmp' . $secmodule;
		$condition = "";
		if (!empty($tables[1]) && !empty($fields[1])) {
			$condvalue = $tables[1] . "." . $fields[1];
			$condition = "$pritablename.$prifieldname=$condvalue";
		} else {
			$condvalue = $table_name . "." . $column_name;
			$condition = "$pritablename.$secfieldname=$condvalue";
		}

		// Look forward for temporary table usage as defined by the QueryPlanner
		$secQuery = "select $table_name.* from $table_name inner join vtiger_crmentity on " .
			"vtiger_crmentity.crmid=$table_name.$column_name and vtiger_crmentity.deleted=0";

		$secQueryTempTableQuery = $queryPlanner->registerTempTable($secQuery, array($column_name, $fields[1], $prifieldname));

		$query = '';
		if ($pritablename == 'vtiger_crmentityrel') {
			$condition = "($table_name.$column_name={$tmpname}.{$secfieldname} " .
				"OR $table_name.$column_name={$tmpname}.{$prifieldname})";
			$query = " left join vtiger_crmentityrel as $tmpname ON ($condvalue={$tmpname}.{$secfieldname} " .
				"OR $condvalue={$tmpname}.{$prifieldname}) ";
		} elseif (strripos($pritablename, 'rel') === (strlen($pritablename) - 3)) {
			$instance = self::getInstance($module);
			$sectableindex = $instance->tab_name_index[$sectablename];
			$condition = "$table_name.$column_name=$tmpname.$secfieldname";
			if ($pritablename == 'vtiger_senotesrel') {
				$query = " left join $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname
                    && $tmpname.notesid IN (SELECT crmid FROM vtiger_crmentity WHERE setype='Documents' && deleted = 0))";
			} else {
				$query = " left join $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname)";
			}
			if ($secmodule == 'Calendar') {
				$condition .= " && $table_name.activitytype != 'Emails'";
			} else if ($secmodule == 'Leads') {
				$condition .= " && $table_name.converted = 0";
			}
		}

		$query .= " left join $secQueryTempTableQuery as $table_name on {$condition}";
		return $query;
	}
	/** END * */

	/**
	 * This function handles the import for uitype 10 fieldtype
	 * @param string $module - the current module name
	 * @param string fieldname - the related to field name
	 */
	public function add_related_to($module, $fieldname)
	{
		global $imported_ids;
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$related_to = $this->column_fields[$fieldname];

		if (empty($related_to)) {
			return false;
		}

		//check if the field has module information; if not get the first module
		if (!strpos($related_to, "::::")) {
			$module = getFirstModule($module, $fieldname);
			$value = $related_to;
		} else {
			//check the module of the field
			$arr = [];
			$arr = explode("::::", $related_to);
			$module = $arr[0];
			$value = $arr[1];
		}

		$focus1 = CRMEntity::getInstance($module);

		$entityNameArr = \vtlib\Functions::getEntityModuleSQLColumnString($module);
		$entityName = $entityNameArr['fieldname'];
		$query = "SELECT vtiger_crmentity.deleted, $focus1->table_name.*
					FROM $focus1->table_name
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=$focus1->table_name.$focus1->table_index
						where $entityName=? and vtiger_crmentity.deleted=0";
		$result = $adb->pquery($query, array($value));

		if (!isset($this->checkFlagArr[$module])) {
			$this->checkFlagArr[$module] = (isPermitted($module, 'EditView', '') == 'yes');
		}

		if ($adb->num_rows($result) > 0) {
			//record found
			$focus1->id = $adb->query_result($result, 0, $focus1->table_index);
		} elseif ($this->checkFlagArr[$module]) {
			//record not found; create it
			$focus1->column_fields[$focus1->list_link_field] = $value;
			$focus1->column_fields['assigned_user_id'] = $current_user->id;
			$focus1->column_fields['modified_user_id'] = $current_user->id;
			$focus1->save($module);

			$last_import = new UsersLastImport();
			$last_import->assigned_user_id = $current_user->id;
			$last_import->bean_type = $module;
			$last_import->bean_id = $focus1->id;
			$last_import->save();
		} else {
			//record not found and cannot create
			$this->column_fields[$fieldname] = "";
			return false;
		}
		if (!empty($focus1->id)) {
			$this->column_fields[$fieldname] = $focus1->id;
			return true;
		} else {
			$this->column_fields[$fieldname] = "";
			return false;
		}
	}

	/**
	 * To keep track of action of field filtering and avoiding doing more than once.
	 *
	 * @var Array
	 */
	protected $__inactive_fields_filtered = false;

	/**
	 * Filter in-active fields based on type
	 *
	 * @param String $module
	 */
	public function filterInactiveFields($module)
	{
		if ($this->__inactive_fields_filtered) {
			return;
		}

		$adb = PearDatabase::getInstance();
		// Look for fields that has presence value NOT IN (0,2)
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module, array('1'));
		if ($cachedModuleFields === false) {
			// Initialize the fields calling suitable API
			getColumnFields($module);
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module, array('1'));
		}

		$hiddenFields = [];

		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				$fieldLabel = $fieldinfo['fieldlabel'];
				// NOTE: We should not translate the label to enable field diff based on it down
				$fieldName = $fieldinfo['fieldname'];
				$tableName = str_replace("vtiger_", "", $fieldinfo['tablename']);
				$hiddenFields[$fieldLabel] = array($tableName => $fieldName);
			}
		}

		if (isset($this->list_fields)) {
			$this->list_fields = array_diff_assoc($this->list_fields, $hiddenFields);
		}

		if (isset($this->search_fields)) {
			$this->search_fields = array_diff_assoc($this->search_fields, $hiddenFields);
		}

		// To avoid re-initializing everytime.
		$this->__inactive_fields_filtered = true;
	}

	/** END * */
	public function buildSearchQueryForFieldTypes($uitypes, $value = false)
	{
		$adb = PearDatabase::getInstance();

		if (!is_array($uitypes))
			$uitypes = array($uitypes);
		$module = get_class($this);

		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		if ($cachedModuleFields === false) {
			getColumnFields($module); // This API will initialize the cache as well
			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}

		$lookuptables = [];
		$lookupcolumns = [];
		foreach ($cachedModuleFields as $fieldinfo) {
			if (in_array($fieldinfo['uitype'], $uitypes)) {
				$lookuptables[] = $fieldinfo['tablename'];
				$lookupcolumns[] = $fieldinfo['columnname'];
			}
		}

		$entityfields = \vtlib\Functions::getEntityModuleSQLColumnString($module);
		$querycolumnnames = implode(',', $lookupcolumns);
		$entitycolumnnames = $entityfields['fieldname'];
		$query = "select crmid as id, $querycolumnnames, $entitycolumnnames as name ";
		$query .= " FROM $this->table_name ";
		$query .=" INNER JOIN vtiger_crmentity ON $this->table_name.$this->table_index = vtiger_crmentity.crmid && deleted = 0 ";

		//remove the base table
		$LookupTable = array_unique($lookuptables);
		$indexes = array_keys($LookupTable, $this->table_name);
		if (!empty($indexes)) {
			foreach ($indexes as $index) {
				unset($LookupTable[$index]);
			}
		}
		foreach ($LookupTable as $tablename) {
			$query .= " INNER JOIN $tablename
						on $this->table_name.$this->table_index = $tablename." . $this->tab_name_index[$tablename];
		}
		if (!empty($lookupcolumns) && $value !== false) {
			$query .=" WHERE ";
			$i = 0;
			$columnCount = count($lookupcolumns);
			foreach ($lookupcolumns as $columnname) {
				if (!empty($columnname)) {
					if ($i == 0 || $i == ($columnCount))
						$query .= sprintf("%s = '%s'", $columnname, $value);
					else
						$query .= sprintf(" || %s = '%s'", $columnname, $value);
					$i++;
				}
			}
		}
		if ($this->table_name == 'vtiger_leaddetails') {
			$query .= " && $this->table_name.converted = 0 ";
		}
		return $query;
	}

	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName)
	{
		if (strripos($tableName, 'rel') === (strlen($tableName) - 3)) {
			return 'LEFT JOIN';
		} else if ($tableName == 'vtiger_entity_stats' || $tableName == 'u_yf_openstreetmap') {
			return 'LEFT JOIN';
		}
		return 'INNER JOIN';
	}

	public function getUserAccessConditionsQuery($moduleName, $user)
	{
		$userPrivileges = \Vtiger_Util_Helper::getUserPrivilegesFile($user->id);

		$query = '';
		$tabId = \includes\Modules::getModuleId($moduleName);
		if ($userPrivileges['is_admin'] == false && $userPrivileges['profile_global_permission'][1] == 1 && $userPrivileges['profile_global_permission'][2] == 1 && $userPrivileges['defaultOrgSharingPermission'][$tabId] == 3) {
			$parentRoleSeq = $userPrivileges['parent_role_seq'];
			$query .= " vtiger_crmentity.smownerid = '$user->id'";
			if (\AppConfig::security('PERMITTED_BY_ROLES')) {
				$query .= " || vtiger_crmentity.smownerid IN (SELECT vtiger_user2role.userid AS userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole like '$parentRoleSeq::%')";
			}
			if (count($userPrivileges['groups']) > 0) {
				$query .= ' || vtiger_crmentity.smownerid IN (' . implode(',', $userPrivileges['groups']) . ')';
			}
		}
		if (\AppConfig::security('PERMITTED_BY_SHARING') && !empty($moduleName)) {
			$sharingPrivileges = \Vtiger_Util_Helper::getUserSharingFile($user->id);
			if (isset($sharingPrivileges['permission'][$moduleName])) {
				$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];
				$sharingRuleInfo = $sharingPrivilegesModule['read'];
				if (count($sharingRuleInfo['ROLE']) > 0 || count($sharingRuleInfo['GROUP']) > 0) {
					$query .= " || vtiger_crmentity.smownerid IN (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per WHERE userid=$user->id && tabid=$tabId) || vtiger_crmentity.smownerid IN (SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid FROM vtiger_tmp_read_group_sharing_per WHERE userid=$user->id && tabid=$tabId)";
				}
			}
		}
		return $query;
	}

	public function getUserAccessConditionsQuerySR($module, $currentUser = false, $relatedRecord = false)
	{
		if ($currentUser == false)
			$currentUser = vglobal('current_user');

		$userid = $currentUser->id;
		$userPrivileges = \Vtiger_Util_Helper::getUserPrivilegesFile($userid);

		$query = $sharedParameter = $securityParameter = '';
		$tabId = \includes\Modules::getModuleId($module);
		if ($relatedRecord && \AppConfig::security('PERMITTED_BY_RECORD_HIERARCHY')) {
			$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$role = $userModel->getRoleDetail();
			if ($role->get('listrelatedrecord') == 2) {
				$rparentRecord = Users_Privileges_Model::getParentRecord($relatedRecord, false, $role->get('listrelatedrecord'));
				if ($rparentRecord) {
					$relatedRecord = $rparentRecord;
				}
			}
			if ($role->get('listrelatedrecord') != 0) {
				$recordMetaData = vtlib\Functions::getCRMRecordMetadata($relatedRecord);
				$recordPermission = Users_Privileges_Model::isPermitted($recordMetaData['setype'], 'DetailView', $relatedRecord);
				if ($recordPermission) {
					return '';
				}
			}
		}

		if ($userPrivileges['is_admin'] == false && $userPrivileges['profile_global_permission'][1] == 1 && $userPrivileges['profile_global_permission'][2] == 1 && $userPrivileges['defaultOrgSharingPermission'][$tabId] == 3) {
			$securityParameter = $this->getUserAccessConditionsQuery($module, $currentUser);
			$shownerid = array_merge([$userid], $userPrivileges['groups']);
			if (\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
				$sharedParameter .= 'vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid IN (' . implode(',', $shownerid) . '))';
			}
		}
		if (!empty($securityParameter) && !empty($sharedParameter)) {
			$query .= " && (($securityParameter) || ($sharedParameter))";
		} elseif (!empty($sharedParameter)) {
			$query .= " && ($sharedParameter)";
		} elseif (!empty($securityParameter)) {
			$query .= " && ($securityParameter)";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	public function getNonAdminAccessQuery($module, $user, $parentRole, $userGroups)
	{
		$query = $this->getNonAdminUserAccessQuery($user, $parentRole, $userGroups);
		if (!empty($module)) {
			$moduleAccessQuery = $this->getNonAdminModuleAccessQuery($module, $user);
			if (!empty($moduleAccessQuery)) {
				$query .= " UNION $moduleAccessQuery";
			}
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	public function getNonAdminUserAccessQuery($user, $parentRole, $userGroups)
	{
		$query = "(SELECT $user->id as id) UNION (SELECT vtiger_user2role.userid AS userid FROM " .
			"vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid " .
			"INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE " .
			"vtiger_role.parentrole like '$parentRole::%')";
		if (count($userGroups) > 0) {
			$query .= " UNION (SELECT groupid FROM vtiger_groups where" .
				" groupid in (" . implode(",", $userGroups) . "))";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 */
	public function getNonAdminModuleAccessQuery($module, $user)
	{
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$tabId = \includes\Modules::getModuleId($module);
		$sharingRuleInfoVariable = $module . '_share_read_permission';
		$sharingRuleInfo = $$sharingRuleInfoVariable;
		$sharedTabId = null;
		$query = '';
		if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
			count($sharingRuleInfo['GROUP']) > 0)) {
			$query = " (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per " .
				"WHERE userid=$user->id && tabid=$tabId) UNION (SELECT " .
				"vtiger_tmp_read_group_sharing_per.sharedgroupid FROM " .
				"vtiger_tmp_read_group_sharing_per WHERE userid=$user->id && tabid=$tabId)";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups)
	{
		$module = null;
		if (!empty($tabId)) {
			$module = getTabModuleName($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key) ignore " .
			$query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, []);
		if (is_object($result)) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param String $module - module name for which query needs to be generated.
	 * @param Users $user - user for which query needs to be generated.
	 * @return String Access control Query for the user.
	 */
	public function getNonAdminAccessControlQuery($module, $user, $scope = '')
	{
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = \includes\Modules::getModuleId($module);
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . $user->id;
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
				count($sharingRuleInfo['GROUP']) > 0)) {
				$tableName = $tableName . '_t' . $tabId;
				$sharedTabId = $tabId;
			} elseif ($module == 'Calendar' || !empty($scope)) {
				$tableName .= '_t' . $tabId;
			}
			$this->setupTemporaryTable($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);
			// for secondary module we should join the records even if record is not there(primary module without related record)
			if ($scope == '') {
				$query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
					"vtiger_crmentity$scope.smownerid ";
			} else {
				$query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
					"vtiger_crmentity$scope.smownerid || vtiger_crmentity$scope.smownerid IS NULL";
			}
		}
		return $query;
	}

	public function listQueryNonAdminChange($query, $scope = '')
	{
		//make the module base table as left hand side table for the joins,
		//as mysql query optimizer puts crmentity on the left side and considerably slow down
		$query = preg_replace('/\s+/', ' ', $query);
		if (strripos($query, ' WHERE ') !== false) {
			vtlib_setup_modulevars($this->moduleName, $this);
			$query = str_ireplace(' where ', " WHERE $this->table_name.$this->table_index > 0  && ", $query);
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param String $secmodule - $secmodule secondary module name
	 * @return Array returns the array with table names and fieldnames storing relations
	 * between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'Documents' => [
				'vtiger_senotesrel' => ['crmid', 'notesid'],
				$this->table_name => $this->table_index
			]
		];
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	/**
	 * Function to clear the fields which needs to be saved only once during the Save of the record
	 * For eg: Comments of HelpDesk should be saved only once during one save of a Trouble Ticket
	 */
	public function clearSingletonSaveFields()
	{
		return;
	}

	/**
	 * Function to track when a new record is linked to a given record
	 */
	public static function trackLinkedInfo($crmid)
	{
		$current_user = vglobal('current_user');
		$adb = PearDatabase::getInstance();
		$currentTime = date('Y-m-d H:i:s');

		$adb->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => $current_user->id], 'crmid = ?', [$crmid]);
	}

	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	public function getSortOrder()
	{
		$log = LoggerManager::getInstance();
		$currentModule = vglobal('currentModule');
		$log->debug("Entering getSortOrder() method ...");
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::getForSql('sorder'));
		else
			$sorder = (($_SESSION[$currentModule . '_Sort_Order'] != '') ? ($_SESSION[$currentModule . '_Sort_Order']) : ($this->default_sort_order));
		$log->debug("Exiting getSortOrder() method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'accountname')
	 */
	public function getOrderBy()
	{
		global $currentModule;
		$log = LoggerManager::getInstance();
		$log->debug("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::getForSql('order_by'));
		else
			$order_by = (($_SESSION[$currentModule . '_Order_By'] != '') ? ($_SESSION[$currentModule . '_Order_By']) : ($use_default_order_by));
		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}
	// Mike Crowe Mod --------------------------------------------------------

	/**
	 * Function to track when a record is unlinked to a given record
	 */
	public function trackUnLinkedInfo($module, $crmid, $with_module, $with_crmid)
	{
		$current_user = vglobal('current_user');
		$adb = PearDatabase::getInstance();
		$currentTime = date('Y-m-d H:i:s');

		$adb->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => $current_user->id], 'crmid = ?', [$crmid]);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 * @param <String> $module
	 * @param <String> $tableColumns
	 * @param <String> $selectedColumns
	 * @param <Boolean> $ignoreEmpty
	 * @return string
	 */
	public function getQueryForDuplicates($module, $tableColumns, $selectedColumns = '', $ignoreEmpty = false, $additionalColumns = '')
	{
		if (is_array($tableColumns)) {
			$tableColumnsString = implode(',', $tableColumns);
		}
		if (is_array($additionalColumns)) {
			$additionalColumns = implode(',', $additionalColumns);
		}
		if (!empty($additionalColumns)) {
			$additionalColumns = ',' . $additionalColumns;
		}
		$selectClause = sprintf('SELECT %s.%s AS recordid,%s%s', $this->table_name, $this->table_index, $tableColumnsString, $additionalColumns);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$fromClause = " FROM $this->table_name";

		$fromClause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		if ($this->tab_name) {
			foreach ($this->tab_name as $tableName) {
				if ($tableName != 'vtiger_crmentity' && $tableName != $this->table_name && $tableName != 'vtiger_inventoryproductrel') {
					if ($this->tab_name_index[$tableName]) {
						$fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
							" = $this->table_name.$this->table_index";
					}
				}
			}
		}
		$fromClause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$whereClause = " WHERE vtiger_crmentity.deleted = 0";
		$whereClause .= $this->getListViewSecurityParameter($module);

		if ($ignoreEmpty) {
			foreach ($tableColumns as $tableColumn) {
				$whereClause .= " && ($tableColumn IS NOT NULL && $tableColumn != '') ";
			}
		}

		if (isset($selectedColumns) && trim($selectedColumns) != '') {
			$sub_query = "SELECT $selectedColumns FROM $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $selectedColumns HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $tableColumnsString $additionalColumns $fromClause $whereClause GROUP BY $tableColumnsString HAVING COUNT(*)>1";
		}

		$i = 1;
		foreach ($tableColumns as $tableColumn) {
			$tableInfo = explode('.', $tableColumn);
			$duplicateCheckClause .= " ifnull($tableColumn,'null') = ifnull(temp.$tableInfo[1],'null')";
			if (count($tableColumns) != $i++)
				$duplicateCheckClause .= ' && ';
		}

		$query = $selectClause . $fromClause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . $duplicateCheckClause .
			$whereClause .
			" ORDER BY $tableColumnsString," . $this->table_name . "." . $this->table_index . " ASC";
		return $query;
	}

	public function getLockFields()
	{
		if (isset($this->lockFields)) {
			return $this->lockFields;
		}
		return false;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String $moduleName Module name
	 * @param String $eventType Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		
	}
}
