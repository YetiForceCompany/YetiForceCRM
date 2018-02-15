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

class PBXManager extends CRMEntity
{
	protected $incominglinkLabel = 'Incoming Calls';
	protected $tabId = 0;
	protected $headerScriptLinkType = 'HEADERSCRIPT';
	protected $dependentModules = ['Contacts', 'Leads', 'Accounts'];
	public $db;
	public $table_name = 'vtiger_pbxmanager';
	public $table_index = 'pbxmanagerid';
	public $customFieldTable = ['vtiger_pbxmanagercf', 'pbxmanagerid'];
	public $tab_name = ['vtiger_crmentity', 'vtiger_pbxmanager', 'vtiger_pbxmanagercf'];
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_pbxmanager' => 'pbxmanagerid',
		'vtiger_pbxmanagercf' => 'pbxmanagerid', ];
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Call Status' => ['vtiger_pbxmanager', 'callstatus'],
		'Customer' => ['vtiger_pbxmanager', 'customer'],
		'User' => ['vtiger_pbxmanager', 'user'],
		'Recording' => ['vtiger_pbxmanager', 'recordingurl'],
		'Start Time' => ['vtiger_pbxmanager', 'starttime'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Call Status' => 'callstatus',
		'Customer' => 'customer',
		'User' => 'user',
		'Recording' => 'recordingurl',
		'Start Time' => 'starttime',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['callstatus', 'customer', 'user', 'recordingurl', 'starttime'];
	// Make the field link to detail view
	public $list_link_field = 'customernumber';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Customer' => ['vtiger_pbxmanager', 'customer'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'Customer' => 'customer',
	];
	// For Popup window record selection
	public $popup_fields = ['customernumber'];
	// For Alphabetical search
	public $def_basicsearch_col = 'customer';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'customernumber';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	//    public $mandatory_fields = Array('assigned_user_id');
	public $column_fields = [];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($modulename, $event_type)
	{
		if ($event_type === 'module.postinstall') {
			$this->addLinksForPBXManager();
			$this->registerLookupEvents();
			$this->addSettingsLinks();
			$this->addActionMapping();
			$this->setModuleRelatedDependencies();
			$this->addUserExtensionField();
		} elseif ($event_type === 'module.disabled') {
			$this->removeLinksForPBXManager();
			App\EventHandler::setInActive('PBXManager_PBXManagerHandler_Handler');
			$this->removeSettingsLinks();
			$this->removeActionMapping();
			$this->unsetModuleRelatedDependencies();
		} elseif ($event_type === 'module.enabled') {
			$this->addLinksForPBXManager();
			App\EventHandler::setActive('PBXManager_PBXManagerHandler_Handler');
			$this->addSettingsLinks();
			$this->addActionMapping();
			$this->setModuleRelatedDependencies();
		} elseif ($event_type === 'module.preuninstall') {
			$this->removeLinksForPBXManager();
			App\EventHandler::deleteHandler('PBXManager_PBXManagerHandler_Handler');
			$this->removeSettingsLinks();
			$this->removeActionMapping();
			$this->unsetModuleRelatedDependencies();
		}
	}

	/**
	 * To add a phone extension field in user preferences page.
	 */
	public function addUserExtensionField()
	{
		$module = vtlib\Module::getInstance('Users');
		if ($module) {
			$module->initTables();
			$blockInstance = vtlib\Block::getInstance('LBL_MORE_INFORMATION', $module->id);
			if ($blockInstance) {
				$fieldInstance = new vtlib\Field();
				$fieldInstance->name = 'phone_crm_extension';
				$fieldInstance->label = 'CRM Phone Extension';
				$fieldInstance->uitype = 11;
				$fieldInstance->typeofdata = 'V~O';
				$blockInstance->addField($fieldInstance);
			}
		}
		\App\Log::info('User Extension Field added');
	}

	/**
	 * To register phone lookup events.
	 */
	public function registerLookupEvents()
	{
		$className = 'PBXManager_PBXManagerHandler_Handler';

		App\EventHandler::registerHandler('EntityAfterSave', $className, 'Contacts,Accounts,Leads');
		App\EventHandler::registerHandler('EntityAfterDelete', $className, 'Contacts,Accounts,Leads');
		App\EventHandler::registerHandler('EntityChangeState', $className, 'Contacts,Accounts,Leads');
		\App\Log::info('Lookup Events Registered');
	}

	/**
	 * To add PBXManager module in module($this->dependentModules) related lists.
	 */
	public function setModuleRelatedDependencies()
	{
		$pbxmanager = vtlib\Module::getInstance('PBXManager');
		foreach ($this->dependentModules as $module) {
			$moduleInstance = vtlib\Module::getInstance($module);
			$moduleInstance->setRelatedList($pbxmanager, 'PBXManager', [], 'getDependentsList');
		}
		\App\Log::info('Successfully added Module Related lists');
	}

	/**
	 * To remove PBXManager module from module($this->dependentModules) related lists.
	 */
	public function unsetModuleRelatedDependencies()
	{
		$pbxmanager = vtlib\Module::getInstance('PBXManager');
		foreach ($this->dependentModules as $module) {
			$moduleInstance = vtlib\Module::getInstance($module);
			$moduleInstance->unsetRelatedList($pbxmanager, 'PBXManager', 'getDependentsList');
		}
		\App\Log::info('Successfully removed Module Related lists');
	}

	/**
	 * To add a link in vtiger_links which is to load our PBXManagerJS.js.
	 */
	public function addLinksForPBXManager()
	{
		$handlerInfo = ['path' => 'modules/PBXManager/PBXManager.php',
			'class' => 'PBXManager',
			'method' => 'checkLinkPermission', ];

		vtlib\Link::addLink($this->tabId, $this->headerScriptLinkType, $this->incominglinkLabel, 'modules/PBXManager/resources/PBXManagerJS.js', '', '', $handlerInfo);
		\App\Log::info('Links added');
	}

	/**
	 * To remove link for PBXManagerJS.js from vtiger_links.
	 */
	public function removeLinksForPBXManager()
	{
		//Deleting Headerscripts links
		vtlib\Link::deleteLink($this->tabId, $this->headerScriptLinkType, $this->incominglinkLabel, 'modules/PBXManager/resources/PBXManagerJS.js');
		\App\Log::info('Links Removed');
	}

	/**
	 * To add Integration->PBXManager block in Settings page.
	 */
	public function addSettingsLinks()
	{
		$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_INTEGRATION'])->scalar();
		// To add Block
		if (!$blockId) {
			$blockid = \App\Db::getInstance()->getUniqueID('vtiger_settings_blocks');
			$sequence = (new \App\Db\Query())->from('vtiger_settings_blocks')->max('sequence');
			\App\Db::getInstance()->createCommand()->insert('vtiger_settings_blocks', [
				'blockid' => $blockid,
				'label' => 'LBL_INTEGRATION',
				'sequence' => ++$sequence,
			])->execute();
		}
		Settings_Vtiger_Module_Model::addSettingsField('LBL_INTEGRATION', [
			'name' => 'LBL_PBXMANAGER',
			'iconpath' => 'adminIcon-pbx-manager',
			'description' => 'LBL_PBXMANAGER_DESCRIPTION',
			'linkto' => 'index.php?module=PBXManager&parent=Settings&view=Index',
		]);
		\App\Log::info('Settings Block and Field added');
	}

	/**
	 * To delete Integration->PBXManager block in Settings page.
	 */
	public function removeSettingsLinks()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_settings_field', ['name' => 'LBL_PBXMANAGER'])->execute();
		\App\Log::info('Settings Field Removed');
	}

	/**
	 * To enable(ReceiveIncomingCall & MakeOutgoingCall) tool in profile.
	 */
	public function addActionMapping()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$module = new vtlib\Module();
		$moduleInstance = $module->getInstance('PBXManager');

		//To add actionname as ReceiveIncomingcalls
		$actionId = (new \App\Db\Query())->from('vtiger_actionmapping')->max(new \yii\db\Expression('actionid + 1'));
		$dbCommand->insert('vtiger_actionmapping', ['actionid' => $actionId, 'actionname' => 'ReceiveIncomingCalls', 'securitycheck' => 0])->execute();
		$moduleInstance->enableTools('ReceiveIncomingcalls');
		\App\Log::info('ReceiveIncomingcalls ActionName Added');

		//To add actionname as MakeOutgoingCalls
		$actionId = (new \App\Db\Query())->from('vtiger_actionmapping')->max(new \yii\db\Expression('actionid + 1'));
		$dbCommand->insert('vtiger_actionmapping', ['actionid' => $actionId, 'actionname' => 'MakeOutgoingCalls', 'securitycheck' => 0])->execute();
		$moduleInstance->enableTools('MakeOutgoingCalls');
		\App\Log::info('MakeOutgoingCalls ActionName Added');
	}

	/**
	 * To remove(ReceiveIncomingCall & MakeOutgoingCall) tool from profile.
	 */
	public function removeActionMapping()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$module = new vtlib\Module();
		$moduleInstance = $module->getInstance('PBXManager');

		$moduleInstance->disableTools('ReceiveIncomingcalls');
		$dbCommand->delete('vtiger_actionmapping', ['actionname' => 'ReceiveIncomingCalls'])->execute();
		\App\Log::info('ReceiveIncomingcalls ActionName Removed');

		$moduleInstance->disableTools('MakeOutgoingCalls');
		$dbCommand->delete('vtiger_actionmapping', ['actionname' => 'MakeOutgoingCalls'])->execute();
		\App\Log::info('MakeOutgoingCalls ActionName Removed');
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
