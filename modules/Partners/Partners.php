<?php
/**
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class Partners extends Vtiger_CRMEntity
{

	public $table_name = 'u_yf_partners';
	public $table_index = 'partnersid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('u_yf_partnerscf', 'partnersid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'u_yf_partners', 'u_yf_partnerscf', 'u_yf_partners_address', 'vtiger_entity_stats');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_partners' => 'partnersid',
		'u_yf_partnerscf' => 'partnersid',
		'u_yf_partners_address' => 'partneraddressid',
		'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('partners', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('partners', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	public $popup_fields = Array('subject');
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('subject', 'assigned_user_id');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$moduleInstance = CRMEntity::getInstance('Partners');
			\App\Fields\RecordNumber::setNumber($moduleName, 'PR', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', ['Partners']);

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('Partners'));
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(vtlib\Functions::getModuleId($moduleName));
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = ['Campaigns' => 'vtiger_campaign_records'];

		$tbl_field_arr = ['vtiger_campaign_records' => 'campaignid'];

		$entity_tbl_field_arr = ['vtiger_campaign_records' => 'crmid'];

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", [$transferId, $entityId]);
				$res_cnt = $adb->num_rows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; $i++) {
						$id_field_value = $adb->query_result($sel_result, $i, $id_field);
						$adb->update($rel_table, [$entity_id_field => $entityId], $entity_id_field . ' = ? and ' . $id_field . ' = ?', [$transferId, $id_field_value]);
					}
				}
			}
		}
		\App\Log::trace("Exiting transferRelatedRecords...");
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'u_yf_partners' => 'partnersid'],
		];
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{

		if (empty($returnModule) || empty($returnId))
			return;
		if ($returnModule === 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $returnId])->execute();
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids))
			$withCrmids = [$withCrmids];
		if ($withModule !== 'Campaigns') {
			parent::save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName);
		} else {
			foreach ($withCrmids as $withCrmid) {
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $withCrmid,
					'crmid' => $crmid,
					'campaignrelstatusid' => 0
				])->execute();
			}
		}
	}
}
