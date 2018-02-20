<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

/**
 * Class Competition.
 */
class Competition extends Vtiger_CRMEntity
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table_name = 'u_yf_competition';

	/**
	 * Table index.
	 *
	 * @var string
	 */
	public $table_index = 'competitionid';

	/**
	 * Mandatory table for supporting custom fields.
	 *
	 * @var array
	 */
	public $customFieldTable = ['u_yf_competitioncf', 'competitionid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 *
	 * @var array
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_competition', 'u_yf_competitioncf', 'u_yf_competition_address', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 *
	 * @var array
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_competition' => 'competitionid',
		'u_yf_competitioncf' => 'competitionid',
		'u_yf_competition_address' => 'competitionaddressid',
		'vtiger_entity_stats' => 'crmid',
	];

	/**
	 * Mandatory for Listing (Related listview).
	 *
	 * @var array
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['competition', 'subject'],
		'Assigned To' => ['crmentity', 'smownerid'],
	];

	/**
	 * List fields name.
	 *
	 * @var array
	 */
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * List of fields in the RelationListView.
	 *
	 * @var string[]
	 */
	public $relationFields = ['subject', 'assigned_user_id'];

	/**
	 * Make the field link to detail view.
	 *
	 * @var string
	 */
	public $list_link_field = 'subject';

	/**
	 * For Popup listview and UI type support.
	 *
	 * @var array
	 */
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['competition', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];

	/**
	 * Search fields name.
	 *
	 * @var array
	 */
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * For Popup window record selection.
	 *
	 * @var array
	 */
	public $popup_fields = ['subject'];

	/**
	 * For Alphabetical search.
	 *
	 * @var string
	 */
	public $def_basicsearch_col = 'subject';

	/**
	 * Column value to use on detail view record text display.
	 *
	 * @var string
	 */
	public $def_detailview_recname = 'subject';

	/**
	 * Used when enabling/disabling the mandatory fields for the module.
	 * Refers to vtiger_field.fieldname values.
	 *
	 * @var array
	 */
	public $mandatory_fields = ['subject', 'assigned_user_id'];

	/**
	 * Default order by.
	 *
	 * @var string
	 */
	public $default_order_by = '';

	/**
	 * Default sort order.
	 *
	 * @var string
	 */
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			$moduleInstance = CRMEntity::getInstance('Competition');
			\App\Fields\RecordNumber::setNumber($moduleName, 'CMP', '1');
			\App\Db::getInstance()->update('vtiger_tab', ['customized' => 0], ['name' => 'Competition'])->execute();

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Competition']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId('Competition'));
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string $module            This module name
	 * @param array  $transferEntityIds List of Entity Id's from which related records need to be transfered
	 * @param int    $entityId          Id of the the Record to which the related records are to be moved
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
				$res_cnt = $adb->numRows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; ++$i) {
						$id_field_value = $adb->queryResult($sel_result, $i, $id_field);
						$adb->update($rel_table, [$entity_id_field => $entityId], $entity_id_field . ' = ? and ' . $id_field . ' = ?', [$transferId, $id_field_value]);
					}
				}
			}
		}
		\App\Log::trace('Exiting transferRelatedRecords...');
	}

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param bool|string $secModule secondary module name
	 *
	 * @return array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'u_yf_competition' => 'competitionid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'u_yf_competition' => 'competitionid'],
		];
		if ($secModule === false) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	/**
	 * Function to unlink an entity with given Id from another entity.
	 *
	 * @param it     $id
	 * @param string $returnModule
	 * @param int    $returnId
	 * @param string $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId)) {
			return;
		}
		if ($returnModule === 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $returnId])->execute();
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	/**
	 * Save related module.
	 *
	 * @param string    $module
	 * @param int       $crmid
	 * @param string    $withModule
	 * @param array|int $withCrmids
	 * @param string    $relatedName
	 */
	public function saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids)) {
			$withCrmids = [$withCrmids];
		}
		foreach ($withCrmids as $withCrmid) {
			if ($withModule === 'Campaigns') {
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $withCrmid,
					'crmid' => $crmid,
					'campaignrelstatusid' => 0,
				])->execute();
			} else {
				parent::saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName);
			}
		}
	}
}
