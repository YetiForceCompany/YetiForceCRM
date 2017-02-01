<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/vtigercrm/data/CRMEntity.php,v 1.16 2005/04/29 04:21:31 mickie Exp $
 * Description:  Defines the base class for all data entities used throughout the
 * application.  The base class including its methods and variables is designed to
 * be overloaded with module-specific methods and variables particular to the
 * module's base entity class.
 * ****************************************************************************** */
require_once('include/Tracker.php');
require_once('include/utils/utils.php');
require_once('include/utils/UserInfoUtil.php');

class CRMEntity
{

	public $db;
	public $ownedby;

	/** 	Constructor which will set the column_fields in this object
	 */
	public function __construct()
	{
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields(get_class($this));
	}

	static function getInstance($module)
	{
		$modName = $module;
		if (is_numeric($module)) {
			$modName = App\Module::getModuleName($module);
		}
		if ($module === 'Calendar' || $module === 'Events') {
			$module = 'Calendar';
			$modName = 'Activity';
		}
		if (\App\Cache::staticHas('CRMEntity', $module)) {
			return clone \App\Cache::staticGet('CRMEntity', $module);
		}

		// File access security check
		if (!class_exists($modName)) {
			if (AppConfig::performance('LOAD_CUSTOM_FILES') && file_exists("custom/modules/$module/$modName.php")) {
				\vtlib\Deprecated::checkFileAccessForInclusion("custom/modules/$module/$modName.php");
				require_once("custom/modules/$module/$modName.php");
			} else {
				\vtlib\Deprecated::checkFileAccessForInclusion("modules/$module/$modName.php");
				require_once("modules/$module/$modName.php");
			}
		}
		$focus = new $modName();
		$focus->moduleName = $module;
		\App\Cache::staticSave('CRMEntity', $module, clone $focus);
		return $focus;
	}

	/**
	 * Save the inventory data
	 */
	public function saveInventoryData($moduleName)
	{
		$db = App\Db::getInstance();

		\App\Log::trace('Entering ' . __METHOD__);

		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$table = $inventory->getTableName('data');

		$db->createCommand()->delete($table, ['id' => $this->id])->execute();
		if (is_array($this->inventoryData)) {
			foreach ($this->inventoryData as $insertData) {
				$insertData['id'] = $this->id;
				$db->createCommand()->insert($table, $insertData)->execute();
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	// Function which returns the value based on result type (array / ADODB ResultSet)
	private function resolve_query_result_value($result, $index, $columnname)
	{
		$adb = PearDatabase::getInstance();
		if (is_array($result))
			return $result[$index][$columnname];
		else
			return $adb->query_result($result, $index, $columnname);
	}

	/** Function to delete a record in the specifed table
	 * @param $table_name -- table name:: Type varchar
	 * The function will delete a record .The id is obtained from the class variable $this->id and the columnname got from $this->tab_name_index[$table_name]
	 */
	public function deleteRelation($table_name)
	{
		$adb = PearDatabase::getInstance();
		$check_query = "select * from $table_name where " . $this->tab_name_index[$table_name] . "=?";
		$check_result = $adb->pquery($check_query, array($this->id));
		$num_rows = $adb->num_rows($check_result);

		if ($num_rows == 1) {
			$adb->delete($table_name, $this->tab_name_index[$table_name] . ' = ?', [$this->id]);
		}
	}

	/** Function to attachment filename of the given entity
	 * @param $notesid -- crmid:: Type Integer
	 * The function will get the attachmentsid for the given entityid from vtiger_seattachmentsrel table and get the attachmentsname from vtiger_attachments table
	 * returns the 'filename'
	 */
	public function getOldFileName($notesid)
	{

		\App\Log::trace("in getOldFileName  " . $notesid);
		$adb = PearDatabase::getInstance();
		$query1 = "select * from vtiger_seattachmentsrel where crmid=?";
		$result = $adb->pquery($query1, array($notesid));
		$noofrows = $adb->num_rows($result);
		if ($noofrows != 0)
			$attachmentid = $adb->query_result($result, 0, 'attachmentsid');
		if ($attachmentid != '') {
			$query2 = "select * from vtiger_attachments where attachmentsid=?";
			$filename = $adb->query_result($adb->pquery($query2, array($attachmentid)), 0, 'name');
		}
		return $filename;
	}

	/**
	 * Function returns the column alias for a field
	 * @param <Array> $fieldinfo - field information
	 * @return string field value
	 */
	protected function createColumnAliasForField($fieldinfo)
	{
		return strtolower($fieldinfo['tablename'] . $fieldinfo['fieldname']);
	}

	/**
	 * Retrieve record information of the module
	 * @param integer $record - crmid of record
	 * @param string $module - module name
	 */
	public function retrieve_entity_info($record, $module)
	{
		if (!isset($record)) {
			throw new \Exception\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
		}

		// Tables which has multiple rows for the same record
		// will be skipped in record retrieve - need to be taken care separately.
		$multiRowTables = NULL;
		if (isset($this->multirow_tables)) {
			$multiRowTables = $this->multirow_tables;
		} else {
			$multiRowTables = array(
				'vtiger_attachments',
			);
		}

		// Lookup module field cache
		if ($module == 'Calendar' || $module == 'Events') {
			getColumnFields('Calendar');
			if (VTCacheUtils::lookupFieldInfo_Module('Events'))
				$cachedEventsFields = VTCacheUtils::lookupFieldInfo_Module('Events');
			else
				$cachedEventsFields = [];
			$cachedCalendarFields = VTCacheUtils::lookupFieldInfo_Module('Calendar');
			$cachedModuleFields = array_merge($cachedEventsFields, $cachedCalendarFields);
			$module = 'Calendar';
		} else {
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}
		if ($cachedModuleFields === false) {
			// Pull fields and cache for further use
			$tabid = \App\Module::getModuleId($module);
			$query = (new \App\Db\Query())
				->select(['fieldname', 'fieldid', 'fieldlabel', 'columnname', 'tablename', 'uitype', 'typeofdata', 'presence'])
				->from('vtiger_field')
				->where(['tabid' => $tabid]);
			$dataReader = $query->createCommand()->query();
			if ($dataReader->count()) {
				while ($row = $dataReader->read()) {
					// Update cache
					VTCacheUtils::updateFieldInfo(
						$tabid, $row['fieldname'], $row['fieldid'], $row['fieldlabel'], $row['columnname'], $row['tablename'], $row['uitype'], $row['typeofdata'], $row['presence']
					);
				}
				// Get only active field information
				$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
			}
		}

		if ($cachedModuleFields) {
			$query = new \App\Db\Query();
			$columnClause = [];
			$requiredTables = $this->tab_name_index; // copies-on-write

			foreach ($cachedModuleFields as $fieldInfo) {
				if (in_array($fieldInfo['tablename'], $multiRowTables)) {
					continue;
				}
				// Alias prefixed with tablename+fieldname to avoid duplicate column name across tables
				// fieldname are always assumed to be unique for a module
				$columnClause[] = $fieldInfo['tablename'] . '.' . $fieldInfo['columnname'] . ' AS ' . $this->createColumnAliasForField($fieldInfo);
			}
			$columnClause[] = 'vtiger_crmentity.deleted';
			$query->select($columnClause);
			if (isset($requiredTables['vtiger_crmentity'])) {
				$query->from('vtiger_crmentity');
				unset($requiredTables['vtiger_crmentity']);
				foreach ($requiredTables as $tableName => $tableIndex) {
					if (in_array($tableName, $multiRowTables)) {
						// Avoid multirow table joins.
						continue;
					}
					$query->leftJoin($tableName, "vtiger_crmentity.crmid = $tableName.$tableIndex");
				}
			}

			$query->where(['vtiger_crmentity.crmid' => $record]);
			if ($module != '') {
				$query->andWhere(['vtiger_crmentity.setype' => $module]);
			}
			$resultRow = $query->one();
			if (empty($resultRow)) {
				throw new \Exception\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
			} else {
				if (!empty($resultRow['deleted'])) {
					throw new \Exception\NoPermittedToRecord('LBL_RECORD_DELETE');
				}
				$showsAdditionalLabels = vglobal('showsAdditionalLabels');
				foreach ($cachedModuleFields as $fieldInfo) {
					$fieldvalue = '';
					$fieldkey = $this->createColumnAliasForField($fieldInfo);
					//Note : value is retrieved with a tablename+fieldname as we are using alias while building query
					if (isset($resultRow[$fieldkey])) {
						$fieldvalue = $resultRow[$fieldkey];
					}
					if ($showsAdditionalLabels && in_array($fieldInfo['uitype'], [10, 51, 73])) {
						$this->column_fields[$fieldInfo['fieldname'] . '_label'] = vtlib\Functions::getCRMRecordLabel($fieldvalue);
					}
					if ($showsAdditionalLabels && in_array($fieldInfo['uitype'], [52, 53])) {
						$this->column_fields[$fieldInfo['fieldname'] . '_label'] = vtlib\Functions::getOwnerRecordLabel($fieldvalue);
					}
					if ($fieldInfo['uitype'] === 120) {
						$query = (new \App\Db\Query())->select('userid')->from('u_#__crmentity_showners')->where(['crmid' => $record])->distinct();
						$fieldvalue = $query->column();
						if (is_array($fieldvalue)) {
							$fieldvalue = implode(',', $fieldvalue);
						}
					}
					$this->column_fields[$fieldInfo['fieldname']] = $fieldvalue;
				}
			}
		}
		$this->column_fields['record_id'] = $record;
		$this->column_fields['record_module'] = $module;
	}

	public function process_full_list_query($query)
	{
		\App\Log::trace("CRMEntity:process_full_list_query");
		$result = & $this->db->query($query, false);


		if ($this->db->getRowCount($result) > 0) {

			while ($row = $this->db->fetchByAssoc($result)) {
				$rowid = $row[$this->table_index];

				if (isset($rowid))
					$this->retrieve_entity_info($rowid, $this->module_name);
				else
					$this->db->println("rowid not set unable to retrieve");



				//clone function added to resolvoe PHP5 compatibility issue in Dashboards
				//If we do not use clone, while using PHP5, the memory address remains fixed but the
				//data gets overridden hence all the rows that come in bear the same value. This in turn
//provides a wrong display of the Dashboard graphs. The data is erroneously shown for a specific month alone
//Added by Richie
				$list[] = clone($this); //added by Richie to support PHP5
			}
		}

		if (isset($list))
			return $list;
		else
			return null;
	}

	/** This function should be overridden in each module.  It marks an item as deleted.
	 * If it is not overridden, then marking this type of item is not allowed
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function mark_deleted($id)
	{
		$current_user = vglobal('current_user');
		$date_var = date("Y-m-d H:i:s");
		$query = "UPDATE vtiger_crmentity set deleted=1,modifiedtime=?,modifiedby=? where crmid=?";
		$this->db->pquery($query, array($this->db->formatDate($date_var, true), $current_user->id, $id), true, "Error marking record deleted: ");
	}

	/**
	 * Function to check if the custom vtiger_field vtiger_table exists
	 * return true or false
	 */
	public function checkIfCustomTableExists($tablename)
	{
		$adb = PearDatabase::getInstance();
		$query = sprintf("SELECT * FROM %s", $adb->sql_escape_string($tablename));
		$result = $this->db->pquery($query, []);
		$testrow = $this->db->getFieldsCount($result);
		if ($testrow > 1) {
			$exists = true;
		} else {
			$exists = false;
		}
		return $exists;
	}

	/**
	 * function to construct the query to fetch the custom vtiger_fields
	 * return the query to fetch the custom vtiger_fields
	 */
	public function constructCustomQueryAddendum($tablename, $module)
	{
		$adb = PearDatabase::getInstance();
		$tabid = \App\Module::getModuleId($module);
		$sql1 = "select columnname,fieldlabel from vtiger_field where generatedtype=2 and tabid=? and vtiger_field.presence in (0,2)";
		$result = $adb->pquery($sql1, array($tabid));
		$numRows = $adb->num_rows($result);
		$sql3 = "select ";
		for ($i = 0; $i < $numRows; $i++) {
			$columnName = $adb->query_result($result, $i, "columnname");
			$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
			//construct query as below
			if ($i == 0) {
				$sql3 .= $tablename . "." . $columnName . " '" . $fieldlabel . "'";
			} else {
				$sql3 .= ", " . $tablename . "." . $columnName . " '" . $fieldlabel . "'";
			}
		}
		if ($numRows > 0) {
			$sql3 = $sql3 . ',';
		}
		return $sql3;
	}

	/**
	 * Function invoked during export of module record value.
	 */
	public function transform_export_value($key, $value)
	{
		// NOTE: The sub-class can override this function as required.
		return $value;
	}

	/** Function to initialize the required fields array for that particular module */
	public function initRequiredFields($module)
	{
		$adb = PearDatabase::getInstance();

		$tabid = \App\Module::getModuleId($module);
		$sql = "select * from vtiger_field where tabid= ? and typeofdata like '%M%' and uitype not in ('53','70') and vtiger_field.presence in (0,2)";
		$result = $adb->pquery($sql, array($tabid));
		$numRows = $adb->num_rows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$fieldName = $adb->query_result($result, $i, "fieldname");
			$this->required_fields[$fieldName] = 1;
		}
	}

	/** Function to delete an entity with given Id */
	public function trash($moduleName, $id)
	{
		if (vtlib\Functions::getCRMRecordType($id) !== $moduleName) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
		$this->mark_deleted($id);
		\App\Db::getInstance()->createCommand()->delete('vtiger_tracker', ['user_id' => \App\User::getCurrentUserRealId(), 'item_id' => $id])->execute();
	}

	/**
	 * Function to unlink all the dependent entities of the given Entity by Id
	 * @param string $moduleName
	 * @param int $recordId
	 */
	public function deletePerminently($moduleName, $recordId)
	{
		
	}

	/** Function to unlink an entity with given Id from another entity */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		$currentModule = vglobal('currentModule');

		switch ($relatedName) {
			case 'getManyToMany':
				$this->deleteRelatedM2M($currentModule, $id, $returnModule, $returnId);
				break;
			case 'getDependentsList':
				$this->deleteRelatedDependent($currentModule, $id, $returnModule, $returnId);
				break;
			case 'getRelatedList':
				$this->deleteRelatedFromDB($currentModule, $id, $returnModule, $returnId);
				break;
			default:
				$this->deleteRelatedDependent($currentModule, $id, $returnModule, $returnId);
				$this->deleteRelatedFromDB($currentModule, $id, $returnModule, $returnId);
				break;
		}
	}

	public function deleteRelatedDependent($module, $crmid, $withModule, $withCrmid)
	{
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_field.tablename', 'vtiger_field.columnname', 'vtiger_tab.name'])
				->from('vtiger_field')
				->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $module, 'relmodule' => $withModule])])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			App\Db::getInstance()->createCommand()
				->update($row['tablename'], [$row['columnname'] => 0], [$row['columnname'] => $withCrmid, CRMEntity::getInstance($row['name'])->table_index => $crmid])->execute();
		}
	}

	public function deleteRelatedM2M($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($module, $withModule);
		$db->delete($referenceInfo['table'], $referenceInfo['base'] . ' = ? && ' . $referenceInfo['rel'] . ' = ?', [$withCrmid, $crmid]);
	}

	public function deleteRelatedFromDB($module, $crmid, $withModule, $withCrmid)
	{
		App\Db::getInstance()->createCommand()->delete('vtiger_crmentityrel', ['or',
			[
				'crmid' => $crmid,
				'relmodule' => $withModule,
				'relcrmid' => $withCrmid
			],
			[
				'relcrmid' => $crmid,
				'module' => $withModule,
				'crmid' => $withCrmid
			]
		])->execute();
	}

	/**
	 * Function to restore a deleted record of specified module with given crmid
	 * @param string $moduleName
	 * @param int $id
	 */
	public function restore($moduleName, $id)
	{
		$result = \App\Db::getInstance()->createCommand()->update(
				'vtiger_crmentity', [
				'deleted' => 0,
				'modifiedtime' => date('Y-m-d H:i:s'),
				'modifiedby' => \App\User::getCurrentUserRealId(),
				'users' => null,
				], ['crmid' => $id]
			)->execute();
		if ($result) {
			if (!\AppConfig::security('CACHING_PERMISSION_TO_RECORD')) {
				\App\Privilege::setUpdater($moduleName, $id, 6, 0);
			}
			//Event triggering code
			$eventHandler = new App\EventHandler();
			$eventHandler->setRecordModel(Vtiger_Record_Model::getInstanceById($id));
			$eventHandler->setModuleName($moduleName);
			$eventHandler->trigger('EntityAfterRestore');
		}
	}
	/* Function to check if the mod number already exits */

	public function checkModuleSeqNumber($table, $column, $no)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery(sprintf("SELECT %s FROM *s WHERE %s = ?", $adb->sql_escape_string($column), $adb->sql_escape_string($table), $adb->sql_escape_string($column)), [$no]);
		$num_rows = $adb->num_rows($result);
		if ($num_rows > 0)
			return true;
		else
			return false;
	}

	// END

	public function updateMissingSeqNumber($module)
	{
		\App\Log::trace("Entered updateMissingSeqNumber function");
		vtlib_setup_modulevars($module, $this);
		$tabid = \App\Module::getModuleId($module);
		if (!\App\Fields\RecordNumber::isModuleSequenceConfigured($tabid))
			return;

		$fieldinfo = (new App\Db\Query())->from('vtiger_field')
				->where(['tabid' => $tabid, 'uitype' => 4])->one();
		$returninfo = [];

		if ($fieldinfo) {
			$fieldTable = $fieldinfo['tablename'];
			$fieldColumn = $fieldinfo['columnname'];
			if ($fieldTable === $this->table_name) {
				$dataReader = (new App\Db\Query())->select(['recordid' => $this->table_index])
						->from($this->table_name)
						->where(['or', [$fieldColumn => ''], [$fieldColumn => null]])
						->createCommand()->query();
				$totalCount = $dataReader->count();
				if ($totalCount) {
					$returninfo['totalrecords'] = $totalCount;
					$returninfo['updatedrecords'] = 0;
					$moduleData = \App\Fields\RecordNumber::getNumber($tabid);
					$sequenceNumber = $moduleData['sequenceNumber'];
					$prefix = $moduleData['prefix'];
					$postfix = $moduleData['postfix'];
					$oldNumber = $sequenceNumber;
					while ($recordinfo = $dataReader->read()) {
						$recordNumber = \App\Fields\RecordNumber::parse($prefix . $sequenceNumber . $postfix);
						App\Db::getInstance()->createCommand()
							->update($fieldTable, [$fieldColumn => $recordNumber], [$this->table_index => $recordinfo['recordid']])
							->execute();
						$sequenceNumber += 1;
						$returninfo['updatedrecords'] = $returninfo['updatedrecords'] + 1;
					}
					if ($oldNumber != $sequenceNumber) {
						\App\Fields\RecordNumber::updateNumber($sequenceNumber, $tabid);
					}
				}
			} else {
				\App\Log::error("Updating Missing Sequence Number FAILED! REASON: Field table and module table mismatching.");
			}
		}
		return $returninfo;
	}

	/**
	 * For Record View Notification
	 */
	public function isViewed($crmid = false)
	{
		if (!$crmid) {
			$crmid = $this->id;
		}
		if ($crmid) {
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery("SELECT viewedtime,modifiedtime,smcreatorid,smownerid,modifiedby FROM vtiger_crmentity WHERE crmid=?", Array($crmid));
			$resinfo = $adb->fetch_array($result);

			$lastviewed = $resinfo['viewedtime'];
			$modifiedon = $resinfo['modifiedtime'];
			$smownerid = $resinfo['smownerid'];
			$smcreatorid = $resinfo['smcreatorid'];
			$modifiedby = $resinfo['modifiedby'];

			if ($modifiedby == '0' && ($smownerid == $smcreatorid)) {
				/** When module record is created * */
				return true;
			} else if ($smownerid == $modifiedby) {
				/** Owner and Modifier as same. * */
				return true;
			} else if ($lastviewed && $modifiedon) {
				/** Lastviewed and Modified time is available. */
				if ($this->__timediff($modifiedon, $lastviewed) > 0)
					return true;
			}
		}
		return false;
	}

	public function __timediff($d1, $d2)
	{
		list($t1_1, $t1_2) = explode(' ', $d1);
		list($t1_y, $t1_m, $t1_d) = explode('-', $t1_1);
		list($t1_h, $t1_i, $t1_s) = explode(':', $t1_2);

		$t1 = mktime($t1_h, $t1_i, $t1_s, $t1_m, $t1_d, $t1_y);

		list($t2_1, $t2_2) = explode(' ', $d2);
		list($t2_y, $t2_m, $t2_d) = explode('-', $t2_1);
		list($t2_h, $t2_i, $t2_s) = explode(':', $t2_2);

		$t2 = mktime($t2_h, $t2_i, $t2_s, $t2_m, $t2_d, $t2_y);

		if ($t1 == $t2)
			return 0;
		return $t2 - $t1;
	}

	public function markAsViewed($userid)
	{
		$adb = PearDatabase::getInstance();
		$adb->update('vtiger_crmentity', ['viewedtime' => date('Y-m-d H:i:s')], 'crmid = ? && smownerid = ?', [$this->id, $userid]);
	}

	/**
	 * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 * @param String function name
	 */
	public function save_related_module($module, $crmid, $withModule, $withCrmid, $relatedName = false)
	{
		if (!is_array($withCrmid))
			$withCrmid = [$withCrmid];
		switch ($relatedName) {
			case 'getManyToMany':
				$this->saveRelatedM2M($module, $crmid, $withModule, $withCrmid);
				break;
			case 'getDependentsList':
				break;
			default:
				$this->saveRelatedToDB($module, $crmid, $withModule, $withCrmid);
				break;
		}
	}

	public function saveRelatedM2M($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($module, $withModule);

		foreach ($withCrmid as $relcrmid) {
			$check = $db->pquery(sprintf('SELECT 1 FROM `%s` WHERE %s = ? && %s = ?', $referenceInfo['table'], $referenceInfo['base'], $referenceInfo['rel']), [$relcrmid, $crmid]);
			// Relation already exists? No need to add again
			if ($check && $db->getRowCount($check))
				continue;
			$db->insert($referenceInfo['table'], [
				$referenceInfo['base'] => $relcrmid,
				$referenceInfo['rel'] => $crmid
			]);
		}
	}

	public function saveRelatedToDB($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		foreach ($withCrmid as $relcrmid) {
			if ($withModule == 'Documents') {
				$checkpresence = $db->pquery('SELECT crmid FROM vtiger_senotesrel WHERE crmid = ? AND notesid = ?', [$crmid, $relcrmid]);
				// Relation already exists? No need to add again
				if ($checkpresence && $db->getRowCount($checkpresence))
					continue;
				\App\Db::getInstance()->createCommand()->insert('vtiger_senotesrel', [
					'crmid' => $crmid,
					'notesid' => $relcrmid
				])->execute();
			} else {
				$checkpresence = $db->pquery('SELECT crmid FROM vtiger_crmentityrel WHERE crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?', [$crmid, $module, $relcrmid, $withModule]
				);
				// Relation already exists? No need to add again
				if ($checkpresence && $db->getRowCount($checkpresence))
					continue;
				\App\Db::getInstance()->createCommand()->insert('vtiger_crmentityrel', [
					'crmid' => $crmid,
					'module' => $module,
					'relcrmid' => $relcrmid,
					'relmodule' => $withModule,
					'rel_created_user' => \App\User::getCurrentUserId(),
					'rel_created_time' => date('Y-m-d H:i:s')
				])->execute();
			}
		}
	}

	/**
	 * Delete the related module record information. Triggered from updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 */
	public function delete_related_module($module, $crmid, $withModule, $withCrmid)
	{
		$db = PearDatabase::getInstance();
		if (!is_array($withCrmid))
			$withCrmid = Array($withCrmid);
		foreach ($withCrmid as $relcrmid) {

			if ($withModule == 'Documents') {
				$db->delete('vtiger_senotesrel', 'crmid=? && notesid=?', [$crmid, $relcrmid]);
			} else {
				$db->delete('vtiger_crmentityrel', '(crmid=? && module=? && relcrmid=? && relmodule=?) || (relcrmid=? && relmodule=? && crmid=? && module=?)', [$crmid, $module, $relcrmid, $withModule, $crmid, $module, $relcrmid, $withModule]
				);
			}
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
		$db = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$relTables = $this->setRelationTables();
		if (key_exists('Documents', $relTables)) {
			$relTables['Attachments'] = ['vtiger_seattachmentsrel' => ['crmid', 'attachmentsid']];
		}
		foreach ($transferEntityIds as &$transferId) {
			// Pick the records related to the entity to be transfered, but do not pick the once which are already related to the current entity.
			$relatedRecords = $db->pquery('SELECT relcrmid, relmodule FROM vtiger_crmentityrel WHERE crmid=? && module=?' .
				' && relcrmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid=? && module=?)', array($transferId, $module, $entityId, $module));
			while ($row = $db->getRow($relatedRecords)) {
				$where = 'relcrmid = ? && relmodule = ? && crmid = ? && module = ?';
				$params = [$row['relcrmid'], $row['relmodule'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['crmid' => $entityId], $where, $params);
			}
			// Pick the records to which the entity to be transfered is related, but do not pick the once to which current entity is already related.
			$parentRecords = $db->pquery('SELECT crmid, module FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?' .
				' && crmid NOT IN (SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?)', array($transferId, $module, $entityId, $module));
			while ($row = $db->getRow($parentRecords)) {
				$where = 'crmid = ? && module = ? && relcrmid = ? && relmodule = ?';
				$params = [$row['crmid'], $row['module'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['relcrmid' => $entityId], $where, $params);
			}

			$db->update('vtiger_modtracker_basic', ['crmid' => $entityId], 'crmid = ? && status <> ?', [$transferId, 7]);
			foreach ($relTables as &$relTable) {
				$idField = current($relTable)[1];
				$entityIdField = current($relTable)[0];
				$relTableName = key($relTable);
				// IN clause to avoid duplicate entries
				$sql = "SELECT $idField FROM $relTableName WHERE $entityIdField = ? " .
					" && $idField NOT IN ( SELECT $idField FROM $relTableName WHERE $entityIdField = ? )";
				$selResult = $db->pquery($sql, [$transferId, $entityId]);
				if ($db->getRowCount($selResult) > 0) {
					while (($idFieldValue = $db->getSingleValue($selResult)) !== false) {
						$db->update($relTableName, [
							$entityIdField => $entityId
							], "$entityIdField = ? and $idField = ?", [$transferId, $idFieldValue]
						);
					}
				}
			}
			$fields = App\Field::getReletedFieldForModule(false, $module);
			foreach ($fields as &$field) {
				$columnName = $field['columnname'];
				$db->update($field['tablename'], [
					$columnName => $entityId
					], "$columnName = ?", [$transferId]
				);
			}
		}
		\App\Log::trace('Exiting transferRelatedRecords...');
	}
	/*
	 * Function to get the primary query part of a report for which generateReportsQuery Doesnt exist in module
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, $queryPlanner)
	{
		$adb = PearDatabase::getInstance();
		$primary = CRMEntity::getInstance($module);

		vtlib_setup_modulevars($module, $primary);
		$moduletable = $primary->table_name;
		$moduleindex = $primary->table_index;
		$modulecftable = $primary->customFieldTable[0];
		$modulecfindex = $primary->customFieldTable[1];
		$joinTables = [$moduletable, 'vtiger_crmentity'];

		if (isset($modulecftable) && $queryPlanner->requireTable($modulecftable)) {
			$joinTables[] = $modulecftable;
			$cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
		} else {
			$cfquery = '';
		}
		foreach ($primary->tab_name_index as $table => $index) {
			if (in_array($table, $joinTables) || !$queryPlanner->requireTable($table)) {
				continue;
			}
			$joinTables[] = $table;
			$cfquery .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $index . ' = ' . $primary->table_name . '.' . $primary->table_index;
		}

		$relquery = '';
		$matrix = $queryPlanner->newDependencyMatrix();

		$fields_query = $adb->pquery("SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid && vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)", [$module]);

		if ($adb->num_rows($fields_query) > 0) {
			$rows_fields_query = $adb->num_rows($fields_query);
			for ($i = 0; $i < $rows_fields_query; $i++) {
				$field_name = $adb->query_result($fields_query, $i, 'fieldname');
				$field_id = $adb->query_result($fields_query, $i, 'fieldid');
				$tab_name = $adb->query_result($fields_query, $i, 'tablename');
				$ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", [$field_id]);

				if ($adb->num_rows($ui10_modules_query) > 0) {

					// Capture the forward table dependencies due to dynamic related-field
					$crmentityRelModuleFieldTable = "vtiger_crmentityRel$module$field_id";

					$crmentityRelModuleFieldTableDeps = [];
					$countNumRows = $adb->num_rows($ui10_modules_query);
					for ($j = 0; $j < $countNumRows; $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$crmentityRelModuleFieldTableDeps[] = $rel_tab_name . "Rel$module$field_id";
					}

					$matrix->setDependency($crmentityRelModuleFieldTable, $crmentityRelModuleFieldTableDeps);
					$matrix->addDependency($tab_name, $crmentityRelModuleFieldTable);

					if ($queryPlanner->requireTable($crmentityRelModuleFieldTable, $matrix)) {
						$relquery .= " left join vtiger_crmentity as $crmentityRelModuleFieldTable on $crmentityRelModuleFieldTable.crmid = $tab_name.$field_name and vtiger_crmentityRel$module$field_id.deleted=0";
					}

					$countNumRows = $adb->num_rows($ui10_modules_query);
					for ($j = 0; $j < $countNumRows; $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;

						$rel_tab_name_rel_module_table_alias = $rel_tab_name . "Rel$module$field_id";

						if ($queryPlanner->requireTable($rel_tab_name_rel_module_table_alias)) {
							$relquery .= " left join $rel_tab_name as $rel_tab_name_rel_module_table_alias  on $rel_tab_name_rel_module_table_alias.$rel_tab_index = $crmentityRelModuleFieldTable.crmid";
						}
					}
				}
			}
		}

		$query = "from $moduletable inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex";

		// Add the pre-joined custom table query
		$query .= ' ' . $cfquery;

		if ($queryPlanner->requireTable('vtiger_groups' . $module)) {
			$query .= ' left join vtiger_groups as vtiger_groups' . $module . ' on vtiger_groups' . $module . '.groupid = vtiger_crmentity.smownerid';
		}

		if ($queryPlanner->requireTable('vtiger_users' . $module)) {
			$query .= ' left join vtiger_users as vtiger_users' . $module . ' on vtiger_users' . $module . '.id = vtiger_crmentity.smownerid';
		}
		if ($queryPlanner->requireTable('vtiger_lastModifiedBy' . $module)) {
			$query .= ' left join vtiger_users as vtiger_lastModifiedBy' . $module . ' on vtiger_lastModifiedBy' . $module . '.id = vtiger_crmentity.modifiedby';
		}
		if ($queryPlanner->requireTable('vtiger_createdby' . $module)) {
			$query .= ' left join vtiger_users as vtiger_createdby' . $module . ' on vtiger_createdby' . $module . '.id = vtiger_crmentity.smcreatorid';
		}
		if ($queryPlanner->requireTable('vtiger_entity_stats')) {
			$query .= ' inner join vtiger_entity_stats on $moduletable.$moduleindex = vtiger_entity_stats.crmid';
		}
		if ($queryPlanner->requireTable('u_yf_crmentity_showners')) {
			$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
		}
		if ($queryPlanner->requireTable("vtiger_shOwners$module")) {
			$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
		}
		$query .= '	left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= ' left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';

		// Add the pre-joined relation table query
		$query .= ' ' . $relquery;

		return $query;
	}
	/*
	 * Function to get the secondary query part of a report for which generateReportsSecQuery Doesnt exist in module
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{
		$adb = PearDatabase::getInstance();
		$secondary = CRMEntity::getInstance($secmodule);

		vtlib_setup_modulevars($secmodule, $secondary);

		$tablename = $secondary->table_name;
		$tableindex = $secondary->table_index;
		$modulecftable = $secondary->customFieldTable[0];
		$modulecfindex = $secondary->customFieldTable[1];

		if (isset($modulecftable) && $queryPlanner->requireTable($modulecftable)) {
			$cfquery = "left join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$tablename.$tableindex";
		} else {
			$cfquery = '';
		}

		$relquery = '';
		$matrix = $queryPlanner->newDependencyMatrix();

		$fields_query = $adb->pquery("SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid && vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)", array($secmodule));

		if ($adb->num_rows($fields_query) > 0) {
			$countFieldsQuery = $adb->num_rows($fields_query);
			for ($i = 0; $i < $countFieldsQuery; $i++) {
				$field_name = $adb->query_result($fields_query, $i, 'fieldname');
				$field_id = $adb->query_result($fields_query, $i, 'fieldid');
				$tab_name = $adb->query_result($fields_query, $i, 'tablename');
				$ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", array($field_id));

				if ($adb->num_rows($ui10_modules_query) > 0) {
					// Capture the forward table dependencies due to dynamic related-field
					$crmentityRelSecModuleTable = "vtiger_crmentityRel$secmodule$field_id";

					$crmentityRelSecModuleTableDeps = [];
					$rows_ui10_modules_query = $adb->num_rows($ui10_modules_query);
					for ($j = 0; $j < $rows_ui10_modules_query; $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$crmentityRelSecModuleTableDeps[] = $rel_tab_name . "Rel$secmodule";
					}

					$matrix->setDependency($crmentityRelSecModuleTable, $crmentityRelSecModuleTableDeps);
					$matrix->addDependency($tab_name, $crmentityRelSecModuleTable);

					if ($queryPlanner->requireTable($crmentityRelSecModuleTable, $matrix)) {
						$relquery .= " left join vtiger_crmentity as $crmentityRelSecModuleTable on $crmentityRelSecModuleTable.crmid = $tab_name.$field_name and $crmentityRelSecModuleTable.deleted=0";
					}
					$countNumRows = $adb->num_rows($ui10_modules_query);
					for ($j = 0; $j < $countNumRows; $j++) {
						$rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;

						$rel_tab_name_rel_secmodule_table_alias = $rel_tab_name . "Rel$secmodule";

						if ($queryPlanner->requireTable($rel_tab_name_rel_secmodule_table_alias)) {
							$relquery .= " left join $rel_tab_name as $rel_tab_name_rel_secmodule_table_alias on $rel_tab_name_rel_secmodule_table_alias.$rel_tab_index = $crmentityRelSecModuleTable.crmid";
						}
					}
				}
			}
		}

		// Update forward table dependencies
		$matrix->setDependency("vtiger_crmentity$secmodule", array("vtiger_groups$secmodule", "vtiger_users$secmodule", "vtiger_lastModifiedBy$secmodule"));
		$matrix->addDependency($tablename, "vtiger_crmentity$secmodule");

		if (!$queryPlanner->requireTable($tablename, $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "$tablename", "$tableindex", $queryPlanner);

		if ($queryPlanner->requireTable("vtiger_crmentity$secmodule", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentity$secmodule on vtiger_crmentity$secmodule.crmid = $tablename.$tableindex && vtiger_crmentity$secmodule.deleted=0";
		}

		// Add the pre-joined custom table query
		$query .= " " . $cfquery;

		if ($queryPlanner->requireTable("vtiger_groups$secmodule")) {
			$query .= " left join vtiger_groups as vtiger_groups" . $secmodule . " on vtiger_groups" . $secmodule . ".groupid = vtiger_crmentity$secmodule.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_users$secmodule")) {
			$query .= " left join vtiger_users as vtiger_users" . $secmodule . " on vtiger_users" . $secmodule . ".id = vtiger_crmentity$secmodule.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedBy$secmodule")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedBy" . $secmodule . " on vtiger_lastModifiedBy" . $secmodule . ".id = vtiger_crmentity" . $secmodule . ".modifiedby";
		}
		if ($queryPlanner->requireTable("vtiger_createdby$secmodule")) {
			$query .= " left join vtiger_users as vtiger_createdby" . $secmodule . " on vtiger_createdby" . $secmodule . ".id = vtiger_crmentity" . $secmodule . ".modifiedby";
		}

		// Add the pre-joined relation table query
		$query .= " " . $relquery;

		return $query;
	}
	/*
	 * Function to get the security query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for security of the module
	 */

	public function getListViewSecurityParameter($module)
	{
		$tabid = \App\Module::getModuleId($module);
		$current_user = vglobal('current_user');
		if ($current_user) {
			$privileges = App\User::getPrivilegesFile($current_user->id);
			$sharingPrivileges = App\User::getSharingFile($current_user->id);
		} else {
			return '';
		}
		$sec_query = '';
		if ($privileges['is_admin'] === false && $privileges['profile_global_permission'][1] == 1 && $privileges['profile_global_permission'][2] == 1 && $sharingPrivileges['defOrgShare'][$tabid] == 3) {
			$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid
					in (select vtiger_user2role.userid from vtiger_user2role
							inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid
							inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid
							where vtiger_role.parentrole like '" . $privileges['parent_role_seq'] . "::%') or vtiger_crmentity.smownerid
					in(select shareduserid from vtiger_tmp_read_user_sharing_per
						where userid=" . $current_user->id . " and tabid=" . $tabid . ") or (";
			if (sizeof($privileges['groups']) > 0) {
				$sec_query .= " vtiger_groups.groupid in (" . implode(",", $privileges['groups']) . ") or ";
			}
			$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid
						from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
		}
		return $sec_query;
	}
	/*
	 * Function to get the relation query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on relating the primary module and secondary module
	 */

	public function getRelationQuery($module, $secmodule, $table_name, $column_name, $queryPlanner)
	{
		$tab = getRelationTables($module, $secmodule);

		foreach ($tab as $key => $value) {
			$tables[] = $key;
			$fields[] = $value;
		}
		$pritablename = $tables[0];
		$sectablename = $tables[1];
		$prifieldname = $fields[0][0];
		$secfieldname = $fields[0][1];
		$tmpname = $pritablename . 'tmp' . $secmodule;
		$condition = "";
		if (!empty($tables[1]) && !empty($fields[1])) {
			$condvalue = $tables[1] . "." . $fields[1];
			$condition = "$pritablename.$prifieldname=$condvalue";
		} else {
			$condvalue = $table_name . "." . $column_name;
			$condition = "$pritablename.$secfieldname=$condvalue";
		}

		// Look forward for temporary table usage as defined by the QueryPlanner
		$secQuery = "select $table_name.* from $table_name inner join vtiger_crmentity on " .
			"vtiger_crmentity.crmid=$table_name.$column_name and vtiger_crmentity.deleted=0";

		$secQueryTempTableQuery = $queryPlanner->registerTempTable($secQuery, array($column_name, $fields[1], $prifieldname));

		$query = '';
		if ($pritablename == 'vtiger_crmentityrel') {
			$condition = "($table_name.$column_name={$tmpname}.{$secfieldname} " .
				"OR $table_name.$column_name={$tmpname}.{$prifieldname})";
			$query = " left join vtiger_crmentityrel as $tmpname ON ($condvalue={$tmpname}.{$secfieldname} " .
				"OR $condvalue={$tmpname}.{$prifieldname}) ";
		} elseif (strripos($pritablename, 'rel') === (strlen($pritablename) - 3)) {
			$instance = self::getInstance($module);
			$sectableindex = $instance->tab_name_index[$sectablename];
			$condition = "$table_name.$column_name=$tmpname.$secfieldname";
			if ($pritablename === 'vtiger_senotesrel') {
				$query = " left join $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname
                    && $tmpname.notesid IN (SELECT crmid FROM vtiger_crmentity WHERE setype='Documents' && deleted = 0))";
			} else {
				$query = " left join $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname)";
			}
			if ($secmodule === 'Leads') {
				$condition .= " && $table_name.converted = 0";
			}
		}

		$query .= " left join $secQueryTempTableQuery as $table_name on {$condition}";
		return $query;
	}
	/** END * */

	/**
	 * This function handles the import for uitype 10 fieldtype
	 * @param string $module - the current module name
	 * @param string fieldname - the related to field name
	 */
	public function add_related_to($module, $fieldname)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$related_to = $this->column_fields[$fieldname];

		if (empty($related_to)) {
			return false;
		}

		//check if the field has module information; if not get the first module
		if (!strpos($related_to, "::::")) {
			$module = getFirstModule($module, $fieldname);
			$value = $related_to;
		} else {
			//check the module of the field
			$arr = [];
			$arr = explode("::::", $related_to);
			$module = $arr[0];
			$value = $arr[1];
		}

		$focus1 = CRMEntity::getInstance($module);

		$entityNameArr = \vtlib\Functions::getEntityModuleSQLColumnString($module);
		$entityName = $entityNameArr['fieldname'];
		$query = "SELECT vtiger_crmentity.deleted, $focus1->table_name.*
					FROM $focus1->table_name
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=$focus1->table_name.$focus1->table_index
						where $entityName=? and vtiger_crmentity.deleted=0";
		$result = $adb->pquery($query, array($value));

		if (!isset($this->checkFlagArr[$module])) {
			$this->checkFlagArr[$module] = (isPermitted($module, 'EditView', '') == 'yes');
		}

		if ($adb->num_rows($result) > 0) {
			//record found
			$focus1->id = $adb->query_result($result, 0, $focus1->table_index);
		} elseif ($this->checkFlagArr[$module]) {
			//record not found; create it
			$focus1->column_fields[$focus1->list_link_field] = $value;
			$focus1->column_fields['assigned_user_id'] = $current_user->id;
			$focus1->column_fields['modified_user_id'] = $current_user->id;
			$focus1->save($module);

			$last_import = new UsersLastImport();
			$last_import->assigned_user_id = $current_user->id;
			$last_import->bean_type = $module;
			$last_import->bean_id = $focus1->id;
			$last_import->save();
		} else {
			//record not found and cannot create
			$this->column_fields[$fieldname] = "";
			return false;
		}
		if (!empty($focus1->id)) {
			$this->column_fields[$fieldname] = $focus1->id;
			return true;
		} else {
			$this->column_fields[$fieldname] = "";
			return false;
		}
	}

	/**
	 * To keep track of action of field filtering and avoiding doing more than once.
	 *
	 * @var Array
	 */
	protected $__inactive_fields_filtered = false;

	/**
	 * Filter in-active fields based on type
	 *
	 * @param String $module
	 */
	public function filterInactiveFields($module)
	{
		if ($this->__inactive_fields_filtered) {
			return;
		}

		$adb = PearDatabase::getInstance();
		// Look for fields that has presence value NOT IN (0,2)
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module, array('1'));
		if ($cachedModuleFields === false) {
			// Initialize the fields calling suitable API
			getColumnFields($module);
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module, array('1'));
		}

		$hiddenFields = [];

		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				$fieldLabel = $fieldinfo['fieldlabel'];
				// NOTE: We should not translate the label to enable field diff based on it down
				$fieldName = $fieldinfo['fieldname'];
				$tableName = str_replace("vtiger_", "", $fieldinfo['tablename']);
				$hiddenFields[$fieldLabel] = array($tableName => $fieldName);
			}
		}

		if (isset($this->list_fields)) {
			$this->list_fields = array_diff_assoc($this->list_fields, $hiddenFields);
		}

		if (isset($this->search_fields)) {
			$this->search_fields = array_diff_assoc($this->search_fields, $hiddenFields);
		}

		// To avoid re-initializing everytime.
		$this->__inactive_fields_filtered = true;
	}

	/** END * */
	public function buildSearchQueryForFieldTypes($uitypes, $value = false)
	{
		$adb = PearDatabase::getInstance();

		if (!is_array($uitypes))
			$uitypes = array($uitypes);
		$module = get_class($this);

		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		if ($cachedModuleFields === false) {
			getColumnFields($module); // This API will initialize the cache as well
			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}

		$lookuptables = [];
		$lookupcolumns = [];
		foreach ($cachedModuleFields as $fieldinfo) {
			if (in_array($fieldinfo['uitype'], $uitypes)) {
				$lookuptables[] = $fieldinfo['tablename'];
				$lookupcolumns[] = $fieldinfo['columnname'];
			}
		}

		$entityfields = \vtlib\Functions::getEntityModuleSQLColumnString($module);
		$querycolumnnames = implode(',', $lookupcolumns);
		$entitycolumnnames = $entityfields['fieldname'];
		$query = "select crmid as id, $querycolumnnames, $entitycolumnnames as name ";
		$query .= " FROM $this->table_name ";
		$query .= " INNER JOIN vtiger_crmentity ON $this->table_name.$this->table_index = vtiger_crmentity.crmid && deleted = 0 ";

		//remove the base table
		$LookupTable = array_unique($lookuptables);
		$indexes = array_keys($LookupTable, $this->table_name);
		if (!empty($indexes)) {
			foreach ($indexes as $index) {
				unset($LookupTable[$index]);
			}
		}
		foreach ($LookupTable as $tablename) {
			$query .= " INNER JOIN $tablename
						on $this->table_name.$this->table_index = $tablename." . $this->tab_name_index[$tablename];
		}
		if (!empty($lookupcolumns) && $value !== false) {
			$query .= " WHERE ";
			$i = 0;
			$columnCount = count($lookupcolumns);
			foreach ($lookupcolumns as $columnname) {
				if (!empty($columnname)) {
					if ($i == 0 || $i == ($columnCount))
						$query .= sprintf("%s = '%s'", $columnname, $value);
					else
						$query .= sprintf(" || %s = '%s'", $columnname, $value);
					$i++;
				}
			}
		}
		if ($this->table_name == 'vtiger_leaddetails') {
			$query .= " && $this->table_name.converted = 0 ";
		}
		return $query;
	}

	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName)
	{
		if (strripos($tableName, 'rel') === (strlen($tableName) - 3)) {
			return 'LEFT JOIN';
		} else if ($tableName == 'vtiger_entity_stats' || $tableName == 'u_yf_openstreetmap') {
			return 'LEFT JOIN';
		}
		return 'INNER JOIN';
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	public function getNonAdminAccessQuery($module, $user, $parentRole, $userGroups)
	{
		$query = $this->getNonAdminUserAccessQuery($user, $parentRole, $userGroups);
		if (!empty($module)) {
			$moduleAccessQuery = $this->getNonAdminModuleAccessQuery($module, $user);
			if (!empty($moduleAccessQuery)) {
				$query .= " UNION $moduleAccessQuery";
			}
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	public function getNonAdminUserAccessQuery($user, $parentRole, $userGroups)
	{
		$query = "(SELECT $user->id as id) UNION (SELECT vtiger_user2role.userid AS userid FROM " .
			"vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid " .
			"INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE " .
			"vtiger_role.parentrole like '$parentRole::%')";
		if (count($userGroups) > 0) {
			$query .= " UNION (SELECT groupid FROM vtiger_groups where" .
				" groupid in (" . implode(",", $userGroups) . "))";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 */
	public function getNonAdminModuleAccessQuery($module, $user)
	{
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$tabId = \App\Module::getModuleId($module);
		$sharingRuleInfoVariable = $module . '_share_read_permission';
		$sharingRuleInfo = $$sharingRuleInfoVariable;
		$sharedTabId = null;
		$query = '';
		if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
			count($sharingRuleInfo['GROUP']) > 0)) {
			$query = " (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per " .
				"WHERE userid=$user->id && tabid=$tabId) UNION (SELECT " .
				"vtiger_tmp_read_group_sharing_per.sharedgroupid FROM " .
				"vtiger_tmp_read_group_sharing_per WHERE userid=$user->id && tabid=$tabId)";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups)
	{
		$module = null;
		if (!empty($tabId)) {
			$module = \App\Module::getModuleName($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key) ignore " .
			$query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, []);
		if (is_object($result)) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param String $module - module name for which query needs to be generated.
	 * @param Users $user - user for which query needs to be generated.
	 * @return String Access control Query for the user.
	 */
	public function getNonAdminAccessControlQuery($module, $user, $scope = '')
	{
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = \App\Module::getModuleId($module);
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . $user->id;
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
				count($sharingRuleInfo['GROUP']) > 0)) {
				$tableName = $tableName . '_t' . $tabId;
				$sharedTabId = $tabId;
			} elseif ($module == 'Calendar' || !empty($scope)) {
				$tableName .= '_t' . $tabId;
			}
			$this->setupTemporaryTable($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);
			// for secondary module we should join the records even if record is not there(primary module without related record)
			if ($scope == '') {
				$query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
					"vtiger_crmentity$scope.smownerid ";
			} else {
				$query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
					"vtiger_crmentity$scope.smownerid || vtiger_crmentity$scope.smownerid IS NULL";
			}
		}
		return $query;
	}

	public function listQueryNonAdminChange($query, $scope = '')
	{
		//make the module base table as left hand side table for the joins,
		//as mysql query optimizer puts crmentity on the left side and considerably slow down
		$query = preg_replace('/\s+/', ' ', $query);
		if (strripos($query, ' WHERE ') !== false) {
			vtlib_setup_modulevars($this->moduleName, $this);
			$query = str_ireplace(' where ', " WHERE $this->table_name.$this->table_index > 0  AND ", $query);
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param String $secmodule - $secmodule secondary module name
	 * @return Array returns the array with table names and fieldnames storing relations
	 * between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'Documents' => [
				'vtiger_senotesrel' => ['crmid', 'notesid'],
				$this->table_name => $this->table_index
			]
		];
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	/**
	 * Function to clear the fields which needs to be saved only once during the Save of the record
	 * For eg: Comments of HelpDesk should be saved only once during one save of a Trouble Ticket
	 */
	public function clearSingletonSaveFields()
	{
		return;
	}

	/**
	 * Function to track when a new record is linked to a given record
	 */
	public static function trackLinkedInfo($crmId)
	{
		$current_user = vglobal('current_user');
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => $current_user->id], ['crmid' => $crmId])->execute();
	}

	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	public function getSortOrder()
	{

		$currentModule = vglobal('currentModule');
		\App\Log::trace("Entering getSortOrder() method ...");
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::getForSql('sorder'));
		else
			$sorder = (($_SESSION[$currentModule . '_Sort_Order'] != '') ? ($_SESSION[$currentModule . '_Sort_Order']) : ($this->default_sort_order));
		\App\Log::trace("Exiting getSortOrder() method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'accountname')
	 */
	public function getOrderBy()
	{
		$currentModule = vglobal('currentModule');

		\App\Log::trace("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::getForSql('order_by'));
		else
			$order_by = (($_SESSION[$currentModule . '_Order_By'] != '') ? ($_SESSION[$currentModule . '_Order_By']) : ($use_default_order_by));
		\App\Log::trace("Exiting getOrderBy method ...");
		return $order_by;
	}
	// Mike Crowe Mod --------------------------------------------------------

	/**
	 * Function to track when a record is unlinked to a given record
	 */
	public function trackUnLinkedInfo($module, $crmId, $with_module, $with_crmid)
	{
		$current_user = vglobal('current_user');
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => $current_user->id], ['crmid' => $crmId])->execute();
	}

	/**
	 * Function which will give the basic query to find duplicates
	 * @param string $module
	 * @param string $tableColumns
	 * @param string $selectedColumns
	 * @param boolean $ignoreEmpty
	 * @return string
	 */
	public function getQueryForDuplicates($module, $tableColumns, $selectedColumns = '', $ignoreEmpty = false, $additionalColumns = '')
	{
		if (is_array($tableColumns)) {
			$tableColumnsString = implode(',', $tableColumns);
		}
		if (is_array($additionalColumns)) {
			$additionalColumns = implode(',', $additionalColumns);
		}
		if (!empty($additionalColumns)) {
			$additionalColumns = ',' . $additionalColumns;
		}
		$selectClause = sprintf('SELECT %s.%s AS recordid,%s%s', $this->table_name, $this->table_index, $tableColumnsString, $additionalColumns);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$fromClause = " FROM $this->table_name";

		$fromClause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		if ($this->tab_name) {
			foreach ($this->tab_name as $tableName) {
				if ($tableName != 'vtiger_crmentity' && $tableName != $this->table_name && $tableName != 'vtiger_inventoryproductrel') {
					if ($this->tab_name_index[$tableName]) {
						$fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
							" = $this->table_name.$this->table_index";
					}
				}
			}
		}
		$fromClause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$whereClause = " WHERE vtiger_crmentity.deleted = 0";
		$whereClause .= $this->getListViewSecurityParameter($module);

		if ($ignoreEmpty) {
			foreach ($tableColumns as $tableColumn) {
				$whereClause .= " AND ($tableColumn IS NOT NULL AND $tableColumn != '') ";
			}
		}

		if (isset($selectedColumns) && trim($selectedColumns) != '') {
			$sub_query = "SELECT $selectedColumns FROM $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $selectedColumns HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $tableColumnsString $additionalColumns $fromClause $whereClause GROUP BY $tableColumnsString HAVING COUNT(*)>1";
		}

		$i = 1;
		foreach ($tableColumns as $tableColumn) {
			$tableInfo = explode('.', $tableColumn);
			$duplicateCheckClause .= " ifnull($tableColumn,'null') = ifnull(temp.$tableInfo[1],'null')";
			if (count($tableColumns) != $i++)
				$duplicateCheckClause .= ' AND ';
		}

		$query = $selectClause . $fromClause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . $duplicateCheckClause .
			$whereClause .
			" ORDER BY $tableColumnsString," . $this->table_name . "." . $this->table_index . " ASC";
		return $query;
	}

	public function getLockFields()
	{
		if (isset($this->lockFields)) {
			return $this->lockFields;
		}
		return false;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String $moduleName Module name
	 * @param String $eventType Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		
	}
}
