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

	public $table_name = 'vtiger_osstimecontrol';
	public $table_index = 'osstimecontrolid';
	public $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_osstimecontrolcf', 'osstimecontrolid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_osstimecontrol', 'vtiger_osstimecontrolcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_osstimecontrol' => 'osstimecontrolid',
		'vtiger_osstimecontrolcf' => 'osstimecontrolid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'No.' => Array('osstimecontrol', 'osstimecontrol_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'No.' => 'osstimecontrol_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['osstimecontrol_no', 'assigned_user_id', 'createdtime'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'assigned_user_id';
	// For Popup listview and UI type support
	public $search_fields = Array(
		'No.' => Array('osstimecontrol', 'osstimecontrol_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	public $search_fields_name = Array(
		'No.' => 'osstimecontrol_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);
	// For Popup window record selection
	public $popup_fields = Array('name');
	// For Alphabetical search
	public $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'name';
	// Required Information for enabling Import feature
	public $required_fields = Array('assigned_user_id' => 1);
	// Callback function list during Importing
	public $special_functions = Array('set_import_assigned_user');
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('createdtime', 'modifiedtime', 'assigned_user_id');

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type)
	{
		$registerLink = false;
		$displayLabel = 'Time Control';
		$adb = PearDatabase::getInstance();

		if ($event_type == 'module.postinstall') {

			$tabid = \App\Module::getModuleId($modulename);
			$adb->query("UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `tabid` = $tabid && `columnname` IN ('name','osstimecontrol_no','osstimecontrol_status','smownerid','date_start','time_start','time_end','due_date','sum_time','platnosc');", true);
			\App\Fields\RecordNumber::setNumber($modulename, 'TC', '1');
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('OSSTimeControl'));
			}
		} else if ($event_type == 'module.disabled') {
			
		} else if ($event_type == 'module.enabled') {
			
		} else if ($event_type == 'module.preuninstall') {
			
		} else if ($event_type == 'module.preupdate') {
			
		} else if ($event_type == 'module.postupdate') {
			
		}
	}

	public function retrieve_entity_info($record, $module)
	{
		parent::retrieve_entity_info($record, $module);
		$start = DateTimeField::convertToUserTimeZone($this->column_fields['date_start'] . ' ' . $this->column_fields['time_start']);
		$this->column_fields['date_start'] = $start->format('Y-m-d');
		$end = DateTimeField::convertToUserTimeZone($this->column_fields['due_date'] . ' ' . $this->column_fields['time_end']);
		$this->column_fields['due_date'] = $end->format('Y-m-d');
	}

	public function saveentity($module_name, $fileid = '')
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
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		global $currentModule;
		$results = [];
		parent::deleteRelatedFromDB($currentModule, $id, $returnModule, $returnId);
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_field.tablename', 'vtiger_field.columnname', 'vtiger_tab.name'])
				->from('vtiger_field')
				->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $currentModule, 'relmodule' => $returnModule])])
				->createCommand()->query();
		if ($dataReader->count()) {
			$results = $dataReader->readAll();
		} else {
			$dataReader = (new \App\Db\Query())->select(['name' => 'fieldname', 'id' => 'fieldid', 'label' => 'fieldlabel', 'column' => 'columnname', 'table' => 'tablename', 'vtiger_field.*'])
					->from('vtiger_field')
					->where(['uitype' => [66, 67, 68], 'tabid' => App\Module::getModuleId($currentModule)])
					->createCommand()->query();
			while ($row = $dataReader->read()) {
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
		foreach ($results as $row) {
			App\Db::getInstance()->createCommand()
				->update($row['tablename'], [$row['columnname'] => 0], [$row['columnname'] => $returnId, CRMEntity::getInstance(App\Module::getModuleName($row['tabid']))->table_index => $id])->execute();
		}
	}

	public function deleteRelatedDependent($module, $crmid, $withModule, $withCrmid)
	{
		$fieldRes = $this->db->pquery('SELECT vtiger_field.tabid, vtiger_field.tablename, vtiger_field.columnname, vtiger_tab.name FROM vtiger_field LEFT JOIN vtiger_tab ON vtiger_tab.`tabid` = vtiger_field.`tabid` WHERE fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module=? && relmodule=?)', [$module, $withModule]);
		if ($fieldRes->rowCount()) {
			$results = $this->db->getArray($fieldRes);
		} else {
			$fieldRes = $this->db->pquery('SELECT fieldname AS `name`, fieldid AS id, fieldlabel AS label, columnname AS `column`, tablename AS `table`, vtiger_field.*  FROM vtiger_field WHERE `uitype` IN (66,67,68) && `tabid` = ?;', [vtlib\Functions::getModuleId($module)]);
			while ($row = $this->db->getRow($fieldRes)) {
				$className = Vtiger_Loader::getComponentClassName('Model', 'Field', $module);
				$fieldModel = new $className();
				foreach ($row as $properName => $propertyValue) {
					$fieldModel->$properName = $propertyValue;
				}
				$moduleList = $fieldModel->getUITypeModel()->getReferenceList();
				if (!empty($moduleList) && in_array($withModule, $moduleList)) {
					$row['name'] = $module;
					$results[] = $row;
					break;
				}
			}
		}
		foreach ($results as $result) {
			$focusObj = CRMEntity::getInstance($row['name']);
			$columnName = $row['columnname'];
			$columns = [$columnName => null];
			$where = "$columnName = ? && $focusObj->table_index = ?";
			$this->db->update($row['tablename'], $columns, $where, [$withCrmid, $crmid]);
		}
	}
}
