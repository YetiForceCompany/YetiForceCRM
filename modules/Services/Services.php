<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Services extends CRMEntity
{
	public $table_name = 'vtiger_service';
	public $table_index = 'serviceid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_servicecf', 'serviceid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_service', 'vtiger_servicecf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_service' => 'serviceid',
		'vtiger_servicecf' => 'serviceid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Service No' => ['service' => 'service_no'],
		'Service Name' => ['service' => 'servicename'],
		'Commission Rate' => ['service' => 'commissionrate'],
		'No of Units' => ['service' => 'qty_per_unit'],
		'Price' => ['service' => 'unit_price'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Commission Rate' => 'commissionrate',
		'No of Units' => 'qty_per_unit',
		'Price' => 'unit_price',
	];
	// Make the field link to detail view
	public $list_link_field = 'servicename';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Service No' => ['service' => 'service_no'],
		'Service Name' => ['service' => 'servicename'],
		'Price' => ['service' => 'unit_price'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Price' => 'unit_price',
	];

	/** @var string[] List of fields in the RelationListView */
	public $relationFields = ['service_no', 'servicename', 'unit_price'];
	// For Popup window record selection
	public $popup_fields = ['servicename', 'service_usageunit', 'unit_price'];
	// For Alphabetical search
	public $def_basicsearch_col = 'servicename';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'servicename';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['servicename', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $unit_price;

	/**
	 * Transform the value while exporting.
	 */
	public function transformExportValue($key, $value)
	{
		return parent::transformExportValue($key, $value);
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
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = ['PriceBooks' => 'vtiger_pricebookproductrel', 'Documents' => 'vtiger_senotesrel'];

		$tbl_field_arr = ['vtiger_inventoryproductrel' => 'id', 'vtiger_pricebookproductrel' => 'pricebookid', 'vtiger_senotesrel' => 'notesid'];

		$entity_tbl_field_arr = ['vtiger_inventoryproductrel' => 'productid', 'vtiger_pricebookproductrel' => 'productid', 'vtiger_senotesrel' => 'crmid'];

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", [$transferId, $entityId]);
				$res_cnt = $adb->numRows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; ++$i) {
						$id_field_value = $adb->queryResult($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", [$entityId, $transferId, $id_field_value]);
					}
				}
			}
		}

		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
	}

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param - $secmodule secondary module name
	 *                     returns the array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'PriceBooks' => ['vtiger_pricebookproductrel' => ['productid', 'pricebookid'], 'vtiger_service' => 'serviceid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_service' => 'serviceid'],
		];
		if ($secmodule === false) {
			return $relTables;
		}

		return $relTables[$secmodule];
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$moduleInstance->allowSharing();

			$ttModuleInstance = vtlib\Module::getInstance('HelpDesk');
			$ttModuleInstance->setRelatedList($moduleInstance, 'Services', ['select']);

			$leadModuleInstance = vtlib\Module::getInstance('Leads');
			$leadModuleInstance->setRelatedList($moduleInstance, 'Services', ['select']);

			$accModuleInstance = vtlib\Module::getInstance('Accounts');
			$accModuleInstance->setRelatedList($moduleInstance, 'Services', ['select']);

			$conModuleInstance = vtlib\Module::getInstance('Contacts');
			$conModuleInstance->setRelatedList($moduleInstance, 'Services', ['select']);

			$pbModuleInstance = vtlib\Module::getInstance('PriceBooks');
			$pbModuleInstance->setRelatedList($moduleInstance, 'Services', ['select'], 'getPricebookServices');

			// Initialize module sequence for the module
			\App\Fields\RecordNumber::setNumber($moduleName, 'SER', 1);
			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		} elseif ($eventType === 'module.postupdate') {
			$ServicesModule = vtlib\Module::getInstance('Services');
			vtlib\Access::setDefaultSharing($ServicesModule);
		}
	}

	/** Function to unlink an entity with given Id from another entity */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if ($returnModule === 'Accounts') {
			$focus = CRMEntity::getInstance($returnModule);
			$entityIds = $focus->getRelatedContactsIds($returnId);
			array_push($entityIds, $returnId);
			$returnModules = ['Accounts', 'Contacts'];
		} else {
			$entityIds = $returnId;
			$returnModules = [$returnModule];
		}
		if ($relatedName && $relatedName !== 'getRelatedList') {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		} else {
			App\Db::getInstance()->createCommand()->delete('vtiger_crmentityrel', [
				'or',
				['and', ['relcrmid' => $id], ['module' => $returnModules], ['crmid' => $entityIds]],
				['and', ['crmid' => $id], ['relmodule' => $returnModules], ['relcrmid' => $entityIds]],
			])->execute();
		}
	}
}
