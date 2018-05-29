<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Project extends CRMEntity
{
	public $table_name = 'vtiger_project';
	public $table_index = 'projectid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_projectcf', 'projectid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_project', 'vtiger_projectcf', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_project' => 'projectid',
		'vtiger_projectcf' => 'projectid',
		'vtiger_entity_stats' => 'crmid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Project Name' => ['project', 'projectname'],
		'Start Date' => ['project', 'startdate'],
		'Status' => ['project', 'projectstatus'],
		'Type' => ['project', 'projecttype'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Total time [Sum]' => ['project', 'sum_time_all'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Project Name' => 'projectname',
		'Start Date' => 'startdate',
		'Status' => 'projectstatus',
		'Type' => 'projecttype',
		'Assigned To' => 'assigned_user_id',
		'Total time [Sum]' => 'sum_time',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['projectname', 'startdate', 'projectstatus', 'projecttype', 'assigned_user_id', 'sum_time'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'projectname';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Project Name' => ['project', 'projectname'],
		'Start Date' => ['project', 'startdate'],
		'Status' => ['project', 'projectstatus'],
		'Type' => ['project', 'projecttype'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'Project Name' => 'projectname',
		'Start Date' => 'startdate',
		'Status' => 'projectstatus',
		'Type' => 'projecttype',
	];
	// For Popup window record selection
	public $popup_fields = ['projectname'];
	// For Alphabetical search
	public $def_basicsearch_col = 'projectname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'projectname';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'projectname', 'assigned_user_id'];

	/**
	 * Transform the value while exporting.
	 */
	public function transformExportValue($key, $value)
	{
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$projectTabid = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => 'Project'])->scalar();

			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			// Add Project module to the related list of Accounts module
			$accountsModuleInstance = vtlib\Module::getInstance('Accounts');
			$accountsModuleInstance->setRelatedList($moduleInstance, 'Projects', ['ADD', 'SELECT'], 'getDependentsList');

			// Add Project module to the related list of Accounts module
			$contactsModuleInstance = vtlib\Module::getInstance('Contacts');
			$contactsModuleInstance->setRelatedList($moduleInstance, 'Projects', ['ADD', 'SELECT'], 'getDependentsList');

			// Add Project module to the related list of HelpDesk module
			$helpDeskModuleInstance = vtlib\Module::getInstance('HelpDesk');
			$helpDeskModuleInstance->setRelatedList($moduleInstance, 'Projects', ['SELECT'], 'getRelatedList');

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Project']);
				}
			}

			\App\Fields\RecordNumber::setNumber($moduleName, 'PROJ', 1);
		} elseif ($eventType === 'module.postupdate') {
			$projectTabid = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => 'Project'])->scalar();

			// Add Comments widget to Project module
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Project']);
				}
			}

			\App\Fields\RecordNumber::setNumber($moduleName, 'PROJ', 1);
		}
	}

	public static function registerLinks()
	{
	}

	/**
	 * Function to unlink an entity with given Id from another entity.
	 *
	 * @param int    $id
	 * @param string $returnModule
	 * @param int    $returnId
	 * @param bool   $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if ($relatedName === 'getManyToMany') {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		} else {
			parent::deleteRelatedFromDB($id, $returnModule, $returnId);
			$dataReader = (new \App\Db\Query())->select(['tabid', 'tablename', 'columnname'])
				->from('vtiger_field')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $this->moduleName, 'relmodule' => $returnModule])])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				App\Db::getInstance()->createCommand()
					->update($row['tablename'], [$row['columnname'] => null], [$row['columnname'] => $returnId, CRMEntity::getInstance(App\Module::getModuleName($row['tabid']))->table_index => $id])
					->execute();
			}
			$dataReader->close();
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string This module name
	 * @param array List of Entity Id's from which related records need to be transfered
	 * @param int Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$relTableArr = ['ProjectTask' => 'vtiger_projecttask', 'ProjectMilestone' => 'vtiger_projectmilestone',
			'Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel', ];

		$tblFieldArr = ['vtiger_projecttask' => 'projecttaskid', 'vtiger_projectmilestone' => 'projectmilestoneid',
			'vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid', ];

		$entityTblFieldArr = ['vtiger_projecttask' => 'projectid', 'vtiger_projectmilestone' => 'projectid',
			'vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid', ];

		foreach ($transferEntityIds as $transferId) {
			foreach ($relTableArr as $relModule => $relTable) {
				$idField = $tblFieldArr[$relTable];
				$entityIdField = $entityTblFieldArr[$relTable];
				// IN clause to avoid duplicate entries
				$subQuery = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $entityId]);
				$query = (new \App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $transferId])->andWhere(['not in', $idField, $subQuery]);
				$dataReader = $query->createCommand()->query();
				while ($idFieldValue = $dataReader->readColumn(0)) {
					\App\Db::getInstance()->createCommand()->update($relTable, [$entityIdField => $entityId], [$entityIdField => $transferId, $idField => $idFieldValue])->execute();
				}
				$dataReader->close();
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
	}
}
