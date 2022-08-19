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
 * Provides API to update module into vtiger CRM.
 */
class PackageUpdate extends PackageImport
{
	public $_migrationinfo = false;
	public $listFields = [];
	public $listBlocks = [];

	/**
	 * Initialize Update.
	 *
	 * @param mixed $moduleInstance
	 * @param mixed $zipfile
	 * @param mixed $overwrite
	 */
	public function initUpdate($moduleInstance, $zipfile, $overwrite)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if (!$moduleInstance || $moduleInstance->name != $module) {
			\App\Log::trace('Module name mismatch!', __METHOD__);

			return false;
		}
		if (null !== $module) {
			$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
			if ($zip->statName("$module.png")) {
				$zip->unzipFile("$module.png", 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/skins/images/$module.png");
			}
			$zip->unzip([
				'updates' => 'cache/updates',
			]);
			// If data is not yet available
			if (empty($this->_modulexml)) {
				$this->__parseManifestFile($zip);
			}
		}
		return $module;
	}

	/**
	 * Update Module from zip file.
	 *
	 * @param Module Instance of the module to update
	 * @param string Zip file name
	 * @param bool True for overwriting existing module
	 * @param mixed $moduleInstance
	 * @param mixed $zipfile
	 * @param mixed $overwrite
	 */
	public function update($moduleInstance, $zipfile, $overwrite = true)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if (null !== $module) {
			$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
			// If data is not yet available
			if (empty($this->_modulexml)) {
				$this->__parseManifestFile($zip);
			}
			$installSequenceArray = $buildModuleArray = [];
			$moduleBundle = (bool) $this->_modulexml->modulebundle;
			if (true === $moduleBundle) {
				$moduleList = (array) $this->_modulexml->modulelist;
				foreach ($moduleList as $moduleInfos) {
					foreach ($moduleInfos as $moduleInfo) {
						$moduleInfo = (array) $moduleInfo;
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
					\App\Log::error('Module name mismatch!', __METHOD__);

					return false;
				}
				$this->initUpdate($moduleInstance, $zipfile, $overwrite);
				// Call module update function
				$this->updateModule($moduleInstance);
			}
		}
	}

	/**
	 * Update Module.
	 *
	 * @param mixed $moduleInstance
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
	 * Parse migration information from manifest.
	 *
	 * @param mixed $modulenode
	 */
	public function parseMigration($modulenode)
	{
		if (empty($this->_migrations)) {
			$this->_migrations = [];
			if (!empty($modulenode->migrations)
				&& !empty($modulenode->migrations->migration)) {
				foreach ($modulenode->migrations->migration as $migrationnode) {
					$migrationattrs = $migrationnode->attributes();
					$migrationversion = $migrationattrs['version'];
					$this->_migrations["$migrationversion"] = $migrationnode;
				}
			}
			// Sort the migration details based on version
			if (\count($this->_migrations) > 1) {
				uksort($this->_migrations, 'version_compare');
			}
		}
	}

	/**
	 * Handle migration of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 */
	public function handleMigration($modulenode, $moduleInstance)
	{
		$this->parseMigration($modulenode);
		$cur_version = $moduleInstance->version;
		foreach ($this->_migrations as $migversion => $migrationnode) {
			// Perform migration only for higher version than current
			if (version_compare($cur_version, $migversion, '<')) {
				\App\Log::trace("Migrating to $migversion ... STARTED", __METHOD__);
				if (!empty($migrationnode->tables) && !empty($migrationnode->tables->table)) {
					foreach ($migrationnode->tables->table as $tablenode) {
						$tablesql = "$tablenode->sql"; // Convert to string
						// Skip SQL which are destructive
						if (Utils::isDestructiveSql($tablesql)) {
							\App\Log::trace("SQL: $tablesql ... SKIPPED", __METHOD__);
						} else {
							// Supress any SQL query failures
							\App\Log::trace("SQL: $tablesql ... ", __METHOD__);
							\App\Db::getInstance()->createCommand($tablesql)->execute();
							\App\Log::trace('DONE', __METHOD__);
						}
					}
				}
				\App\Log::trace("Migrating to $migversion ... DONE", __METHOD__);
			}
		}
	}

	/**
	 * Update Tables of the module.
	 *
	 * @param mixed $modulenode
	 */
	public function updateTables($modulenode)
	{
		$this->importTables($modulenode);
	}

	/**
	 * Update Blocks of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 */
	public function updateBlocks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->blocks) || empty($modulenode->blocks->block)) {
			return;
		}

		foreach ($modulenode->blocks->block as $blocknode) {
			$this->listBlocks[] = (string) ($blocknode->blocklabel);
			$blockInstance = Block::getInstance((string) $blocknode->blocklabel, $moduleInstance->id);
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
			if (!(\in_array($blockInstance->label, $this->listBlocks))) {
				$blockInstance->delete();
			}
		}
		// Deleting removed fields
		if ($this->listFields) {
			$listFieldBeforeUpdate = Field::getAllForModule($moduleInstance);
			foreach ($listFieldBeforeUpdate as $fieldInstance) {
				if (!(\in_array($fieldInstance->name, $this->listFields))) {
					$fieldInstance->delete();
				}
			}
		}
	}

	/**
	 * Update Block of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $blocknode
	 * @param mixed $blockInstance
	 */
	public function updateBlock($modulenode, $moduleInstance, $blocknode, $blockInstance)
	{
		$blockInstance->label = (string) ($blocknode->blocklabel);
		if (isset($blocknode->sequence, $blocknode->display_status)) {
			$blockInstance->sequence = (string) ($blocknode->sequence);
			$blockInstance->showtitle = (string) ($blocknode->show_title);
			$blockInstance->visible = (string) ($blocknode->visible);
			$blockInstance->increateview = (string) ($blocknode->create_view);
			$blockInstance->ineditview = (string) ($blocknode->edit_view);
			$blockInstance->indetailview = (string) ($blocknode->detail_view);
			$blockInstance->display_status = (string) ($blocknode->display_status);
			$blockInstance->iscustom = (string) ($blocknode->iscustom);
			$blockInstance->islist = (string) ($blocknode->islist);
		} else {
			$blockInstance->display_status = null;
		}
		$blockInstance->save();

		return $blockInstance;
	}

	/**
	 * Update Fields of the module.
	 *
	 * @param mixed $blocknode
	 * @param mixed $blockInstance
	 * @param mixed $moduleInstance
	 */
	public function updateFields($blocknode, $blockInstance, $moduleInstance)
	{
		if (empty($blocknode->fields) || empty($blocknode->fields->field)) {
			return;
		}

		foreach ($blocknode->fields->field as $fieldnode) {
			$this->listFields[] = (string) ($fieldnode->fieldname);
			$fieldInstance = Field::getInstance((string) $fieldnode->fieldname, $moduleInstance);
			if (!$fieldInstance) {
				$fieldInstance = $this->importField($blocknode, $blockInstance, $moduleInstance, $fieldnode);
			} else {
				$this->updateField($blocknode, $blockInstance, $moduleInstance, $fieldnode, $fieldInstance);
			}
			$this->__AddModuleFieldToCache($moduleInstance, $fieldInstance->name, $fieldInstance);
		}
	}

	/**
	 * Update Field of the module.
	 *
	 * @param mixed $blocknode
	 * @param mixed $blockInstance
	 * @param mixed $moduleInstance
	 * @param mixed $fieldnode
	 * @param mixed $fieldInstance
	 */
	public function updateField($blocknode, $blockInstance, $moduleInstance, $fieldnode, $fieldInstance)
	{
		// strval used because in $fieldnode there is a SimpleXMLElement object
		$fieldInstance->name = (string) ($fieldnode->fieldname);
		$fieldInstance->label = (string) ($fieldnode->fieldlabel);
		$fieldInstance->table = (string) ($fieldnode->tablename);
		$fieldInstance->column = (string) ($fieldnode->columnname);
		$fieldInstance->uitype = (string) ($fieldnode->uitype);
		$fieldInstance->generatedtype = (string) ($fieldnode->generatedtype);
		$fieldInstance->readonly = (string) ($fieldnode->readonly);
		$fieldInstance->presence = (string) ($fieldnode->presence);
		$fieldInstance->defaultvalue = (string) ($fieldnode->defaultvalue);
		$fieldInstance->maximumlength = (string) ($fieldnode->maximumlength);
		$fieldInstance->sequence = (string) ($fieldnode->sequence);
		$fieldInstance->quickcreate = (string) ($fieldnode->quickcreate);
		$fieldInstance->quicksequence = (string) ($fieldnode->quickcreatesequence);
		$fieldInstance->typeofdata = (string) ($fieldnode->typeofdata);
		$fieldInstance->displaytype = (string) ($fieldnode->displaytype);
		$fieldInstance->info_type = (string) ($fieldnode->info_type);
		$fieldInstance->fieldparams = (string) ($fieldnode->fieldparams);

		if (!empty($fieldnode->fieldparams)) {
			$fieldInstance->fieldparams = (string) ($fieldnode->fieldparams);
		}

		// Check if new parameters are defined
		if (isset($fieldnode->columntype)) {
			$fieldInstance->columntype = (string) ($fieldnode->columntype);
		} else {
			$fieldInstance->columntype = null;
		}

		if (!empty($fieldnode->helpinfo)) {
			$fieldInstance->setHelpInfo($fieldnode->helpinfo);
		}
		if (!empty($fieldnode->masseditable)) {
			$fieldInstance->setMassEditable($fieldnode->masseditable);
		}
		if (!empty($fieldnode->summaryfield)) {
			$fieldInstance->setSummaryField($fieldnode->summaryfield);
		}

		$fieldInstance->block = $blockInstance;
		$fieldInstance->save();

		// Set the field as entity identifier if marked.
		if (!empty($fieldnode->entityidentifier)) {
			if (isset($fieldnode->entityidentifier->fieldname) && !empty($fieldnode->entityidentifier->fieldname)) {
				$moduleInstance->entityfieldname = (string) ($fieldnode->entityidentifier->fieldname);
			} else {
				$moduleInstance->entityfieldname = $fieldInstance->name;
			}
			$moduleInstance->entityidfield = (string) ($fieldnode->entityidentifier->entityidfield);
			$moduleInstance->entityidcolumn = (string) ($fieldnode->entityidentifier->entityidcolumn);
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
	 * Import Custom views of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 */
	public function updateCustomViews($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customviews) || empty($modulenode->customviews->customview)) {
			return;
		}
		foreach ($modulenode->customviews->customview as $customviewnode) {
			$filterInstance = Filter::getInstance($customviewnode->viewname, $moduleInstance->id);
			if (!$filterInstance) {
				$this->importCustomView($modulenode, $moduleInstance, $customviewnode);
			} else {
				$this->updateCustomView($modulenode, $moduleInstance, $customviewnode, $filterInstance);
			}
		}
	}

	/**
	 * Update Custom View of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $customviewnode
	 * @param mixed $filterInstance
	 */
	public function updateCustomView($modulenode, $moduleInstance, $customviewnode, $filterInstance)
	{
		$filterInstance->delete();
		$this->importCustomView($modulenode, $moduleInstance, $customviewnode);
	}

	/**
	 * Update Sharing Access of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 */
	public function updateSharingAccess($modulenode, $moduleInstance)
	{
		if (empty($modulenode->sharingaccess)) {
			return;
		}
	}

	/**
	 * Update Events of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 * Update actions of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 */
	public function updateActions($modulenode, $moduleInstance)
	{
		if (empty($modulenode->actions) || empty($modulenode->actions->action)) {
			return;
		}
		foreach ($modulenode->actions->action as $actionnode) {
			$this->updateAction($modulenode, $moduleInstance, $actionnode);
		}
	}

	/**
	 * Update action of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $actionnode
	 */
	public function updateAction($modulenode, $moduleInstance, $actionnode)
	{
	}

	/**
	 * Update related lists of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $relatedlistnode
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
		$fields = [];
		if (!empty($relatedlistnode->fields)) {
			foreach ($relatedlistnode->fields->field as $fieldNode) {
				$fields[] = "$fieldNode";
			}
		}
		if ($relModuleInstance) {
			$moduleInstance->unsetRelatedList($relModuleInstance, "$label", "$relatedlistnode->function");
			$moduleInstance->setRelatedList($relModuleInstance, "$label", $actions, "$relatedlistnode->function", "$relatedlistnode->field_name", $fields);
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
		$fields = [];
		if (!empty($inRelatedListNode->fields)) {
			foreach ($inRelatedListNode->fields->field as $fieldNode) {
				$fields[] = "$fieldNode";
			}
		}
		if ($inRelModuleInstance) {
			$inRelModuleInstance->unsetRelatedList($moduleInstance, "$label", "$inRelatedListNode->function", $inRelatedListNode->field_name);
			$inRelModuleInstance->setRelatedList($moduleInstance, "$label", $actions, "$inRelatedListNode->function", "$inRelatedListNode->field_name", $fields);
		}
		return $inRelModuleInstance;
	}

	public function updateCustomLinks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customlinks) || empty($modulenode->customlinks->customlink)) {
			return;
		}
		Link::deleteAll($moduleInstance->id);
		$this->importCustomLinks($modulenode, $moduleInstance);
	}

	public function updateCronTasks($modulenode)
	{
		if (empty($modulenode->crons) || empty($modulenode->crons->cron)) {
			return;
		}
		$cronTasks = Cron::listAllInstancesByModule($modulenode->name);
		foreach ($modulenode->crons->cron as $importCronTask) {
			foreach ($cronTasks as $cronTask) {
				if ($cronTask->getName() == $importCronTask->name && $importCronTask->handler == $cronTask->getHandlerClass()) {
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
