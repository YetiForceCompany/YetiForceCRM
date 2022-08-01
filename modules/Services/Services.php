<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Commission Rate' => 'commissionrate',
		'No of Units' => 'qty_per_unit',
		'Price' => 'unit_price',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Service No' => ['service' => 'service_no'],
		'Service Name' => ['service' => 'servicename'],
		'Price' => ['service' => 'unit_price'],
	];
	public $search_fields_name = [];

	/** @var string[] List of fields in the RelationListView */
	public $relationFields = [];
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

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param bool|string $secmodule secondary module name
	 *
	 * @return array returns the array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'PriceBooks' => ['vtiger_pricebookproductrel' => ['productid', 'pricebookid'], 'vtiger_service' => 'serviceid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_service' => 'serviceid'],
		];
		if (false === $secmodule) {
			return $relTables;
		}

		return $relTables[$secmodule];
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
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
			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		} elseif ('module.postupdate' === $eventType) {
			$ServicesModule = vtlib\Module::getInstance('Services');
			vtlib\Access::setDefaultSharing($ServicesModule);
		}
	}
}
