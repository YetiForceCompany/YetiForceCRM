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
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';

class CRMEntity
{

	public $db;
	public $ownedby;

	/** 	Constructor which will set the column_fields in this object
	 */
	public function __construct()
	{
		$this->db = PearDatabase::getInstance();
		$this->column_fields = vtlib\Deprecated::getColumnFields(get_class($this));
	}

	public static function getInstance($module)
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

	/** Function to delete a record in the specifed table
	 * @param string $tableName -- table name:: Type varchar
	 * The function will delete a record. The id is obtained from the class variable $this->id and the columnname got from $this->tab_name_index[$table_name]
	 */
	public function deleteRelation($tableName)
	{
		if ((new App\Db\Query())->from($tableName)->where([$this->tab_name_index[$tableName] => $this->id])->exists()) {
			\App\Db::getInstance()->createCommand()->delete($tableName, [$this->tab_name_index[$tableName] => $this->id])->execute();
		}
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
	public function retrieveEntityInfo($record, $module)
	{
		if (!isset($record)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
		}

		// Tables which has multiple rows for the same record
		// will be skipped in record retrieve - need to be taken care separately.
		$multiRowTables = NULL;
		if (isset($this->multirow_tables)) {
			$multiRowTables = $this->multirow_tables;
		} else {
			$multiRowTables = [
				'vtiger_attachments',
			];
		}

		// Lookup module field cache
		if ($module == 'Calendar' || $module == 'Events') {
			vtlib\Deprecated::getColumnFields('Calendar');
			if (VTCacheUtils::lookupFieldInfoModule('Events'))
				$cachedEventsFields = VTCacheUtils::lookupFieldInfoModule('Events');
			else
				$cachedEventsFields = [];
			$cachedCalendarFields = VTCacheUtils::lookupFieldInfoModule('Calendar');
			$cachedModuleFields = array_merge($cachedEventsFields, $cachedCalendarFields);
			$module = 'Calendar';
		} else {
			$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
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
						(int) $tabid, $row['fieldname'], (int) $row['fieldid'], $row['fieldlabel'], $row['columnname'], $row['tablename'], (int) $row['uitype'], $row['typeofdata'], (int) $row['presence']
					);
				}
				// Get only active field information
				$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
			}
			$dataReader->close();
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
				throw new \App\Exceptions\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
			} else {
				foreach ($cachedModuleFields as $fieldInfo) {
					$fieldvalue = '';
					$fieldkey = $this->createColumnAliasForField($fieldInfo);
					//Note : value is retrieved with a tablename+fieldname as we are using alias while building query
					if (isset($resultRow[$fieldkey])) {
						$fieldvalue = $resultRow[$fieldkey];
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

	/**
	 * Function invoked during export of module record value.
	 */
	public function transformExportValue($key, $value)
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
		$result = $adb->pquery($sql, [$tabid]);
		$numRows = $adb->numRows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$fieldName = $adb->queryResult($result, $i, "fieldname");
			$this->required_fields[$fieldName] = 1;
		}
	}

	/**
	 * Function to unlink an entity with given Id from another entity
	 * @param int $id
	 * @param string $returnModule
	 * @param int $returnId
	 * @param boolean $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		switch ($relatedName) {
			case 'getManyToMany':
				$this->deleteRelatedM2M($id, $returnModule, $returnId);
				break;
			case 'getDependentsList':
				$this->deleteRelatedDependent($id, $returnModule, $returnId);
				break;
			case 'getRelatedList':
				$this->deleteRelatedFromDB($id, $returnModule, $returnId);
				break;
			default:
				$this->deleteRelatedDependent($id, $returnModule, $returnId);
				$this->deleteRelatedFromDB($id, $returnModule, $returnId);
				break;
		}
	}

	public function deleteRelatedDependent($crmid, $withModule, $withCrmid)
	{
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_field.tablename', 'vtiger_field.columnname', 'vtiger_tab.name'])
				->from('vtiger_field')
				->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $this->moduleName, 'relmodule' => $withModule])])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			App\Db::getInstance()->createCommand()
				->update($row['tablename'], [$row['columnname'] => 0], [$row['columnname'] => $withCrmid, CRMEntity::getInstance($row['name'])->table_index => $crmid])->execute();
		}
		$dataReader->close();
	}

	/**
	 * Function to remove relation M2M - for relation many to many
	 * @param string $module
	 * @param integer $crmid
	 * @param string $withModule
	 * @param integer $withCrmid
	 */
	public function deleteRelatedM2M($crmid, $withModule, $withCrmid)
	{
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($this->moduleName, $withModule);
		\App\Db::getInstance()->createCommand()->delete($referenceInfo['table'], [$referenceInfo['base'] => $withCrmid, $referenceInfo['rel'] => $crmid])->execute();
	}

	/**
	 *
	 * @param int $crmid
	 * @param string $withModule
	 * @param int $withCrmid
	 */
	public function deleteRelatedFromDB($crmid, $withModule, $withCrmid)
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

	public function updateMissingSeqNumber($module)
	{
		\App\Log::trace("Entered updateMissingSeqNumber function");
		\VtlibUtils::vtlibSetupModulevars($module, $this);
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
					$dataReader->close();
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
	 * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 * @param String function name
	 */
	public function saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName = false)
	{
		if (!is_array($withCrmid)) {
			$withCrmid = [$withCrmid];
		}
		switch ($relatedName) {
			case 'getManyToMany':
				$this->saveRelatedM2M($module, $crmid, $withModule, $withCrmid);
				break;
			case 'getDependentsList':
				break;
			case 'getActivities':
				break;
			default:
				$this->saveRelatedToDB($module, $crmid, $withModule, $withCrmid);
				break;
		}
	}

	/**
	 * Function to save relation between records in relation many to many
	 * @param string $module
	 * @param integer $crmid
	 * @param string $withModule
	 * @param int[] $withCrmid
	 */
	public function saveRelatedM2M($module, $crmid, $withModule, $withCrmid)
	{
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($module, $withModule);
		foreach ($withCrmid as $relcrmid) {
			// Relation already exists? No need to add again
			if ((new App\Db\Query())->from($referenceInfo['table'])
					->where([$referenceInfo['base'] => $relcrmid, $referenceInfo['rel'] => $crmid])
					->exists()) {
				continue;
			}
			\App\Db::getInstance()->createCommand()->insert($referenceInfo['table'], [
				$referenceInfo['base'] => $relcrmid,
				$referenceInfo['rel'] => $crmid
			])->execute();
		}
	}

	/**
	 * Function add info about relations
	 * @param string $module
	 * @param int $crmid
	 * @param string $withModule
	 * @param int[] $withCrmid
	 */
	public function saveRelatedToDB($module, $crmid, $withModule, $withCrmid)
	{
		foreach ($withCrmid as $relcrmid) {
			if ($withModule === 'Documents') {
				$checkpresence = (new \App\Db\Query())->select(['crmid'])->from('vtiger_senotesrel')->where(['crmid' => $crmid, 'notesid' => $relcrmid])->exists();
				if ($checkpresence) {
					continue;
				}
				\App\Db::getInstance()->createCommand()->insert('vtiger_senotesrel', [
					'crmid' => $crmid,
					'notesid' => $relcrmid
				])->execute();
			} else {
				$checkpresence = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentityrel')->where(['crmid' => $crmid, 'module' => $module, 'relcrmid' => $relcrmid, 'relmodule' => $withModule])->exists();
				if ($checkpresence) {
					continue;
				}
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
				' && relcrmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid=? && module=?)', [$transferId, $module, $entityId, $module]);
			while ($row = $db->getRow($relatedRecords)) {
				$where = 'relcrmid = ? && relmodule = ? && crmid = ? && module = ?';
				$params = [$row['relcrmid'], $row['relmodule'], $transferId, $module];
				$db->update('vtiger_crmentityrel', ['crmid' => $entityId], $where, $params);
			}
			// Pick the records to which the entity to be transfered is related, but do not pick the once to which current entity is already related.
			$parentRecords = $db->pquery('SELECT crmid, module FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?' .
				' && crmid NOT IN (SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid=? && relmodule=?)', [$transferId, $module, $entityId, $module]);
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
			$fields = App\Field::getRelatedFieldForModule(false, $module);
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

	/**
	 * Function to get the primary query part of a report for which generateReportsQuery Doesnt exist in module
	 * @param string $module
	 * @param ReportRunQueryPlanner $queryPlanner
	 * @return string
	 */
	public function generateReportsQuery($module, ReportRunQueryPlanner $queryPlanner)
	{
		$adb = PearDatabase::getInstance();
		$primary = CRMEntity::getInstance($module);

		\VtlibUtils::vtlibSetupModulevars($module, $primary);
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

		if ($adb->numRows($fields_query) > 0) {
			$rows_fields_query = $adb->numRows($fields_query);
			for ($i = 0; $i < $rows_fields_query; $i++) {
				$field_name = $adb->queryResult($fields_query, $i, 'fieldname');
				$field_id = $adb->queryResult($fields_query, $i, 'fieldid');
				$tab_name = $adb->queryResult($fields_query, $i, 'tablename');
				$ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", [$field_id]);

				if ($adb->numRows($ui10_modules_query) > 0) {

					// Capture the forward table dependencies due to dynamic related-field
					$crmentityRelModuleFieldTable = "vtiger_crmentityRel$module$field_id";

					$crmentityRelModuleFieldTableDeps = [];
					$countNumRows = $adb->numRows($ui10_modules_query);
					for ($j = 0; $j < $countNumRows; $j++) {
						$rel_mod = $adb->queryResult($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						\VtlibUtils::vtlibSetupModulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$crmentityRelModuleFieldTableDeps[] = $rel_tab_name . "Rel$module$field_id";
					}

					$matrix->setDependency($crmentityRelModuleFieldTable, $crmentityRelModuleFieldTableDeps);
					$matrix->addDependency($tab_name, $crmentityRelModuleFieldTable);

					if ($queryPlanner->requireTable($crmentityRelModuleFieldTable, $matrix)) {
						$relquery .= " left join vtiger_crmentity as $crmentityRelModuleFieldTable on $crmentityRelModuleFieldTable.crmid = $tab_name.$field_name and vtiger_crmentityRel$module$field_id.deleted=0";
					}

					$countNumRows = $adb->numRows($ui10_modules_query);
					for ($j = 0; $j < $countNumRows; $j++) {
						$rel_mod = $adb->queryResult($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						\VtlibUtils::vtlibSetupModulevars($rel_mod, $rel_obj);

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
		if ($queryPlanner->requireTable('vtiger_entity_stats') && strpos($query, 'vtiger_entity_stats.crmid') === false) {
			$query .= " inner join vtiger_entity_stats on $moduletable.$moduleindex = vtiger_entity_stats.crmid";
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

	/**
	 * Function to get the secondary query part of a report for which generateReportsSecQuery Doesnt exist in module
	 * @param string $module
	 * @param string $secmodule
	 * @param ReportRunQueryPlanner $queryPlanner
	 * @return string
	 */
	public function generateReportsSecQuery($module, $secmodule, ReportRunQueryPlanner $queryPlanner)
	{
		$adb = PearDatabase::getInstance();
		$secondary = CRMEntity::getInstance($secmodule);

		\VtlibUtils::vtlibSetupModulevars($secmodule, $secondary);

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

		$fields_query = $adb->pquery('SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid && vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)', [$secmodule]);

		if ($adb->numRows($fields_query) > 0) {
			$countFieldsQuery = $adb->numRows($fields_query);
			for ($i = 0; $i < $countFieldsQuery; $i++) {
				$field_name = $adb->queryResult($fields_query, $i, 'fieldname');
				$field_id = $adb->queryResult($fields_query, $i, 'fieldid');
				$tab_name = $adb->queryResult($fields_query, $i, 'tablename');
				$ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", [$field_id]);

				if ($adb->numRows($ui10_modules_query) > 0) {
					// Capture the forward table dependencies due to dynamic related-field
					$crmentityRelSecModuleTable = "vtiger_crmentityRel$secmodule$field_id";

					$crmentityRelSecModuleTableDeps = [];
					$rows_ui10_modules_query = $adb->numRows($ui10_modules_query);
					for ($j = 0; $j < $rows_ui10_modules_query; $j++) {
						$rel_mod = $adb->queryResult($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						\VtlibUtils::vtlibSetupModulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$crmentityRelSecModuleTableDeps[] = $rel_tab_name . "Rel$secmodule";
					}

					$matrix->setDependency($crmentityRelSecModuleTable, $crmentityRelSecModuleTableDeps);
					$matrix->addDependency($tab_name, $crmentityRelSecModuleTable);

					if ($queryPlanner->requireTable($crmentityRelSecModuleTable, $matrix)) {
						$relquery .= " left join vtiger_crmentity as $crmentityRelSecModuleTable on $crmentityRelSecModuleTable.crmid = $tab_name.$field_name and $crmentityRelSecModuleTable.deleted=0";
					}
					$countNumRows = $adb->numRows($ui10_modules_query);
					for ($j = 0; $j < $countNumRows; $j++) {
						$rel_mod = $adb->queryResult($ui10_modules_query, $j, 'relmodule');
						$rel_obj = CRMEntity::getInstance($rel_mod);
						\VtlibUtils::vtlibSetupModulevars($rel_mod, $rel_obj);

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
		$matrix->setDependency("vtiger_crmentity$secmodule", ["vtiger_groups$secmodule", "vtiger_users$secmodule", "vtiger_lastModifiedBy$secmodule"]);
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

	/**
	 * Function to get the relation query part of a report
	 * @param string $module
	 * @param string $secmodule
	 * @param string $table_name
	 * @param string $column_name
	 * @param ReportRunQueryPlanner $queryPlanner
	 * @return string
	 */
	public function getRelationQuery($module, $secmodule, $table_name, $column_name, ReportRunQueryPlanner $queryPlanner)
	{
		$tab = vtlib\Deprecated::getRelationTables($module, $secmodule);

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

		$secQueryTempTableQuery = $queryPlanner->registerTempTable($secQuery, [$column_name, $fields[1], $prifieldname]);

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
		// Look for fields that has presence value NOT IN (0,2)
		$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module, ['1']);
		if ($cachedModuleFields === false) {
			// Initialize the fields calling suitable API
			vtlib\Deprecated::getColumnFields($module);
			$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module, ['1']);
		}

		$hiddenFields = [];

		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				$fieldLabel = $fieldinfo['fieldlabel'];
				// NOTE: We should not translate the label to enable field diff based on it down
				$fieldName = $fieldinfo['fieldname'];
				$tableName = str_replace("vtiger_", "", $fieldinfo['tablename']);
				$hiddenFields[$fieldLabel] = [$tableName => $fieldName];
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
	public function getNonAdminAccessQuery($module, $parentRole, $userGroups)
	{
		$query = $this->getNonAdminUserAccessQuery($parentRole, $userGroups);
		if (!empty($module)) {
			$moduleAccessQuery = $this->getNonAdminModuleAccessQuery($module);
			if (!empty($moduleAccessQuery)) {
				$query .= " UNION $moduleAccessQuery";
			}
		}
		return $query;
	}

	/**
	 * The function retrieves access to queries for users without administrator rights
	 * @param Users $user
	 * @param string $parentRole
	 * @param array $userGroups
	 * @return string
	 */
	public function getNonAdminUserAccessQuery($parentRole, $userGroups)
	{
		$userId = \App\User::getCurrentUserId();
		$query = "(SELECT $userId as id) UNION (SELECT vtiger_user2role.userid AS userid FROM " .
			'vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid ' .
			'INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE ' .
			"vtiger_role.parentrole like '$parentRole::%')";
		if (count($userGroups) > 0) {
			$query .= ' UNION (SELECT groupid FROM vtiger_groups where' .
				' groupid in (' . implode(',', $userGroups) . '))';
		}
		return $query;
	}

	/**
	 * This function takes access to the module for users without administrator privileges
	 * @param string $module
	 * @param Users $user
	 * @return string
	 */
	public function getNonAdminModuleAccessQuery($module)
	{
		$userId = \App\User::getCurrentUserId();
		require('user_privileges/sharing_privileges_' . $userId . '.php');
		$tabId = \App\Module::getModuleId($module);
		$sharingRuleInfoVariable = $module . '_share_read_permission';
		$sharingRuleInfo = $$sharingRuleInfoVariable;
		$query = '';
		if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
			count($sharingRuleInfo['GROUP']) > 0)) {
			$query = " (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per " .
				"WHERE userid=$userId && tabid=$tabId) UNION (SELECT " .
				"vtiger_tmp_read_group_sharing_per.sharedgroupid FROM " .
				"vtiger_tmp_read_group_sharing_per WHERE userid=$userId && tabid=$tabId)";
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
	protected function setupTemporaryTable($tableName, $tabId, $parentRole, $userGroups)
	{
		$module = null;
		if (!empty($tabId)) {
			$module = \App\Module::getModuleName($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $parentRole, $userGroups);
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
	 * The function takes control access for users without administrator rights
	 * @param string $module - module name for which query needs to be generated.
	 * @param Users $user - user for which query needs to be generated.
	 * @param string $scope
	 * @return string - access control query for the user.
	 */
	public function getNonAdminAccessControlQuery($module, $scope = '')
	{
		require('user_privileges/user_privileges_' . \App\User::getCurrentUserId() . '.php');
		require('user_privileges/sharing_privileges_' . \App\User::getCurrentUserId() . '.php');
		$query = ' ';
		$tabId = \App\Module::getModuleId($module);
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . \App\User::getCurrentUserId();
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
			$this->setupTemporaryTable($tableName, $sharedTabId, $current_user_parent_role_seq, $current_user_groups);
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

	/**
	 * Returns the terms of non-administrator changes
	 * @param string $query
	 * @return string
	 */
	public function listQueryNonAdminChange($query)
	{
		//make the module base table as left hand side table for the joins,
		//as mysql query optimizer puts crmentity on the left side and considerably slow down
		$query = preg_replace('/\s+/', ' ', $query);
		if (strripos($query, ' WHERE ') !== false) {
			\VtlibUtils::vtlibSetupModulevars($this->moduleName, $this);
			$query = str_ireplace(' WHERE ', " WHERE $this->table_name.$this->table_index > 0  AND ", $query);
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param string $secModule - $secmodule secondary module name
	 * @return array returns the array with table names and fieldnames storing relations
	 * between module and this module
	 */

	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Documents' => [
				'vtiger_senotesrel' => ['crmid', 'notesid'],
				$this->table_name => $this->table_index
			],
			'OSSMailView' => [
				'vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'],
				$this->table_name => $this->table_index
			]
		];
		if ($secModule === false) {
			return $relTables;
		}
		return $relTables[$secModule];
	}

	/**
	 * Function to track when a new record is linked to a given record
	 */
	public static function trackLinkedInfo($crmId)
	{
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $crmId])->execute();
	}

	/**
	 * Function to track when a record is unlinked to a given record
	 * @param int $crmId
	 */
	public function trackUnLinkedInfo($crmId)
	{
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $crmId])->execute();
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
	 * @param string $moduleName Module name
	 * @param string $eventType Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($moduleName && $eventType === 'module.postinstall') {

		} else if ($eventType === 'module.disabled') {

		} else if ($eventType === 'module.preuninstall') {

		} else if ($eventType === 'module.preupdate') {

		} else if ($eventType === 'module.postupdate') {

		}
	}
}
