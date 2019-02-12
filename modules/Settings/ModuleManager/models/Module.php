<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Settings_ModuleManager_Module_Model extends Vtiger_Module_Model
{
	/**
	 * @var string[] Base module tools.
	 */
	public static $baseModuleTools = ['Import', 'Export', 'Merge', 'CreateCustomFilter',
		'DuplicateRecord', 'MassEdit', 'MassArchived', 'MassActive', 'MassDelete', 'MassAddComment', 'MassTransferOwnership',
		'ReadRecord', 'WorkflowTrigger', 'Dashboard', 'CreateDashboardFilter', 'QuickExportToExcel', 'ExportPdf', 'RecordMapping',
		'RecordMappingList', 'FavoriteRecords', 'WatchingRecords', 'WatchingModule', 'RemoveRelation', 'ReviewingUpdates', 'OpenRecord'];

	/**
	 * @var array Base module tools exceptions.
	 */
	public static $baseModuleToolsExceptions = [
		'Documents' => ['notAllowed' => ['Import']],
		'Faq' => ['notAllowed' => ['Import', 'Export']],
		'OSSMailView' => ['notAllowed' => 'all'],
		'CallHistory' => ['allowed' => ['QuickExportToExcel']],
	];

	/**
	 * Get module base tools exceptions parse to ids.
	 *
	 * @return array
	 */
	public static function getBaseModuleToolsExceptions()
	{
		$exceptions = [];
		$actionIds = (new \App\Db\Query())->select(['actionname', 'actionid'])->from('vtiger_actionmapping')->createCommand()->queryAllByGroup();
		foreach (static::$baseModuleToolsExceptions as $moduleName => $moduleException) {
			foreach ($moduleException as $type => $exception) {
				if (is_array($exception)) {
					$moduleExceptions = [];
					foreach ($exception as $actionName) {
						$moduleExceptions[$actionIds[$actionName]] = $actionName;
					}
					$exceptions[App\Module::getModuleId($moduleName)][$type] = $moduleExceptions;
				} else {
					$exceptions[App\Module::getModuleId($moduleName)][$type] = false;
				}
			}
		}
		return $exceptions;
	}

	public static function getNonVisibleModulesList()
	{
		return ['ModTracker', 'Portal', 'Users', 'Integration',
			'ConfigEditor', 'FieldFormulas', 'VtigerBackup', 'CronTasks', 'Import', 'Tooltip',
			'Home', ];
	}

	/**
	 * Function to get the url of new module import.
	 */
	public static function getNewModuleImportUrl()
	{
		return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport';
	}

	/**
	 * Function to get the url of new module import.
	 */
	public static function getUserModuleImportUrl()
	{
		return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1';
	}

	/**
	 * Function to disable a module.
	 *
	 * @param type $moduleName - name of the module
	 */
	public function disableModule($moduleName)
	{
		//Handling events after disable module
		\vtlib\Module::toggleModuleAccess($moduleName, false);
	}

	/**
	 * Function to enable the module.
	 *
	 * @param type $moduleName -- name of the module
	 */
	public function enableModule($moduleName)
	{
		//Handling events after enable module
		\vtlib\Module::toggleModuleAccess($moduleName, true);
	}

	/**
	 * Function to check module name.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function checkModuleName($name): bool
	{
		return
			preg_match('/Settings/i', $name) ||
			preg_match('/Api/i', $name) ||
			preg_match('/Vtiger/i', $name) ||
			preg_match('/CustomView/i', $name) ||
			preg_match('/PickList/i', $name) ||
			preg_match('/[^A-Za-z]/i', $name);
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for all the modules.
	 *
	 * @return <Array> - List of Vtiger Module Model or sub class instances
	 */
	public static function getAll($presence = [], $restrictedModulesList = [], $isEntityType = false)
	{
		return parent::getAll([0, 1], self::getNonVisibleModulesList());
	}

	/**
	 * Function which will get count of modules.
	 *
	 * @param bool $onlyActive - if true get count of only active modules else all the modules
	 *
	 * @return <integer> number of modules
	 */
	public static function getModulesCount($onlyActive = false)
	{
		$query = (new \App\Db\Query())->from('vtiger_tab');
		if ($onlyActive) {
			$nonVisibleModules = self::getNonVisibleModulesList();
			$query->where(['and', ['presence' => 0], ['NOT IN', 'name', $nonVisibleModules]]);
		}
		return $query->count();
	}

	/**
	 * Function that returns all those modules that support Module Sequence Numbering.
	 *
	 * @return <Array of Vtiger_Module_Model>
	 */
	public static function getModulesSupportingSequenceNumbering()
	{
		$subQuery = (new \App\Db\Query())->select(['tabid'])->from('vtiger_field')->where(['uitype' => 4])->distinct('tabid');
		$dataReader = (new \App\Db\Query())->select(['tabid', 'name'])
			->from('vtiger_tab')
			->where(['isentitytype' => 1, 'presence' => 0, 'tabid' => $subQuery])
			->createCommand()->query();
		$moduleModels = [];
		while ($row = $dataReader->read()) {
			$moduleModels[$row['name']] = self::getInstanceFromArray($row);
		}
		$dataReader->close();

		return $moduleModels;
	}

	/**
	 * Function to get restricted modules list.
	 *
	 * @return array List module names
	 */
	public static function getActionsRestrictedModulesList()
	{
		return ['Home'];
	}

	public static function createModule($moduleInformation)
	{
		$moduleInformation['entityfieldname'] = strtolower(self::toAlphaNumeric($moduleInformation['entityfieldname']));

		$module = new vtlib\Module();
		$module->name = ucfirst($moduleInformation['module_name']);
		$module->label = $moduleInformation['module_label'];
		$module->type = (int) $moduleInformation['entitytype'];
		$module->save();
		$module->initTables();

		$block = new vtlib\Block();
		$block->label = 'LBL_BASIC_INFORMATION';
		$module->addBlock($block);

		$blockcf = new vtlib\Block();
		$blockcf->label = 'LBL_CUSTOM_INFORMATION';
		$module->addBlock($blockcf);

		$field1 = new vtlib\Field();
		$field1->name = $moduleInformation['entityfieldname'];
		$field1->label = $moduleInformation['entityfieldlabel'];
		$field1->uitype = 2;
		$field1->column = $field1->name;
		$field1->columntype = 'string(255)';
		$field1->typeofdata = 'V~M';
		$field1->maximumlength = '255';
		$block->addField($field1);

		$module->setEntityIdentifier($field1);

		/** Common fields that should be in every module, linked to vtiger CRM core table */
		$field2 = new vtlib\Field();
		$field2->name = 'number';
		$field2->label = 'FL_NUMBER';
		$field2->column = 'number';
		$field2->table = $module->basetable;
		$field2->uitype = 4;
		$field2->typeofdata = 'V~O';
		$field2->columntype = 'string(32)';
		$field2->maximumlength = '32';
		$block->addField($field2);

		$field3 = new vtlib\Field();
		$field3->name = 'assigned_user_id';
		$field3->label = 'Assigned To';
		$field3->table = 'vtiger_crmentity';
		$field3->column = 'smownerid';
		$field3->uitype = 53;
		$field3->typeofdata = 'V~M';
		$field3->maximumlength = '65535';
		$block->addField($field3);

		$field4 = new vtlib\Field();
		$field4->name = 'createdtime';
		$field4->label = 'Created Time';
		$field4->table = 'vtiger_crmentity';
		$field4->column = 'createdtime';
		$field4->uitype = 70;
		$field4->typeofdata = 'DT~O';
		$field4->displaytype = 2;
		$block->addField($field4);

		$field5 = new vtlib\Field();
		$field5->name = 'modifiedtime';
		$field5->label = 'Modified Time';
		$field5->table = 'vtiger_crmentity';
		$field5->column = 'modifiedtime';
		$field5->uitype = 70;
		$field5->typeofdata = 'DT~O';
		$field5->displaytype = 2;
		$block->addField($field5);

		$field6 = new vtlib\Field();
		$field6->name = 'created_user_id';
		$field6->label = 'Created By';
		$field6->table = 'vtiger_crmentity';
		$field6->column = 'smcreatorid';
		$field6->uitype = 53;
		$field6->typeofdata = 'V~O';
		$field6->displaytype = 2;
		$field6->quickcreate = 3;
		$field6->masseditable = 0;
		$field6->maximumlength = '65535';
		$block->addField($field6);

		// Create default custom filter (mandatory)
		$filter1 = new vtlib\Filter();
		$filter1->name = 'All';
		$filter1->isdefault = true;
		$filter1->presence = 0;
		$module->addFilter($filter1);
		// Add fields to the filter created
		$filter1->addField($field1)->addField($field2, 1)->addField($field3, 2)->addField($field4, 2);

		// Set sharing access of this module
		$module->setDefaultSharing();

		// Enable and Disable available tools
		$module->enableTools(static::$baseModuleTools);

		// Initialize Webservice support
		$module->initWebservice();

		// Create files
		$module->createFiles($field1);
		\App\Fields\RecordNumber::getInstance($module->id)->set('prefix', 'N')->set('cur_id', 1)->save();

		if ($module->type === 1) {
			\Vtiger_Inventory_Model::getInstance($module->name)->createInventoryTables();
		}
	}

	public static function toAlphaNumeric($value)
	{
		return preg_replace('/[^a-zA-Z0-9_]/', '', $value);
	}

	public static function getUploadDirectory()
	{
		return 'cache/vtlib';
	}
}
