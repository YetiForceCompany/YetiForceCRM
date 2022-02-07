<?php
/**
 * Ideas CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

/**
 * Class Ideas.
 */
class Ideas extends Vtiger_CRMEntity
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table_name = 'vtiger_ideas';

	/**
	 * Table index.
	 *
	 * @var string
	 */
	public $table_index = 'ideasid';

	/**
	 * Column fields.
	 *
	 * @var array
	 */
	public $column_fields = [];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ideascf', 'ideasid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_ideas', 'vtiger_ideascf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_ideas' => 'ideasid',
		'vtiger_ideascf' => 'ideasid', ];

	/**
	 * List fields name.
	 *
	 * @var array
	 */
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_NO' => 'ideas_no',
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => ['ideas', 'ideas_no'],
		'LBL_SUBJECT' => ['ideas', 'subject'],
		'Assigned To' => ['crmentity', 'assigned_user_id'],
	];

	/**
	 * Search fields name.
	 *
	 * @var array
	 */
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

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => 'Ideas'])->execute();
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Ideas']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
		}
	}
}
