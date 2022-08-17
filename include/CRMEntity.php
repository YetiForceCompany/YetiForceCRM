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
 * Contributor(s): YetiForce S.A.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/vtigercrm/data/CRMEntity.php,v 1.16 2005/04/29 04:21:31 mickie Exp $
 * Description:  Defines the base class for all data entities used throughout the
 * application.  The base class including its methods and variables is designed to
 * be overloaded with module-specific methods and variables particular to the
 * module's base entity class.
 * ****************************************************************************** */
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
	/** @var string[] Tables join clause. */
	public $tableJoinClause = [
		'vtiger_entity_stats' => 'LEFT JOIN',
		'u_yf_openstreetmap' => 'LEFT JOIN',
		'u_yf_wapro_records_map' => 'LEFT JOIN',
	];

	/**
	 * Constructor which will set the column_fields in this object.
	 */
	public function __construct()
	{
		$this->column_fields = vtlib\Deprecated::getColumnFields(static::class);
	}

	/**
	 * Get CRMEntity instance.
	 *
	 * @param string $module
	 *
	 * @return self
	 */
	public static function getInstance(string $module)
	{
		if (is_numeric($module)) {
			$module = App\Module::getModuleName($module);
		}
		if (\App\Cache::staticHas('CRMEntity', $module)) {
			return clone \App\Cache::staticGet('CRMEntity', $module);
		}
		// File access security check
		if (!class_exists($module)) {
			if (App\Config::performance('LOAD_CUSTOM_FILES') && file_exists("custom/modules/$module/$module.php")) {
				\vtlib\Deprecated::checkFileAccessForInclusion("custom/modules/$module/$module.php");
				require_once "custom/modules/$module/$module.php";
			} else {
				\vtlib\Deprecated::checkFileAccessForInclusion("modules/$module/$module.php");
				require_once "modules/$module/$module.php";
			}
		}
		$focus = new $module();
		$focus->moduleName = $module;
		if (method_exists($focus, 'init')) {
			$focus->init();
		}
		\App\Cache::staticSave('CRMEntity', $module, clone $focus);
		return $focus;
	}

	/**
	 * Loading the system configuration.
	 *
	 * @return void
	 */
	protected function init(): void
	{
		$this->tab_name_index += ['u_yf_wapro_records_map' => 'crmid'];
	}

	/**
	 * Function returns the column alias for a field.
	 *
	 * @param array $fieldInfo - field information
	 *
	 * @return string field value
	 */
	protected function createColumnAliasForField(array $fieldInfo)
	{
		return strtolower($fieldInfo['tablename'] . $fieldInfo['fieldname']);
	}

	/**
	 * Retrieve record information of the module.
	 *
	 * @param int    $record - crmid of record
	 * @param string $module - module name
	 */
	public function retrieveEntityInfo(int $record, string $module)
	{
		if (!isset($record)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_RECORD_NOT_FOUND');
		}
		if ($cachedModuleFields = \App\Field::getModuleFieldInfosByPresence($module)) {
			$query = new \App\Db\Query();
			$tabNameIndex = $this->tab_name_index; // copies-on-write
			$requiredTables = $columnClause = [];
			foreach ($cachedModuleFields as $fieldInfo) {
				if (isset($tabNameIndex[$fieldInfo['tablename']])) {
					if (!isset($requiredTables[$fieldInfo['tablename']])) {
						$requiredTables[$fieldInfo['tablename']] = $tabNameIndex[$fieldInfo['tablename']];
					}
					// Alias prefixed with tablename+fieldname to avoid duplicate column name across tables
					// fieldname are always assumed to be unique for a module
					$columnClause[] = $fieldInfo['tablename'] . '.' . $fieldInfo['columnname'] . ' AS ' . $this->createColumnAliasForField($fieldInfo);
				}
			}
			$columnClause[] = 'vtiger_crmentity.deleted';
			$query->select($columnClause);
			$query->from('vtiger_crmentity');
			if (isset($requiredTables['vtiger_crmentity'])) {
				unset($requiredTables['vtiger_crmentity']);
			}
			foreach ($requiredTables as $tableName => $tableIndex) {
				$query->leftJoin($tableName, "vtiger_crmentity.crmid = $tableName.$tableIndex");
			}
			$query->where(['vtiger_crmentity.crmid' => $record]);
			if ('' != $module) {
				$query->andWhere(['vtiger_crmentity.setype' => $module]);
			}
			$resultRow = $query->one();
			if (empty($resultRow)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_RECORD_NOT_FOUND||' . $record);
			}
			foreach ($cachedModuleFields as $fieldInfo) {
				$fieldvalue = '';
				$fieldkey = $this->createColumnAliasForField($fieldInfo);
				//Note : value is retrieved with a tablename+fieldname as we are using alias while building query
				if (isset($resultRow[$fieldkey])) {
					$fieldvalue = $resultRow[$fieldkey];
				}
				if (120 === $fieldInfo['uitype']) {
					$fieldvalue = \App\Fields\SharedOwner::getById($record);
					if (\is_array($fieldvalue)) {
						$fieldvalue = implode(',', $fieldvalue);
					}
				}
				$this->column_fields[$fieldInfo['fieldname']] = $fieldvalue;
			}
		}
		$this->column_fields['record_id'] = $record;
		$this->column_fields['record_module'] = $module;
	}

	/**
	 * Get table join clause by table name.
	 *
	 * @param string $tableName
	 *
	 * @return string
	 */
	public function getJoinClause($tableName): string
	{
		if (strripos($tableName, 'rel') === (\strlen($tableName) - 3)) {
			return 'LEFT JOIN';
		}
		if (isset($this->tableJoinClause[$tableName])) {
			return $this->tableJoinClause[$tableName];
		}
		return 'INNER JOIN';
	}

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param string $secModule - $secmodule secondary module name
	 *
	 * @return array returns the array with table names and fieldnames storing relations
	 *               between module and this module
	 */
	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Documents' => [
				'vtiger_senotesrel' => ['crmid', 'notesid'],
				$this->table_name => $this->table_index,
			],
			'OSSMailView' => [
				'vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'],
				$this->table_name => $this->table_index,
			],
		];
		if (false === $secModule) {
			return $relTables;
		}
		return $relTables[$secModule] ?? [];
	}

	/**
	 * Function to track when a new record is linked to a given record.
	 *
	 * @param mixed $crmId
	 */
	public static function trackLinkedInfo($crmId)
	{
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $crmId])->execute();
	}

	/**
	 * Function to track when a record is unlinked to a given record.
	 *
	 * @param int $crmId
	 */
	public function trackUnLinkedInfo($crmId)
	{
		$currentTime = date('Y-m-d H:i:s');
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['modifiedtime' => $currentTime, 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $crmId])->execute();
	}

	/**
	 * Gets fields to locking record.
	 *
	 * @return array
	 */
	public function getLockFields()
	{
		if (isset($this->lockFields)) {
			return $this->lockFields;
		}
		return [];
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($moduleName && 'module.postinstall' === $eventType) {
		} elseif ('module.disabled' === $eventType) {
		} elseif ('module.preuninstall' === $eventType) {
		} elseif ('module.preupdate' === $eventType) {
		} elseif ('module.postupdate' === $eventType) {
		}
	}
}
