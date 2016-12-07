<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */
require 'include/events/include.php';
require_once 'include/utils/utils.php';

class PBXManager extends CRMEntity
{

	protected $incominglinkLabel = 'Incoming Calls';
	protected $tabId = 0;
	protected $headerScriptLinkType = 'HEADERSCRIPT';
	protected $dependentModules = array('Contacts', 'Leads', 'Accounts');
	public $db;
	public $table_name = 'vtiger_pbxmanager';
	public $table_index = 'pbxmanagerid';
	public $customFieldTable = Array('vtiger_pbxmanagercf', 'pbxmanagerid');
	public $tab_name = Array('vtiger_crmentity', 'vtiger_pbxmanager', 'vtiger_pbxmanagercf');
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_pbxmanager' => 'pbxmanagerid',
		'vtiger_pbxmanagercf' => 'pbxmanagerid');
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Call Status' => Array('vtiger_pbxmanager', 'callstatus'),
		'Customer' => Array('vtiger_pbxmanager', 'customer'),
		'User' => Array('vtiger_pbxmanager', 'user'),
		'Recording' => Array('vtiger_pbxmanager', 'recordingurl'),
		'Start Time' => Array('vtiger_pbxmanager', 'starttime'),
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Call Status' => 'callstatus',
		'Customer' => 'customer',
		'User' => 'user',
		'Recording' => 'recordingurl',
		'Start Time' => 'starttime',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['callstatus', 'customer', 'user', 'recordingurl', 'starttime'];
	// Make the field link to detail view
	public $list_link_field = 'customernumber';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Customer' => Array('vtiger_pbxmanager', 'customer'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Customer' => 'customer',
	);
	// For Popup window record selection
	public $popup_fields = Array('customernumber');
	// For Alphabetical search
	public $def_basicsearch_col = 'customer';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'customernumber';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
//    public $mandatory_fields = Array('assigned_user_id');
	public $column_fields = Array();
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type)
	{
		if ($event_type === 'module.postinstall') {
			$this->addLinksForPBXManager();
			$this->registerLookupEvents();
			$this->addSettingsLinks();
			$this->addActionMapping();
			$this->setModuleRelatedDependencies();
			$this->addUserExtensionField();
		} else if ($event_type === 'module.disabled') {
			$this->removeLinksForPBXManager();
			App\EventHandler::setInActive('PBXManager_PBXManagerHandler_Handler');
			$this->removeSettingsLinks();
			$this->removeActionMapping();
			$this->unsetModuleRelatedDependencies();
		} else if ($event_type === 'module.enabled') {
			$this->addLinksForPBXManager();
			App\EventHandler::setActive('PBXManager_PBXManagerHandler_Handler');
			$this->addSettingsLinks();
			$this->addActionMapping();
			$this->setModuleRelatedDependencies();
		} else if ($event_type === 'module.preuninstall') {
			$this->removeLinksForPBXManager();
			App\EventHandler::deleteHandler('PBXManager_PBXManagerHandler_Handler');
			$this->removeSettingsLinks();
			$this->removeActionMapping();
			$this->unsetModuleRelatedDependencies();
		}
	}

	/**
	 * To add a phone extension field in user preferences page 
	 */
	public function addUserExtensionField()
	{

		$module = vtlib\Module::getInstance('Users');
		if ($module) {
			$module->initTables();
			$blockInstance = vtlib\Block::getInstance('LBL_MORE_INFORMATION', $module);
			if ($blockInstance) {
				$fieldInstance = new vtlib\Field();
				$fieldInstance->name = 'phone_crm_extension';
				$fieldInstance->label = 'CRM Phone Extension';
				$fieldInstance->uitype = 11;
				$fieldInstance->typeofdata = 'V~O';
				$blockInstance->addField($fieldInstance);
			}
		}
		\App\Log::error('User Extension Field added');
	}

	/**
	 * To register phone lookup events 
	 */
	public function registerLookupEvents()
	{
		$className = 'PBXManager_PBXManagerHandler_Handler';

		App\EventHandler::registerHandler('EntityAfterSave', $className, 'Contacts,Accounts,Leads');
		App\EventHandler::registerHandler('EntityAfterDelete', $className, 'Contacts,Accounts,Leads');
		App\EventHandler::registerHandler('EntityAfterRestore', $className, 'Contacts,Accounts,Leads');
		\App\Log::error('Lookup Events Registered');
	}

	/**
	 * To add PBXManager module in module($this->dependentModules) related lists
	 */
	public function setModuleRelatedDependencies()
	{

		$pbxmanager = vtlib\Module::getInstance('PBXManager');
		foreach ($this->dependentModules as $module) {
			$moduleInstance = vtlib\Module::getInstance($module);
			$moduleInstance->setRelatedList($pbxmanager, "PBXManager", array(), 'getDependentsList');
		}
		\App\Log::error('Successfully added Module Related lists');
	}

	/**
	 * To remove PBXManager module from module($this->dependentModules) related lists
	 */
	public function unsetModuleRelatedDependencies()
	{

		$pbxmanager = vtlib\Module::getInstance('PBXManager');
		foreach ($this->dependentModules as $module) {
			$moduleInstance = vtlib\Module::getInstance($module);
			$moduleInstance->unsetRelatedList($pbxmanager, "PBXManager", 'getDependentsList');
		}
		\App\Log::error('Successfully removed Module Related lists');
	}

	/**
	 * To add a link in vtiger_links which is to load our PBXManagerJS.js 
	 */
	public function addLinksForPBXManager()
	{

		$handlerInfo = array('path' => 'modules/PBXManager/PBXManager.php',
			'class' => 'PBXManager',
			'method' => 'checkLinkPermission');

		vtlib\Link::addLink($this->tabId, $this->headerScriptLinkType, $this->incominglinkLabel, 'modules/PBXManager/resources/PBXManagerJS.js', '', '', $handlerInfo);
		\App\Log::error('Links added');
	}

	/**
	 * To remove link for PBXManagerJS.js from vtiger_links
	 */
	public function removeLinksForPBXManager()
	{

		//Deleting Headerscripts links
		vtlib\Link::deleteLink($this->tabId, $this->headerScriptLinkType, $this->incominglinkLabel, 'modules/PBXManager/resources/PBXManagerJS.js');
		\App\Log::error('Links Removed');
	}

	/**
	 * To add Integration->PBXManager block in Settings page
	 */
	public function addSettingsLinks()
	{

		$adb = PearDatabase::getInstance();
		$integrationBlock = $adb->pquery('SELECT * FROM vtiger_settings_blocks WHERE label=?', array('LBL_INTEGRATION'));
		$integrationBlockCount = $adb->num_rows($integrationBlock);

		// To add Block
		if ($integrationBlockCount > 0) {
			$blockid = $adb->query_result($integrationBlock, 0, 'blockid');
		} else {
			$blockid = $adb->getUniqueID('vtiger_settings_blocks');
			$sequenceResult = $adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_blocks", array());
			if ($adb->num_rows($sequenceResult)) {
				$sequence = $adb->query_result($sequenceResult, 0, 'sequence');
			}
			$adb->pquery("INSERT INTO vtiger_settings_blocks(blockid, label, sequence) VALUES(?,?,?)", array($blockid, 'LBL_INTEGRATION', ++$sequence));
		}
		Settings_Vtiger_Module_Model::addSettingsField('LBL_INTEGRATION', [
			'name' => 'LBL_PBXMANAGER',
			'iconpath' => 'adminIcon-pbx-manager',
			'description' => 'LBL_PBXMANAGER_DESCRIPTION',
			'linkto' => 'index.php?module=PBXManager&parent=Settings&view=Index'
		]);
		\App\Log::error('Settings Block and Field added');
	}

	/**
	 * To delete Integration->PBXManager block in Settings page
	 */
	public function removeSettingsLinks()
	{

		$adb = PearDatabase::getInstance();
		$adb->pquery('DELETE FROM vtiger_settings_field WHERE name=?', array('LBL_PBXMANAGER'));
		\App\Log::error('Settings Field Removed');
	}

	/**
	 * To enable(ReceiveIncomingCall & MakeOutgoingCall) tool in profile
	 */
	public function addActionMapping()
	{

		$adb = PearDatabase::getInstance();
		$module = new vtlib\Module();
		$moduleInstance = $module->getInstance('PBXManager');

		//To add actionname as ReceiveIncomingcalls
		$maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM vtiger_actionmapping', array());
		if ($adb->num_rows($maxActionIdresult)) {
			$actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
		}
		$adb->pquery('INSERT INTO vtiger_actionmapping
                     (actionid, actionname, securitycheck) VALUES(?,?,?)', array($actionId, 'ReceiveIncomingCalls', 0));
		$moduleInstance->enableTools('ReceiveIncomingcalls');
		\App\Log::error('ReceiveIncomingcalls ActionName Added');

		//To add actionname as MakeOutgoingCalls
		$maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM vtiger_actionmapping', array());
		if ($adb->num_rows($maxActionIdresult)) {
			$actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
		}
		$adb->pquery('INSERT INTO vtiger_actionmapping
                     (actionid, actionname, securitycheck) VALUES(?,?,?)', array($actionId, 'MakeOutgoingCalls', 0));
		$moduleInstance->enableTools('MakeOutgoingCalls');
		\App\Log::error('MakeOutgoingCalls ActionName Added');
	}

	/**
	 * To remove(ReceiveIncomingCall & MakeOutgoingCall) tool from profile
	 */
	public function removeActionMapping()
	{

		$adb = PearDatabase::getInstance();
		$module = new vtlib\Module();
		$moduleInstance = $module->getInstance('PBXManager');

		$moduleInstance->disableTools('ReceiveIncomingcalls');
		$adb->pquery('DELETE FROM vtiger_actionmapping 
                     WHERE actionname=?', array('ReceiveIncomingCalls'));
		\App\Log::error('ReceiveIncomingcalls ActionName Removed');

		$moduleInstance->disableTools('MakeOutgoingCalls');
		$adb->pquery('DELETE FROM vtiger_actionmapping 
                      WHERE actionname=?', array('MakeOutgoingCalls'));
		\App\Log::error('MakeOutgoingCalls ActionName Removed');
	}

	public static function checkLinkPermission($linkData)
	{
		$module = new vtlib\Module();
		$moduleInstance = $module->getInstance('PBXManager');

		if ($moduleInstance) {
			return true;
		} else {
			return false;
		}
	}
}
