<?php
/** KnowledgeBase CRMEntity Class.
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class KnowledgeBase extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_knowledgebase';
	public $table_index = 'knowledgebaseid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_knowledgebasecf', 'knowledgebaseid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_knowledgebase', 'u_yf_knowledgebasecf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_knowledgebase' => 'knowledgebaseid',
		'u_yf_knowledgebasecf' => 'knowledgebaseid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'subject' => 'subject',
		'FL_CATEGORY' => 'category',
		'Assigned To' => 'assigned_user_id',
		'FL_INTRODUCTION' => 'introduction',
		'FL_STATUS' => 'knowledgebase_status',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'subject' => ['knowledgebase', 'subject'],
		'FL_CATEGORY' => ['knowledgebase', 'category'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_INTRODUCTION' => ['knowledgebase', 'introduction'],
		'FL_STATUS' => ['knowledgebase', 'knowledgebase_status'],
	];
	public $search_fields_name = [];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// For Popup window record selection
	public $popup_fields = ['subject'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
}
