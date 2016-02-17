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

class OSSTimeControl extends Vtiger_CRMEntity
{

	var $table_name = 'vtiger_osstimecontrol';
	var $table_index = 'osstimecontrolid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_osstimecontrolcf', 'osstimecontrolid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_osstimecontrol', 'vtiger_osstimecontrolcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_osstimecontrol' => 'osstimecontrolid',
		'vtiger_osstimecontrolcf' => 'osstimecontrolid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'No.' => Array('osstimecontrol', 'osstimecontrol_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'No.' => 'osstimecontrol_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);
	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'assigned_user_id';
	// For Popup listview and UI type support
	var $search_fields = Array(
		'No.' => Array('osstimecontrol', 'osstimecontrol_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	var $search_fields_name = Array(
		'No.' => 'osstimecontrol_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);
	// For Popup window record selection
	var $popup_fields = Array('name');
	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	// For Alphabetical search
	var $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'name';
	// Required Information for enabling Import feature
	var $required_fields = Array('assigned_user_id' => 1);
	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');
	var $default_order_by = '';
	var $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'assigned_user_id');

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type)
	{
		$registerLink = false;
		$displayLabel = 'Time Control';
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		if ($event_type == 'module.postinstall') {

			$tabid = getTabid($modulename);
			$adb->query("UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `tabid` = $tabid AND `columnname` IN ('name','osstimecontrol_no','osstimecontrol_status','smownerid','date_start','time_start','time_end','due_date','sum_time','platnosc');", true);
			$ModuleInstance = CRMEntity::getInstance($modulename);
			$ModuleInstance->setModuleSeqNumber("configure", $modulename, 'TC', '1');
			include_once('vtlib/Vtiger/Module.php');

			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('OSSTimeControl'));
			}
		} else if ($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if ($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if ($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if ($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if ($event_type == 'module.postupdate') {
			
		}
	}

	function retrieve_entity_info($record, $module)
	{
		parent::retrieve_entity_info($record, $module);
		$start = DateTimeField::convertToUserTimeZone($this->column_fields['date_start'] . ' ' . $this->column_fields['time_start']);
		$this->column_fields['date_start'] = $start->format('Y-m-d');
		$end = DateTimeField::convertToUserTimeZone($this->column_fields['due_date'] . ' ' . $this->column_fields['time_end']);
		$this->column_fields['due_date'] = $end->format('Y-m-d');
	}

	function saveentity($module_name, $fileid = '')
	{
		$date_start = $this->column_fields['date_start'];
		$due_date = $this->column_fields['due_date'];
		$start = DateTimeField::convertToDBTimeZone($this->column_fields['date_start'] . ' ' . $this->column_fields['time_start']);
		$this->column_fields['date_start'] = $start->format(DateTimeField::getPHPDateFormat());
		$end = DateTimeField::convertToDBTimeZone($this->column_fields['due_date'] . ' ' . $this->column_fields['time_end']);
		$this->column_fields['due_date'] = $end->format(DateTimeField::getPHPDateFormat());
		parent::saveentity($module_name, $fileid = '');
		$this->column_fields['date_start'] = $date_start;
		$this->column_fields['due_date'] = $due_date;
	}

	/** Function to unlink an entity with given Id from another entity */
	// TODO This function was placed here because uitype fields (67, 68, 69) exist in this module. Once the way of getting modules for these fields is improved, you should correct the parent::unlinkRelationship() function, and remove this one.
	function unlinkRelationship($id, $returnModule, $returnId)
	{
		global $log, $currentModule;
		$results = [];

		$where = '(crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
		$params = [$id, $returnModule, $returnId, $id, $returnModule, $returnId];
		$this->db->delete('vtiger_crmentityrel', $where, $params);

		$fieldRes = $this->db->pquery('SELECT tabid, tablename, columnname FROM vtiger_field WHERE fieldid IN (
			SELECT fieldid FROM vtiger_fieldmodulerel WHERE module=? AND relmodule=?)', [$currentModule, $returnModule]);
		$numOfFields = $this->db->num_rows($fieldRes);
		if ($fieldRes->rowCount()) {
			$results = $this->db->getArray($fieldRes);
		} else {
			$fieldRes = $this->db->pquery('SELECT fieldname AS `name`, fieldid AS id, fieldlabel AS label, columnname AS `column`, tablename AS `table`, vtiger_field.*  FROM vtiger_field WHERE `uitype` IN (66,67,68) AND `tabid` = ?;', [Vtiger_Functions::getModuleId($currentModule)]);
			while ($row = $this->db->getRow($fieldRes)) {
				$className = Vtiger_Loader::getComponentClassName('Model', 'Field', $currentModule);
				$fieldModel = new $className();
				foreach ($row as $properName => $propertyValue) {
					$fieldModel->$properName = $propertyValue;
				}
				$moduleList = $fieldModel->getUITypeModel()->getReferenceList();
				if (!empty($moduleList) && in_array($returnModule, $moduleList)) {
					$results[] = $row;
					break;
				}
			}
		}
		foreach ($results as $result) {
			$columnName = $result['columnname'];

			$relatedModule = vtlib_getModuleNameById($result['tabid']);
			$focusObj = CRMEntity::getInstance($relatedModule);

			$columns = [$columnName => null];
			$where = "$columnName = ? AND $focusObj->table_index = ?";
			$params = [$returnId, $id];
			$this->db->update($result['tablename'], $columns, $where, $params);
		}
	}
}
