<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides API to work with vtiger CRM Modules
 * @package vtlib
 */
class Module extends ModuleBasic
{

	/**
	 * Function to get the Module/Tab id
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get related list sequence to use
	 * @return int
	 */
	public function __getNextRelatedListSequence()
	{
		return (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $this->id])->max('sequence') + 1;
	}

	/**
	 * Set related list information between other module
	 * @param Module Instance of target module with which relation should be setup
	 * @param String Label to display in related list (default is target module name)
	 * @param Array List of action button to show ('ADD', 'SELECT')
	 * @param String Callback function name of this module to use as handler
	 *
	 * @internal Creates table vtiger_crmentityrel if it does not exists
	 */
	public function setRelatedList($moduleInstance, $label = '', $actions = false, $functionName = 'getRelatedList')
	{
		$db = \App\Db::getInstance();

		if (empty($moduleInstance))
			return;
		if (empty($label)) {
			$label = $moduleInstance->name;
		}
		$isExists = (new \App\Db\Query())
			->select('relation_id')
			->from('vtiger_relatedlists')
			->where(['tabid' => $this->id, 'related_tabid' => $moduleInstance->id, 'name' => $functionName, 'label' => $label])
			->exists();
		if ($isExists) {
			self::log("Setting relation with $moduleInstance->name [$useactions_text] ... Error, the related module already exists");
			return;
		}

		$sequence = $this->__getNextRelatedListSequence();
		$presence = 0; // 0 - Enabled, 1 - Disabled
		// Allow ADD action of other module records (default)
		if ($actions === false)
			$actions = ['ADD'];

		$useactionsText = $actions;
		if (is_array($actions))
			$useactionsText = implode(',', $actions);
		$useactionsText = strtoupper($useactionsText);

		$db->createCommand()->insert('vtiger_relatedlists', [
			'tabid' => $this->id,
			'related_tabid' => $moduleInstance->id,
			'name' => $functionName,
			'sequence' => $sequence,
			'label' => $label,
			'presence' => $presence,
			'actions' => $useactionsText
		])->execute();

		if ($functionName === 'getManyToMany') {
			$refTableName = \Vtiger_Relation_Model::getReferenceTableInfo($moduleInstance->name, $this->name);
			$schema = $db->getSchema();
			if (!$schema->getTableSchema($refTableName['table'])) {
				$db->createTable($refTableName['table'], [
					'crmid' => 'int',
					'relcrmid' => 'int'
				]);
				$db->createCommand()->createIndex("{$refTableName['table']}_crmid_idx", $refTableName['table'], 'crmid')->execute();
				$db->createCommand()->createIndex("{$refTableName['table']}_relcrmid_idx", $refTableName['table'], 'relcrmid')->execute();
				$db->createCommand()->addForeignKey(
					"fk_1_{$refTableName['table']}", $refTableName['table'], 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'
				)->execute();
				$db->createCommand()->addForeignKey(
					"fk_2_{$refTableName['table']}", $refTableName['table'], 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'
				)->execute();
			}
		}
		self::log("Setting relation with $moduleInstance->name  ... DONE");
	}

	/**
	 * Unset related list information that exists with other module
	 * @param \Module Instance of target module with which relation should be setup
	 * @param string Label to display in related list (default is target module name)
	 * @param string Callback function name of this module to use as handler
	 */
	public function unsetRelatedList($moduleInstance, $label = '', $function_name = 'getRelatedList')
	{
		if (empty($moduleInstance))
			return;

		if (empty($label))
			$label = $moduleInstance->name;

		\App\Db::getInstance()->createCommand()->delete('vtiger_relatedlists', ['tabid' => $this->id, 'related_tabid' => $moduleInstance->id, 'name' => $function_name, 'label' => $label])->execute();
		self::log("Unsetting relation with $moduleInstance->name ... DONE");
	}

	/**
	 * Add custom link for a module page
	 * @param String Type can be like 'DETAILVIEW', 'LISTVIEW' etc..
	 * @param String Label to use for display
	 * @param String HREF value to use for generated link
	 * @param String Path to the image file (relative or absolute)
	 * @param Integer Sequence of appearance
	 *
	 * NOTE: $url can have variables like $MODULE (module for which link is associated),
	 * $RECORD (record on which link is dispalyed)
	 */
	public function addLink($type, $label, $url, $iconpath = '', $sequence = 0, $handlerInfo = null)
	{
		Link::addLink($this->id, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
	}

	/**
	 * Delete custom link of a module
	 * @param String Type can be like 'DETAILVIEW', 'LISTVIEW' etc..
	 * @param String Display label to lookup
	 * @param String URL value to lookup
	 */
	public function deleteLink($type, $label, $url = false)
	{
		Link::deleteLink($this->id, $type, $label, $url);
	}

	/**
	 * Get all the custom links related to this module.
	 */
	public function getLinks()
	{
		return Link::getAll($this->id);
	}

	/**
	 * Get all the custom links related to this module for exporting.
	 */
	public function getLinksForExport()
	{
		return Link::getAllForExport($this->id);
	}

	/**
	 * Initialize webservice setup for this module instance.
	 */
	public function initWebservice()
	{
		Webservice::initialize($this);
	}

	/**
	 * De-Initialize webservice setup for this module instance.
	 */
	public function deinitWebservice()
	{
		Webservice::uninitialize($this);
	}

	public function createFiles(Field $entityField)
	{
		$targetpath = 'modules/' . $this->name;

		if (!is_file($targetpath)) {
			$templatepath = 'vtlib/ModuleDir/BaseModule/';
			$flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
			$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($templatepath, $flags), \RecursiveIteratorIterator::SELF_FIRST);
			foreach ($objects as $name => $object) {
				$targetPath = str_replace($templatepath, '', $name);
				$targetPath = str_replace('_ModuleName_', $this->name, $targetPath);
				if (is_dir($name)) {
					if (!is_dir($targetPath)) {
						mkdir($targetPath);
					}
				} else {
					$fileContent = file_get_contents($name);
					$replacevars = [
						'<ModuleName>' => $this->name,
						'<ModuleLabel>' => $this->label,
						'<modulename>' => strtolower($this->name),
						'<entityfieldlabel>' => $entityField->label,
						'<entitycolumn>' => $entityField->column,
						'<entityfieldname>' => $entityField->name,
						'_ModuleName_' => $this->name,
					];
					foreach ($replacevars as $key => $value) {
						$fileContent = str_replace($key, addslashes($value), $fileContent);
					}
					file_put_contents($targetPath, $fileContent);
				}
			}
			$languages = \Users_Module_Model::getLanguagesList();
			$langFile = 'languages/en_us/' . $this->name . '.php';
			foreach ($languages as $key => $language) {
				if ($key !== 'en_us') {
					copy($langFile, 'languages/' . $key . '/' . $this->name . '.php');
				}
			}
		}
	}

	/**
	 * Get instance by id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value)
	{
		$instance = false;
		$data = Functions::getModuleData($value);
		if ($data) {
			$instance = new self();
			$instance->initialize($data);
		}
		return $instance;
	}

	/**
	 * Get instance of the module class.
	 * @param String Module name
	 */
	public static function getClassInstance($modulename)
	{
		if ($modulename == 'Calendar')
			$modulename = 'Activity';

		$instance = false;
		$filepath = "modules/$modulename/$modulename.php";
		if (Utils::checkFileAccessForInclusion($filepath, false)) {
			Deprecated::checkFileAccessForInclusion($filepath);
			include_once($filepath);
			if (class_exists($modulename)) {
				$instance = new $modulename();
			}
		}
		return $instance;
	}

	/**
	 * Fire the event for the module (if vtlib_handler is defined)
	 */
	public static function fireEvent($modulename, $eventType)
	{
		$return = true;
		$instance = self::getClassInstance((string) $modulename);
		if ($instance) {
			if (method_exists($instance, 'vtlib_handler')) {
				self::log("Invoking vtlib_handler for $eventType ...START");
				$fire = $instance->vtlib_handler((string) $modulename, (string) $eventType);
				if ($fire !== null && $fire !== true) {
					$return = false;
				}
				self::log("Invoking vtlib_handler for $eventType ...DONE");
			}
		}
		return $return;
	}

	/**
	 * Toggle the module (enable/disable)
	 */
	public static function toggleModuleAccess($moduleName, $enableDisable)
	{
		$eventType = false;
		if ($enableDisable === true) {
			$enableDisable = 0;
			$eventType = Module::EVENT_MODULE_ENABLED;
		} else if ($enableDisable === false) {
			$enableDisable = 1;
			$eventType = Module::EVENT_MODULE_DISABLED;
		}
		$fire = self::fireEvent($moduleName, $eventType);
		if ($fire) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['presence' => $enableDisable], ['name' => $moduleName])->execute();
			Deprecated::createModuleMetaFile();
			vtlib_RecreateUserPrivilegeFiles();
			$menuRecordModel = new \Settings_Menu_Record_Model();
			$menuRecordModel->refreshMenuFiles();
		}
	}
}
