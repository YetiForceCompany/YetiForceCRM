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

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Asset No' => ['assets' => 'asset_no'],
		'Asset Name' => ['assets' => 'assetname'],
		'Customer Name' => ['account' => 'account'],
		'Product Name' => ['products' => 'product'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Asset No' => 'asset_no',
		'Asset Name' => 'assetname',
		'Customer Name' => 'account',
		'Product Name' => 'product',
	];
	// Make the field link to detail view
	public $list_link_field = 'assetname';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Asset No' => ['assets' => 'asset_no'],
		'Asset Name' => ['assets' => 'assetname'],
		'Customer Name' => ['account' => 'account'],
		'Product Name' => ['products' => 'product'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'Asset No' => 'asset_no',
		'Asset Name' => 'assetname',
		'Customer Name' => 'account',
		'Product Name' => 'product',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['asset_no', 'assetname', 'product', 'assigned_user_id'];
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
	public $unit_price;

	/**
	 * Transform the value while exporting.
	 */
	public function transformExportValue($key, $value)
	{
		if ('owner' == $key) {
			return \App\Fields\Owner::getLabel($value);
		}

		return parent::transformExportValue($key, $value);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type
	 */
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

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string This module name
	 * @param array List of Entity Id's from which related records need to be transfered
	 * @param int Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		$relTableArr = ['Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel'];
		$tblFieldArr = ['vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid'];
		$entityTblFieldArr = ['vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid'];
		foreach ($transferEntityIds as $transferId) {
			foreach ($relTableArr as $relTable) {
				$idField = $tblFieldArr[$relTable];
				$entityIdField = $entityTblFieldArr[$relTable];
				// IN clause to avoid duplicate entries
				$subQuery = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $entityId]);
				$query = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $transferId])->andWhere(['not in', $idField, $subQuery]);
				$dataReader = $query->createCommand()->query();
				while ($idFieldValue = $dataReader->readColumn(0)) {
					$dbCommand->update($relTable, [$entityIdField => $entityId], [$entityIdField => $transferId, $idField => $idFieldValue])->execute();
				}
				$dataReader->close();
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
	}
}
