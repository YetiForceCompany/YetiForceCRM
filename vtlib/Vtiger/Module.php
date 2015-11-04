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

require_once 'include/runtime/Cache.php';
include_once('vtlib/Vtiger/ModuleBasic.php');

/**
 * Provides API to work with vtiger CRM Modules
 * @package vtlib
 */
class Vtiger_Module extends Vtiger_ModuleBasic
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
	 * Get unique id for related list
	 * @access private
	 */
	function __getRelatedListUniqueId()
	{
		$adb = PearDatabase::getInstance();
		return $adb->getUniqueID('vtiger_relatedlists');
	}

	/**
	 * Get related list sequence to use
	 * @access private
	 */
	function __getNextRelatedListSequence()
	{
		$adb = PearDatabase::getInstance();
		$max_sequence = 0;
		$result = $adb->pquery("SELECT max(sequence) as maxsequence FROM vtiger_relatedlists WHERE tabid=?", Array($this->id));
		if ($adb->num_rows($result))
			$max_sequence = $adb->query_result($result, 0, 'maxsequence');
		return ++$max_sequence;
	}

	/**
	 * Set related list information between other module
	 * @param Vtiger_Module Instance of target module with which relation should be setup
	 * @param String Label to display in related list (default is target module name)
	 * @param Array List of action button to show ('ADD', 'SELECT')
	 * @param String Callback function name of this module to use as handler
	 *
	 * @internal Creates table vtiger_crmentityrel if it does not exists
	 */
	function setRelatedList($moduleInstance, $label = '', $actions = false, $function_name = 'get_related_list')
	{
		$adb = PearDatabase::getInstance();

		if (empty($moduleInstance))
			return;
		if (empty($label))
			$label = $moduleInstance->name;

		$result = $adb->pquery("SELECT relation_id FROM vtiger_relatedlists WHERE tabid=? AND related_tabid = ? AND name = ? AND label = ?;", Array($this->id, $moduleInstance->id, $function_name, $label));
		if ($adb->num_rows($result) > 0) {
			self::log("Setting relation with $moduleInstance->name [$useactions_text] ... Error, the related module already exists");
			return;
		}

		$relation_id = $this->__getRelatedListUniqueId();
		$sequence = $this->__getNextRelatedListSequence();
		$presence = 0; // 0 - Enabled, 1 - Disabled
		// Allow ADD action of other module records (default)
		if ($actions === false)
			$actions = Array('ADD');

		$useactions_text = $actions;
		if (is_array($actions))
			$useactions_text = implode(',', $actions);
		$useactions_text = strtoupper($useactions_text);

		$adb->pquery("INSERT INTO vtiger_relatedlists(relation_id,tabid,related_tabid,name,sequence,label,presence,actions) VALUES(?,?,?,?,?,?,?,?)", Array($relation_id, $this->id, $moduleInstance->id, $function_name, $sequence, $label, $presence, $useactions_text));

		self::log("Setting relation with $moduleInstance->name [$useactions_text] ... DONE");
	}

	/**
	 * Unset related list information that exists with other module
	 * @param Vtiger_Module Instance of target module with which relation should be setup
	 * @param String Label to display in related list (default is target module name)
	 * @param String Callback function name of this module to use as handler
	 */
	function unsetRelatedList($moduleInstance, $label = '', $function_name = 'get_related_list')
	{
		$adb = PearDatabase::getInstance();

		if (empty($moduleInstance))
			return;

		if (empty($label))
			$label = $moduleInstance->name;

		$adb->pquery("DELETE FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=? AND name=? AND label=?", Array($this->id, $moduleInstance->id, $function_name, $label));

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
	function addLink($type, $label, $url, $iconpath = '', $sequence = 0, $handlerInfo = null)
	{
		Vtiger_Link::addLink($this->id, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
	}

	/**
	 * Delete custom link of a module
	 * @param String Type can be like 'DETAILVIEW', 'LISTVIEW' etc..
	 * @param String Display label to lookup
	 * @param String URL value to lookup
	 */
	function deleteLink($type, $label, $url = false)
	{
		Vtiger_Link::deleteLink($this->id, $type, $label, $url);
	}

	/**
	 * Get all the custom links related to this module.
	 */
	function getLinks()
	{
		return Vtiger_Link::getAll($this->id);
	}

	/**
	 * Get all the custom links related to this module for exporting.
	 */
	function getLinksForExport()
	{
		return Vtiger_Link::getAllForExport($this->id);
	}

	/**
	 * Initialize webservice setup for this module instance.
	 */
	function initWebservice()
	{
		Vtiger_Webservice::initialize($this);
	}

	/**
	 * De-Initialize webservice setup for this module instance.
	 */
	function deinitWebservice()
	{
		Vtiger_Webservice::uninitialize($this);
	}

	function createFiles(Vtiger_Field $entityField)
	{
		$targetpath = 'modules/' . $this->name;

		if (!is_file($targetpath)) {
			$templatepath = 'vtlib/ModuleDir/BaseModule/';
			$flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templatepath, $flags), RecursiveIteratorIterator::SELF_FIRST);
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
			$languages = Users_Module_Model::getLanguagesList();
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
	static function getInstance($value)
	{
		$instance = false;
		$data = Vtiger_Functions::getModuleData($value);
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
	static function getClassInstance($modulename)
	{
		if ($modulename == 'Calendar')
			$modulename = 'Activity';

		$instance = false;
		$filepath = "modules/$modulename/$modulename.php";
		if (Vtiger_Utils::checkFileAccessForInclusion($filepath, false)) {
			checkFileAccessForInclusion($filepath);
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
	static function fireEvent($modulename, $event_type)
	{
		$instance = self::getClassInstance((string) $modulename);
		if ($instance) {
			if (method_exists($instance, 'vtlib_handler')) {
				self::log("Invoking vtlib_handler for $event_type ...START");
				$instance->vtlib_handler((string) $modulename, (string) $event_type);
				self::log("Invoking vtlib_handler for $event_type ...DONE");
			}
		}
	}
}
