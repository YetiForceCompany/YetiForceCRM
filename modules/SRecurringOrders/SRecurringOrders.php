<?php
/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class SRecurringOrders extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_srecurringorders';
	public $table_index = 'srecurringordersid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_srecurringorderscf', 'srecurringordersid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_srecurringorders', 'u_yf_srecurringorderscf', 'u_yf_recurring_info', 'u_yf_srecurringorders_address', 'vtiger_entity_stats'];
	public $related_tables = ['u_yf_recurring_info' => ['srecurringordersid', 'u_yf_srecurringorders', 'srecurringordersid']];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_srecurringorders' => 'srecurringordersid',
		'u_yf_srecurringorderscf' => 'srecurringordersid',
		'u_yf_recurring_info' => 'srecurringordersid',
		'u_yf_srecurringorders_address' => 'srecurringordersaddressid',
		'vtiger_entity_stats' => 'crmid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['srecurringorders', 'subject'],
		'Assigned To' => ['crmentity', 'smownerid'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['srecurringorders', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
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

	/** {@inheritdoc} */
	protected function init(): void
	{
		parent::init();
		$this->tableJoinClause['vtiger_recurring_info'] = 'LEFT JOIN';
	}

	/** {@inheritdoc} */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Fields\RecordNumber::getInstance($moduleName)->set('prefix', 'S-RO')->set('cur_id', 1)->save();
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['SRecurringOrders']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
		}
	}
}
