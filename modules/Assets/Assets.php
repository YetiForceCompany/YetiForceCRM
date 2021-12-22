<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Assets extends CRMEntity
{
	public $table_name = 'vtiger_assets';
	public $table_index = 'assetsid';
	public $column_fields = [];
	protected $lockFields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_assetscf', 'assetsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_assets', 'vtiger_assetscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_assets' => 'assetsid',
		'vtiger_assetscf' => 'assetsid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Asset No' => 'asset_no',
		'Asset Name' => 'assetname',
		'Customer Name' => 'account',
		'Product Name' => 'product',
	];
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Asset No' => ['assets' => 'asset_no'],
		'Asset Name' => ['assets' => 'assetname'],
		'Customer Name' => ['account' => 'account'],
		'Product Name' => ['products' => 'product'],
	];
	public $search_fields_name = [];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// For Popup window record selection
	public $popup_fields = ['assetname', 'account', 'product'];
	// For Alphabetical search
	public $def_basicsearch_col = 'assetname';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assetname', 'product', 'assigned_user_id'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/** {@inheritdoc} */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			//adds sharing accsess
			$AssetsModule = vtlib\Module::getInstance('Assets');
			vtlib\Access::setDefaultSharing($AssetsModule);

			//Showing Assets module in the related modules in the More Information Tab
			$assetInstance = vtlib\Module::getInstance('Assets');
			$assetLabel = 'Assets';

			$accountInstance = vtlib\Module::getInstance('Accounts');
			$accountInstance->setRelatedlist($assetInstance, $assetLabel, ['ADD'], 'getDependentsList');
			$productInstance = vtlib\Module::getInstance('Products');
			$productInstance->setRelatedlist($assetInstance, $assetLabel, ['ADD'], 'getDependentsList');
		}
	}
}
