<?php
/**
 * Partners CRMEntity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class Partners extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_partners';
	public $table_index = 'partnersid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_partnerscf', 'partnersid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_partners', 'u_yf_partnerscf', 'u_yf_partners_address', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_partners' => 'partnersid',
		'u_yf_partnerscf' => 'partnersid',
		'u_yf_partners_address' => 'partneraddressid',
		'vtiger_entity_stats' => 'crmid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['partners', 'subject'],
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
	public $relationFields = ['subject', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['partners', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
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
		if ($eventType === 'module.postinstall') {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => 'Partners'])->execute();

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['Partners']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
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
		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		$relTableArr = ['Campaigns' => 'vtiger_campaign_records'];
		$tblFieldArr = ['vtiger_campaign_records' => 'campaignid'];
		$entityTblFieldArr = ['vtiger_campaign_records' => 'crmid'];
		foreach ($transferEntityIds as $transferId) {
			foreach ($relTableArr as $relTable) {
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
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'u_yf_partners' => 'partnersid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'u_yf_partners' => 'partnersid'],
		];
		if ($secModule === false) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	// Function to unlink an entity with given Id from another entity
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

	public function saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids)) {
			$withCrmids = [$withCrmids];
		}
		if ($withModule !== 'Campaigns') {
			parent::saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName);
		} else {
			foreach ($withCrmids as $withCrmid) {
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $withCrmid,
					'crmid' => $crmid,
					'campaignrelstatusid' => 0,
				])->execute();
			}
		}
	}
}
