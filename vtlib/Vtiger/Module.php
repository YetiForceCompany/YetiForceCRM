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
	 * @return <Number>
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get related list sequence to use
	 * @access private
	 */
	public function __getNextRelatedListSequence()
	{
		$adb = \PearDatabase::getInstance();
		$max_sequence = 0;
		$result = $adb->pquery("SELECT max(sequence) as maxsequence FROM vtiger_relatedlists WHERE tabid=?", Array($this->id));
		if ($adb->num_rows($result))
			$max_sequence = $adb->query_result($result, 0, 'maxsequence');
		return ++$max_sequence;
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
	public function setRelatedList($moduleInstance, $label = '', $actions = false, $functionName = 'get_related_list')
	{
		$adb = \PearDatabase::getInstance();

		if (empty($moduleInstance))
			return;
		if (empty($label))
			$label = $moduleInstance->name;

		$result = $adb->pquery('SELECT relation_id FROM vtiger_relatedlists WHERE tabid=? && related_tabid = ? && name = ? && label = ?;', [$this->id, $moduleInstance->id, $functionName, $label]);
		if ($result->rowCount() > 0) {
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

		$adb->insert('vtiger_relatedlists', [
			'relation_id' => $adb->getUniqueID('vtiger_relatedlists'),
			'tabid' => $this->id,
			'related_tabid' => $moduleInstance->id,
			'name' => $functionName,
			'sequence' => $sequence,
			'label' => $label,
			'presence' => $presence,
			'actions' => $useactionsText,
		]);

		if ($functionName == 'get_many_to_many') {
			$refTableName = \Vtiger_Relation_Model::getReferenceTableInfo($moduleInstance->name, $this->name);
			if (!Utils::CheckTable($refTableName['table'])) {
				Utils::CreateTable(
					$refTableName['table'], '(crmid INT(19) ,relcrmid INT(19),KEY crmid (crmid),KEY relcrmid (relcrmid),'
					. ' CONSTRAINT `' . $refTableName['table'] . '_ibfk_1` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,'
					. ' CONSTRAINT `' . $refTableName['table'] . '_ibfk_2` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE)', true);
			}
		}
		self::log("Setting relation with $moduleInstance->name  ... DONE");
	}

	/**
	 * Unset related list information that exists with other module
	 * @param Module Instance of target module with which relation should be setup
	 * @param String Label to display in related list (default is target module name)
	 * @param String Callback function name of this module to use as handler
	 */
	public function unsetRelatedList($moduleInstance, $label = '', $function_name = 'get_related_list')
	{
		$adb = \PearDatabase::getInstance();

		if (empty($moduleInstance))
			return;

		if (empty($label))
			$label = $moduleInstance->name;

		$adb->pquery("DELETE FROM vtiger_relatedlists WHERE tabid=? && related_tabid=? && name=? && label=?", Array($this->id, $moduleInstance->id, $function_name, $label));

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
						$fileContent = str_replace($key, $value, $fileContent);
					}
					file_put_contents($targetPath, $fileContent);
				}
			}
			$languages = \Users_Module_Model::getLanguagesList();
			$langFile = 'languages/en_us/' . $this->name . '.php';
			foreach ($languages as $key => $language) {
				if ($key != 'en_us') {
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
		$return = false;
		$instance = self::getClassInstance((string) $modulename);
		if ($instance) {
			if (method_exists($instance, 'vtlib_handler')) {
				self::log("Invoking vtlib_handler for $eventType ...START");
				$fire = $instance->vtlib_handler((string) $modulename, (string) $eventType);
				if ($fire === null || $fire === true) {
					$return = true;
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
			$db = \PearDatabase::getInstance();
			$db->update('vtiger_tab', [
				'presence' => $enableDisable
				], 'name = ?', [$moduleName]
			);
			Deprecated::createModuleMetaFile();
			vtlib_RecreateUserPrivilegeFiles();
			$menuRecordModel = new \Settings_Menu_Record_Model();
			$menuRecordModel->refreshMenuFiles();
		}
	}
}
