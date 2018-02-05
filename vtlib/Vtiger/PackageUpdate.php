<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides API to update module into vtiger CRM
 * @package vtlib
 */
class PackageUpdate extends PackageImport
{

	public $_migrationinfo = false;
	public $listFields = [];
	public $listBlocks = [];

	/**
	 * Initialize Update
	 * @access private
	 */
	public function initUpdate($moduleInstance, $zipfile, $overwrite)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if (!$moduleInstance || $moduleInstance->name != $module) {
			self::log('Module name mismatch!');
			return false;
		}
		if ($module !== null) {
			$zip = new \App\Zip($zipfile, ['checkFiles' => false]);
			if ($zip->statName("$module.png")) {
				$zip->unzipFile("$module.png", 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/skins/images/$module.png");
			}
			$zip->unzip([
				// Templates folder
				'templates' => 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/$module",
				// Cron folder
				'cron' => "cron/modules/$module",
				// Config
				'config' => 'config/modules',
				// Modules folder
				'modules' => 'modules',
				// Settings folder
				'settings/actions' => "modules/Settings/$module/actions",
				'settings/views' => "modules/Settings/$module/views",
				'settings/models' => "modules/Settings/$module/models",
				// Settings templates folder
				'settings/templates' => 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/$module",
				//module images
				'images' => 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/images/$module",
				'updates' => 'cache/updates',
				'layouts' => 'layouts'
			]);
			// If data is not yet available
			if (empty($this->_modulexml)) {
				$this->__parseManifestFile($zip);
			}
		}
		return $module;
	}

	/**
	 * Update Module from zip file
	 * @param Module Instance of the module to update
	 * @param String Zip file name
	 * @param Boolean True for overwriting existing module
	 */
	public function update($moduleInstance, $zipfile, $overwrite = true)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if ($module !== null) {
			$zip = new \App\Zip($zipfile, ['checkFiles' => false]);
			// If data is not yet available
			if (empty($this->_modulexml)) {
				$this->__parseManifestFile($zip);
			}

			$buildModuleArray = [];
			$installSequenceArray = [];
			$moduleBundle = (boolean) $this->_modulexml->modulebundle;
			if ($moduleBundle === true) {
				$moduleList = (Array) $this->_modulexml->modulelist;
				foreach ($moduleList as $moduleInfos) {
					foreach ($moduleInfos as $moduleInfo) {
						$moduleInfo = (Array) $moduleInfo;
						$buildModuleArray[] = $moduleInfo;
						$installSequenceArray[] = $moduleInfo['install_sequence'];
					}
				}
				sort($installSequenceArray);
				$zip->unzip($this->getTemporaryFilePath());
				foreach ($installSequenceArray as $sequence) {
					foreach ($buildModuleArray as $moduleInfo) {
						if ($moduleInfo['install_sequence'] == $sequence) {
							$moduleInstance = Module::getInstance($moduleInfo['name']);
							$this->update($moduleInstance, $this->getTemporaryFilePath($moduleInfo['filepath']), $overwrite);
						}
					}
				}
			} else {
				if (!$moduleInstance || $moduleInstance->name != $module) {
					self::log('Module name mismatch!');
					return false;
				}
				$module = $this->initUpdate($moduleInstance, $zipfile, $overwrite);
				// Call module update function
				$this->updateModule($moduleInstance);
			}
			unlink($zipfile);
		}
	}

	/**
	 * Update Module
	 * @access private
	 */
	public function updateModule($moduleInstance)
	{
		$tablabel = $this->_modulexml->label;
		$tabversion = $this->_modulexml->version;
		Module::fireEvent($moduleInstance->name, Module::EVENT_MODULE_PREUPDATE);
		$moduleInstance->label = $tablabel;
		$moduleInstance->save();

		$this->handleMigration($this->_modulexml, $moduleInstance);
		$this->updateTables($this->_modulexml);
		$this->updateBlocks($this->_modulexml, $moduleInstance);
		$this->updateCustomViews($this->_modulexml, $moduleInstance);
		$this->updateSharingAccess($this->_modulexml, $moduleInstance);
		$this->updateEvents($this->_modulexml, $moduleInstance);
		$this->updateActions($this->_modulexml, $moduleInstance);
		$this->updateRelatedLists($this->_modulexml, $moduleInstance);
		$this->updateCustomLinks($this->_modulexml, $moduleInstance);
		$this->updateCronTasks($this->_modulexml);
		$moduleInstance->__updateVersion($tabversion);

		Module::fireEvent($moduleInstance->name, Module::EVENT_MODULE_POSTUPDATE);
	}

	/**
	 * Parse migration information from manifest
	 * @access private
	 */
	public function parseMigration($modulenode)
	{
		if (!$this->_migrations) {
			$this->_migrations = [];
			if (!empty($modulenode->migrations) &&
				!empty($modulenode->migrations->migration)) {
				foreach ($modulenode->migrations->migration as $migrationnode) {
					$migrationattrs = $migrationnode->attributes();
					$migrationversion = $migrationattrs['version'];
					$this->_migrations["$migrationversion"] = $migrationnode;
				}
			}
			// Sort the migration details based on version
			if (count($this->_migrations) > 1) {
				uksort($this->_migrations, 'version_compare');
			}
		}
	}

	/**
	 * Handle migration of the module
	 * @access private
	 */
	public function handleMigration($modulenode, $moduleInstance)
	{
		$this->parseMigration($modulenode);
		$cur_version = $moduleInstance->version;
		foreach ($this->_migrations as $migversion => $migrationnode) {
			// Perform migration only for higher version than current
			if (version_compare($cur_version, $migversion, '<')) {
				self::log("Migrating to $migversion ... STARTED");
				if (!empty($migrationnode->tables) && !empty($migrationnode->tables->table)) {
					foreach ($migrationnode->tables->table as $tablenode) {
						$tablesql = "$tablenode->sql"; // Convert to string
						// Skip SQL which are destructive
						if (Utils::isDestructiveSql($tablesql)) {
							self::log("SQL: $tablesql ... SKIPPED");
						} else {
							// Supress any SQL query failures
							self::log("SQL: $tablesql ... ", false);
							Utils::executeQuery($tablesql, true);
							self::log('DONE');
						}
					}
				}
				self::log("Migrating to $migversion ... DONE");
			}
		}
	}

	/**
	 * Update Tables of the module
	 * @access private
	 */
	public function updateTables($modulenode)
	{
		$this->importTables($modulenode);
	}

	/**
	 * Update Blocks of the module
	 * @access private
	 */
	public function updateBlocks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->blocks) || empty($modulenode->blocks->block))
			return;

		foreach ($modulenode->blocks->block as $blocknode) {
			$this->listBlocks[] = strval($blocknode->label);
			$blockInstance = Block::getInstance((string) $blocknode->label, $moduleInstance->id);
			if (!$blockInstance) {
				$blockInstance = $this->importBlock($modulenode, $moduleInstance, $blocknode);
			} else {
				$this->updateBlock($modulenode, $moduleInstance, $blocknode, $blockInstance);
			}

			$this->updateFields($blocknode, $blockInstance, $moduleInstance);
		}
		// Deleting removed blocks
		$listBlockBeforeUpdate = Block::getAllForModule($moduleInstance);
		foreach ($listBlockBeforeUpdate as $blockInstance) {
			if (!(in_array($blockInstance->label, $this->listBlocks))) {
				$blockInstance->delete();
			}
		}
	}

	/**
	 * Update Block of the module
	 * @access private
	 */
	public function updateBlock($modulenode, $moduleInstance, $blocknode, $blockInstance)
	{
		$blockInstance->label = strval($blocknode->label);
		if (isset($blocknode->sequence) && isset($blocknode->display_status)) {
			$blockInstance->sequence = strval($blocknode->sequence);
			$blockInstance->showtitle = strval($blocknode->show_title);
			$blockInstance->visible = strval($blocknode->visible);
			$blockInstance->increateview = strval($blocknode->create_view);
			$blockInstance->ineditview = strval($blocknode->edit_view);
			$blockInstance->indetailview = strval($blocknode->detail_view);
			$blockInstance->display_status = strval($blocknode->display_status);
			$blockInstance->iscustom = strval($blocknode->iscustom);
			$blockInstance->islist = strval($blocknode->islist);
		} else {
			$blockInstance->display_status = NULL;
		}
		$blockInstance->save();
		return $blockInstance;
	}

	/**
	 * Update Fields of the module
	 * @access private
	 */
	public function updateFields($blocknode, $blockInstance, $moduleInstance)
	{
		if (empty($blocknode->fields) || empty($blocknode->fields->field))
			return;

		foreach ($blocknode->fields->field as $fieldnode) {
			$this->listFields[] = strval($fieldnode->fieldname);
			$fieldInstance = Field::getInstance((string) $fieldnode->fieldname, $moduleInstance->id);
			if (!$fieldInstance) {
				$fieldInstance = $this->importField($blocknode, $blockInstance, $moduleInstance, $fieldnode);
			} else {
				$this->updateField($blocknode, $blockInstance, $moduleInstance, $fieldnode, $fieldInstance);
			}
			$this->__AddModuleFieldToCache($moduleInstance, $fieldInstance->name, $fieldInstance);
		}
		// Deleting removed fields
		$listFieldBeforeUpdate = Field::getAllForModule($moduleInstance);
		foreach ($listFieldBeforeUpdate as $fieldInstance) {
			if (!(in_array($fieldInstance->name, $this->listFields))) {
				$fieldInstance->delete();
			}
		}
	}

	/**
	 * Update Field of the module
	 * @access private
	 */
	public function updateField($blocknode, $blockInstance, $moduleInstance, $fieldnode, $fieldInstance)
	{

		// strval used because in $fieldnode there is a SimpleXMLElement object
		$fieldInstance->name = strval($fieldnode->fieldname);
		$fieldInstance->label = strval($fieldnode->fieldlabel);
		$fieldInstance->table = strval($fieldnode->tablename);
		$fieldInstance->column = strval($fieldnode->columnname);
		$fieldInstance->uitype = strval($fieldnode->uitype);
		$fieldInstance->generatedtype = strval($fieldnode->generatedtype);
		$fieldInstance->readonly = strval($fieldnode->readonly);
		$fieldInstance->presence = strval($fieldnode->presence);
		$fieldInstance->defaultvalue = strval($fieldnode->defaultvalue);
		$fieldInstance->maximumlength = strval($fieldnode->maximumlength);
		$fieldInstance->sequence = strval($fieldnode->sequence);
		$fieldInstance->quickcreate = strval($fieldnode->quickcreate);
		$fieldInstance->quicksequence = strval($fieldnode->quickcreatesequence);
		$fieldInstance->typeofdata = strval($fieldnode->typeofdata);
		$fieldInstance->displaytype = strval($fieldnode->displaytype);
		$fieldInstance->info_type = strval($fieldnode->info_type);
		$fieldInstance->fieldparams = strval($fieldnode->fieldparams);

		if (!empty($fieldnode->fieldparams))
			$fieldInstance->fieldparams = strval($fieldnode->fieldparams);

		// Check if new parameters are defined
		if (isset($fieldnode->columntype)) {
			$fieldInstance->columntype = strval($fieldnode->columntype);
		} else {
			$fieldInstance->columntype = NULL;
		}

		if (!empty($fieldnode->helpinfo))
			$fieldInstance->setHelpInfo($fieldnode->helpinfo);
		if (!empty($fieldnode->masseditable))
			$fieldInstance->setMassEditable($fieldnode->masseditable);
		if (!empty($fieldnode->summaryfield))
			$fieldInstance->setSummaryField($fieldnode->summaryfield);

		$fieldInstance->block = $blockInstance;
		$fieldInstance->save();

		// Set the field as entity identifier if marked.
		if (!empty($fieldnode->entityidentifier)) {
			if (isset($fieldnode->entityidentifier->fieldname) && !empty($fieldnode->entityidentifier->fieldname)) {
				$moduleInstance->entityfieldname = strval($fieldnode->entityidentifier->fieldname);
			} else {
				$moduleInstance->entityfieldname = $fieldInstance->name;
			}
			$moduleInstance->entityidfield = strval($fieldnode->entityidentifier->entityidfield);
			$moduleInstance->entityidcolumn = strval($fieldnode->entityidentifier->entityidcolumn);
			$moduleInstance->setEntityIdentifier($fieldInstance);
		}

		// Check picklist values associated with field if any.
		if (!empty($fieldnode->picklistvalues) && !empty($fieldnode->picklistvalues->picklistvalue)) {
			$picklistvalues = [];
			foreach ($fieldnode->picklistvalues->picklistvalue as $picklistvaluenode) {
				$picklistvalues[] = $picklistvaluenode;
			}
			$fieldInstance->setPicklistValues($picklistvalues);
		}

		// Check related modules associated with this field
		if (!empty($fieldnode->relatedmodules) && !empty($fieldnode->relatedmodules->relatedmodule)) {
			$relatedmodules = [];

			foreach ($fieldnode->relatedmodules->relatedmodule as $relatedmodulenode) {
				$relatedmodules[] = $relatedmodulenode;
			}
			$fieldInstance->setRelatedModules($relatedmodules);
		}
		return $fieldInstance;
	}

	/**
	 * Import Custom views of the module
	 * @access private
	 */
	public function updateCustomViews($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customviews) || empty($modulenode->customviews->customview))
			return;
		foreach ($modulenode->customviews->customview as $customviewnode) {
			$filterInstance = Filter::getInstance($customviewnode->viewname, $moduleInstance->id);
			if (!$filterInstance) {
				$filterInstance = $this->importCustomView($modulenode, $moduleInstance, $customviewnode);
			} else {
				$this->updateCustomView($modulenode, $moduleInstance, $customviewnode, $filterInstance);
			}
		}
	}

	/**
	 * Update Custom View of the module
	 * @access private
	 */
	public function updateCustomView($modulenode, $moduleInstance, $customviewnode, $filterInstance)
	{

		$filterInstance->delete();
		$this->importCustomView($modulenode, $moduleInstance, $customviewnode);
	}

	/**
	 * Update Sharing Access of the module
	 * @access private
	 */
	public function updateSharingAccess($modulenode, $moduleInstance)
	{
		if (empty($modulenode->sharingaccess))
			return;
	}

	/**
	 * Update Events of the module
	 * @access private
	 */
	public function updateEvents($modulenode, $moduleInstance)
	{
		if (empty($modulenode->eventHandlers) || empty($modulenode->eventHandlers->event)) {
			return;
		}
		$moduleId = \App\Module::getModuleId($moduleInstance->name);
		\App\Db::getInstance()->createCommand()->delete('vtiger_eventhandlers', ['owner_id' => $moduleId])->execute();
		foreach ($modulenode->eventHandlers->event as &$eventNode) {
			\App\EventHandler::registerHandler($eventNode->eventName, $eventNode->className, $eventNode->includeModules, $eventNode->excludeModules, $eventNode->priority, $eventNode->isActive, $moduleId);
		}
	}

	/**
	 * Update actions of the module
	 * @access private
	 */
	public function updateActions($modulenode, $moduleInstance)
	{
		if (empty($modulenode->actions) || empty($modulenode->actions->action))
			return;
		foreach ($modulenode->actions->action as $actionnode) {
			$this->updateAction($modulenode, $moduleInstance, $actionnode);
		}
	}

	/**
	 * Update action of the module
	 * @access private
	 */
	public function updateAction($modulenode, $moduleInstance, $actionnode)
	{
		
	}

	/**
	 * Update related lists of the module
	 * @access private
	 */
	public function updateRelatedLists($modulenode, $moduleInstance)
	{
		$moduleInstance->unsetAllRelatedList();
		if (!empty($modulenode->relatedlists) && !empty($modulenode->relatedlists->relatedlist)) {
			foreach ($modulenode->relatedlists->relatedlist as $relatedlistnode) {
				$this->updateRelatedlist($modulenode, $moduleInstance, $relatedlistnode);
			}
		}
		if (!empty($modulenode->inrelatedlists) && !empty($modulenode->inrelatedlists->inrelatedlist)) {
			foreach ($modulenode->inrelatedlists->inrelatedlist as $inRelatedListNode) {
				$this->updateInRelatedlist($modulenode, $moduleInstance, $inRelatedListNode);
			}
		}
	}

	/**
	 * Import related list of the module.
	 * @access private
	 */
	public function updateRelatedlist($modulenode, $moduleInstance, $relatedlistnode)
	{
		$relModuleInstance = Module::getInstance((string) $relatedlistnode->relatedmodule);
		$label = $relatedlistnode->label;
		$actions = false;
		if (!empty($relatedlistnode->actions) && !empty($relatedlistnode->actions->action)) {
			$actions = [];
			foreach ($relatedlistnode->actions->action as $actionnode) {
				$actions[] = "$actionnode";
			}
		}
		if ($relModuleInstance) {
			$moduleInstance->unsetRelatedList($relModuleInstance, "$label", "$relatedlistnode->function");
			$moduleInstance->setRelatedList($relModuleInstance, "$label", $actions, "$relatedlistnode->function");
		}
		return $relModuleInstance;
	}

	public function updateInRelatedlist($modulenode, $moduleInstance, $inRelatedListNode)
	{
		$inRelModuleInstance = Module::getInstance((string) $inRelatedListNode->inrelatedmodule);
		$label = $inRelatedListNode->label;
		$actions = false;
		if (!empty($inRelatedListNode->actions) && !empty($inRelatedListNode->actions->action)) {
			$actions = [];
			foreach ($inRelatedListNode->actions->action as $actionnode) {
				$actions[] = "$actionnode";
			}
		}
		if ($inRelModuleInstance) {
			$inRelModuleInstance->unsetRelatedList($moduleInstance, "$label", "$inRelatedListNode->function");
			$inRelModuleInstance->setRelatedList($moduleInstance, "$label", $actions, "$inRelatedListNode->function");
		}
		return $inRelModuleInstance;
	}

	public function updateCustomLinks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customlinks) || empty($modulenode->customlinks->customlink))
			return;
		Link::deleteAll($moduleInstance->id);
		$this->importCustomLinks($modulenode, $moduleInstance);
	}

	public function updateCronTasks($modulenode)
	{
		if (empty($modulenode->crons) || empty($modulenode->crons->cron))
			return;
		$cronTasks = Cron::listAllInstancesByModule($modulenode->name);
		foreach ($modulenode->crons->cron as $importCronTask) {
			foreach ($cronTasks as $cronTask) {
				if ($cronTask->getName() == $importCronTask->name && $importCronTask->handler == $cronTask->getHandlerFile()) {
					Cron::deregister($importCronTask->name);
				}
			}
			if (empty($importCronTask->status)) {
				$cronTask->status = Cron::$STATUS_DISABLED;
			} else {
				$cronTask->status = Cron::$STATUS_ENABLED;
			}
			if ((empty($importCronTask->sequence))) {
				$importCronTask->sequence = Cron::nextSequence();
			}
			Cron::register("$importCronTask->name", "$importCronTask->handler", "$importCronTask->frequency", "$modulenode->name", "$importCronTask->status", "$importCronTask->sequence", "$importCronTask->description");
		}
	}
}
