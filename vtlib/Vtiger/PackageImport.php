<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides API to import module into vtiger CRM.
 */
class PackageImport extends PackageExport
{
	/**
	 * Module Meta XML File (Parsed).
	 */
	public $_modulexml;

	/**
	 * Module Fields mapped by [modulename][fieldname] which
	 * will be used to create customviews.
	 */
	public $_modulefields_cache = [];

	/**
	 * License of the package.
	 */
	public $_licensetext = false;
	public $_errorText = '';
	public $packageType = '';
	public $parameters = [];

	/**
	 * Parse the manifest file.
	 *
	 * @param \App\Zip $zip
	 */
	public function __parseManifestFile(\App\Zip $zip)
	{
		$this->_modulexml = simplexml_load_string($zip->getFromName('manifest.xml'));
	}

	/**
	 * Get type of package (as specified in manifest).
	 */
	public function type()
	{
		if (!empty($this->_modulexml) && !empty($this->_modulexml->type)) {
			return $this->_modulexml->type;
		}

		return false;
	}

	/**
	 * Get type of package (as specified in manifest).
	 */
	public function getTypeName()
	{
		if (!empty($this->_modulexml) && !empty($this->_modulexml->type)) {
			$packageType = strtolower($this->_modulexml->type);
			switch ($packageType) {
				case 'extension': $packageType = 'LBL_EXTENSION_MODULE';
					break;
				case 'entity': $packageType = 'LBL_BASE_MODULE';
					break;
				case 'inventory': $packageType = 'LBL_INVENTORY_MODULE';
					break;
				case 'language': $packageType = 'LBL_LANGUAGE_MODULE';
					break;
			}

			return $packageType;
		}

		return '';
	}

	/**
	 * XPath evaluation on the root module node.
	 *
	 * @param string Path expression
	 */
	public function xpath($path)
	{
		return $this->_modulexml->xpath($path);
	}

	/**
	 * Are we trying to import language package?
	 */
	public function isLanguageType($zipfile = null)
	{
		if (!empty($zipfile)) {
			if (!$this->checkZip($zipfile)) {
				return false;
			}
		}
		$packagetype = $this->type();
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ($lcasetype === 'language') {
				return true;
			}
		}
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ($lcasetype === 'layout') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Are we trying to import extension package?
	 */
	public function isExtensionType($zipfile = null)
	{
		if (!empty($zipfile)) {
			if (!$this->checkZip($zipfile)) {
				return false;
			}
		}
		$packagetype = $this->type();
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ($lcasetype === 'extension') {
				return true;
			}
		}
		return false;
	}

	public function isUpdateType($zipfile = null)
	{
		if (!empty($zipfile)) {
			if (!$this->checkZip($zipfile)) {
				return false;
			}
		}
		$packagetype = $this->type();

		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ($lcasetype === 'update') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Are we trying to import language package?
	 */
	public function isLayoutType($zipfile = null)
	{
		if (!empty($zipfile)) {
			if (!$this->checkZip($zipfile)) {
				return false;
			}
		}
		$packagetype = $this->type();

		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ($lcasetype === 'layout') {
				return true;
			}
		}

		return false;
	}

	/**
	 * checks whether a package is module bundle or not.
	 *
	 * @param string $zipfile - path to the zip file
	 *
	 * @return bool - true if given zipfile is a module bundle and false otherwise
	 */
	public function isModuleBundle($zipfile = null)
	{
		// If data is not yet available
		if (!empty($zipfile)) {
			if (!$this->checkZip($zipfile)) {
				return false;
			}
		}

		return (bool) $this->_modulexml->modulebundle;
	}

	/**
	 * @return array module list available in the module bundle
	 */
	public function getAvailableModuleInfoFromModuleBundle()
	{
		$list = (array) $this->_modulexml->modulelist;

		return (array) $list['dependent_module'];
	}

	/**
	 * Get the license of this package
	 * NOTE: checkzip should have been called earlier.
	 */
	public function getLicense()
	{
		return $this->_licensetext;
	}

	public function getParameters()
	{
		$parameters = [];
		if (empty($this->_modulexml->parameters)) {
			return $parameters;
		}
		foreach ($this->_modulexml->parameters->parameter as $parameter) {
			$parameters[] = $parameter;
		}

		return $parameters;
	}

	public function initParameters(\App\Request $request)
	{
		$data = [];
		foreach ($request->getAll() as $name => $value) {
			if (strpos($name, 'param_') !== false) {
				$name = str_replace('param_', '', $name);
				$data[$name] = $value;
			}
		}
		$this->parameters = $data;
	}

	/**
	 * Check if zipfile is a valid package.
	 */
	public function checkZip($zipfile)
	{
		$manifestxml_found = false;
		$languagefile_found = false;
		$layoutfile_found = false;
		$updatefile_found = false;
		$extensionfile_found = false;
		$moduleVersionFound = false;
		$modulename = null;
		$language_modulename = null;

		$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
		$this->__parseManifestFile($zip);
		for ($i = 0; $i < $zip->numFiles; ++$i) {
			$fileName = $zip->getNameIndex($i);
			$matches = [];
			if ($fileName === 'manifest.xml') {
				$manifestxml_found = true;
				$modulename = (string) $this->_modulexml->name;
				$isModuleBundle = (string) $this->_modulexml->modulebundle;
				if ($isModuleBundle === 'true' && (!empty($this->_modulexml)) &&
					(!empty($this->_modulexml->dependencies)) &&
					(!empty($this->_modulexml->dependencies->vtiger_version))) {
					$languagefile_found = true;
					break;
				}
				// Do we need to check the zip further?
				if ($this->isLanguageType()) {
					$languagefile_found = true; // No need to search for module language file.
					break;
				} elseif ($this->isLayoutType()) {
					$layoutfile_found = true; // No need to search for module language file.
					break;
				} elseif ($this->isExtensionType()) {
					$extensionfile_found = true; // No need to search for module language file.
					break;
				} elseif ($this->isUpdateType()) {
					$updatefile_found = true; // No need to search for module language file.
					break;
				} else {
					continue;
				}
			}
			// Language file present in en_us folder
			$pattern = '/languages[\/\\\]' . \AppConfig::main('default_language') . '[\/\\\]([^\/]+)\.json/';
			preg_match($pattern, $fileName, $matches);
			if (count($matches)) {
				$language_modulename = $matches[1];
			}
			// or Language file may be present in en_us/Settings folder
			$settingsPattern = '/languages[\/\\\]' . \AppConfig::main('default_language') . '[\/\\\]Settings[\/\\\]([^\/]+)\.json/';
			preg_match($settingsPattern, $fileName, $matches);
			if (count($matches)) {
				$language_modulename = $matches[1];
			}
		}
		// Verify module language file.
		if (!empty($language_modulename) && $language_modulename == $modulename) {
			$languagefile_found = true;
		} elseif (!$updatefile_found && !$layoutfile_found && !$languagefile_found) {
			$_errorText = \App\Language::translate('LBL_ERROR_NO_DEFAULT_LANGUAGE', 'Settings:ModuleManager');
			$_errorText = str_replace('__DEFAULTLANGUAGE__', \AppConfig::main('default_language'), $_errorText);
			$this->_errorText = $_errorText;
		}
		if (!empty($this->_modulexml) &&
			!empty($this->_modulexml->dependencies) &&
			!empty($this->_modulexml->dependencies->vtiger_version)) {
			$moduleVersion = (string) $this->_modulexml->dependencies->vtiger_version;
			if (\App\Version::check($moduleVersion) === true) {
				$moduleVersionFound = true;
			} else {
				$_errorText = \App\Language::translate('LBL_ERROR_VERSION', 'Settings:ModuleManager');
				$_errorText = str_replace('__MODULEVERSION__', $moduleVersion, $_errorText);
				$_errorText = str_replace('__CRMVERSION__', \App\Version::get(), $_errorText);
				$this->_errorText = $_errorText;
			}
		}
		$validzip = false;
		if ($manifestxml_found && $languagefile_found && $moduleVersionFound) {
			$validzip = true;
		}
		if ($manifestxml_found && $layoutfile_found && $moduleVersionFound) {
			$validzip = true;
		}
		if ($manifestxml_found && $languagefile_found && $extensionfile_found && $moduleVersionFound) {
			$validzip = true;
		}
		if ($manifestxml_found && $updatefile_found && $moduleVersionFound) {
			$validzip = true;
		}
		if ($this->isLanguageType() && $manifestxml_found && strpos($this->_modulexml->prefix, '/') !== false) {
			$validzip = false;
			$this->_errorText = \App\Language::translate('LBL_ERROR_NO_VALID_PREFIX', 'Settings:ModuleManager');
		}
		if ($validzip) {
			if (!empty($this->_modulexml->license)) {
				if (!empty($this->_modulexml->license->inline)) {
					$this->_licensetext = (string) $this->_modulexml->license->inline;
				} elseif (!empty($this->_modulexml->license->file)) {
					$licensefile = (string) $this->_modulexml->license->file;
					if ($licenseContent = $zip->getFromName($licensefile)) {
						$this->_licensetext = $licenseContent;
					} else {
						$this->_licensetext = "Missing $licensefile!";
					}
				}
			}
		}
		if ($zip) {
			$zip->close();
		}
		return $validzip;
	}

	/**
	 * Get module name packaged in the zip file.
	 */
	public function getModuleNameFromZip($zipfile)
	{
		if (!$this->checkZip($zipfile)) {
			return null;
		}
		return (string) $this->_modulexml->name;
	}

	/**
	 * returns the name of the module.
	 *
	 * @return string - name of the module as given in manifest file
	 */
	public function getModuleName()
	{
		return (string) $this->_modulexml->name;
	}

	/**
	 * Cache the field instance for re-use.
	 */
	public function __AddModuleFieldToCache($moduleInstance, $fieldname, $fieldInstance)
	{
		$this->_modulefields_cache["$moduleInstance->name"]["$fieldname"] = $fieldInstance;
	}

	/**
	 * Get field instance from cache.
	 */
	public function __GetModuleFieldFromCache($moduleInstance, $fieldname)
	{
		return $this->_modulefields_cache["$moduleInstance->name"]["$fieldname"];
	}

	/**
	 * Initialize Import.
	 */
	public function initImport($zipfile, $overwrite = true)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if ($module !== null) {
			$defaultLayout = \Vtiger_Viewer::getDefaultLayoutName();
			$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
			if ($zip->statName("$module.png")) {
				$zip->unzipFile("$module.png", "layouts/$defaultLayout/images/$module.png");
			}
			$zip->unzip([
				// Templates folder
				'templates/resources' => "public_html/layouts/$defaultLayout/modules/$module/resources",
				'templates' => "layouts/$defaultLayout/modules/$module",
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
				'settings/templates' => "layouts/$defaultLayout/modules/Settings/$module",
				//module images
				'images' => "layouts/$defaultLayout/images/$module",
				'updates' => 'cache/updates',
				'layouts' => 'layouts',
				'languages' => 'languages',
			]);
		}

		return $module;
	}

	public function getTemporaryFilePath($filepath = false)
	{
		return 'cache/' . $filepath;
	}

	/**
	 * Get dependent version.
	 */
	public function getDependentVtigerVersion()
	{
		return $this->_modulexml->dependencies->vtiger_version;
	}

	/**
	 * Get dependent Maximum version.
	 */
	public function getDependentMaxVtigerVersion()
	{
		return $this->_modulexml->dependencies->vtiger_max_version;
	}

	/**
	 * Get package version.
	 */
	public function getVersion()
	{
		return $this->_modulexml->version;
	}

	/**
	 * Get package author name.
	 */
	public function getAuthorName()
	{
		return $this->_modulexml->authorname;
	}

	/**
	 * Get package author phone number.
	 */
	public function getAuthorPhone()
	{
		return $this->_modulexml->authorphone;
	}

	/**
	 * Get package author phone email.
	 */
	public function getAuthorEmail()
	{
		return $this->_modulexml->authoremail;
	}

	/**
	 * Get package author phone email.
	 */
	public function getDescription()
	{
		return $this->_modulexml->description;
	}

	public function getUpdateInfo()
	{
		return [
			'from' => $this->_modulexml->from_version,
			'to' => $this->_modulexml->to_version,
		];
	}

	/**
	 * Import Module from zip file.
	 *
	 * @param string Zip file name
	 * @param bool True for overwriting existing module
	 */
	public function import($zipfile, $overwrite = false)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if ($module !== null) {
			$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
			// If data is not yet available
			if (empty($this->_modulexml)) {
				$this->__parseManifestFile($zip);
			}
			$buildModuleArray = [];
			$installSequenceArray = [];
			$moduleBundle = (bool) $this->_modulexml->modulebundle;
			if ($moduleBundle === true) {
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
							$this->import($this->getTemporaryFilePath($moduleInfo['filepath']), $overwrite);
						}
					}
				}
			} else {
				$this->initImport($zipfile, $overwrite);
				// Call module import function
				$this->importModule();
			}
		}
	}

	/**
	 * Import Module.
	 */
	public function importModule()
	{
		$tabname = $this->_modulexml->name;
		$tabLabel = $this->_modulexml->label;
		$tabVersion = $this->_modulexml->version;

		$isextension = false;
		$moduleType = 0;
		if (!empty($this->_modulexml->type)) {
			$this->packageType = strtolower($this->_modulexml->type);
			if ($this->packageType == 'extension' || $this->packageType == 'language') {
				$isextension = true;
			}
			if ($this->packageType == 'inventory') {
				$moduleType = 1;
			}
		}

		$vtigerMinVersion = $this->_modulexml->dependencies->vtiger_version;
		$vtigerMaxVersion = $this->_modulexml->dependencies->vtiger_max_version;

		$moduleInstance = new Module();
		$moduleInstance->name = $tabname;
		$moduleInstance->label = $tabLabel;
		$moduleInstance->isentitytype = ($isextension !== true);
		$moduleInstance->version = (!$tabVersion) ? 0 : $tabVersion;
		$moduleInstance->minversion = (!$vtigerMinVersion) ? false : $vtigerMinVersion;
		$moduleInstance->maxversion = (!$vtigerMaxVersion) ? false : $vtigerMaxVersion;
		$moduleInstance->type = $moduleType;

		if ($this->packageType != 'update') {
			$moduleInstance->save();
			$moduleInstance->initWebservice();
			$this->moduleInstance = $moduleInstance;

			$this->importTables($this->_modulexml);
			$this->importBlocks($this->_modulexml, $moduleInstance);
			$this->importInventory();
			$this->importCustomViews($this->_modulexml, $moduleInstance);
			$this->importSharingAccess($this->_modulexml, $moduleInstance);
			$this->importEvents($this->_modulexml, $moduleInstance);
			$this->importActions($this->_modulexml, $moduleInstance);
			$this->importRelatedLists($this->_modulexml, $moduleInstance);
			$this->importCustomLinks($this->_modulexml, $moduleInstance);
			$this->importCronTasks($this->_modulexml);
			Module::fireEvent($moduleInstance->name, Module::EVENT_MODULE_POSTINSTALL);
		} else {
			$this->importUpdate($this->_modulexml);
		}
	}

	/**
	 * Import Tables of the module.
	 */
	public function importTables($modulenode)
	{
		if (empty($modulenode->tables) || empty($modulenode->tables->table)) {
			return;
		}
		$adb = \PearDatabase::getInstance();
		$adb->query('SET FOREIGN_KEY_CHECKS = 0;');

		// Import the table via queries
		foreach ($modulenode->tables->table as $tablenode) {
			$tableName = $tablenode->name;
			$sql = (string) $tablenode->sql; // Convert to string format
			// Avoid executing SQL that will DELETE or DROP table data
			if (Utils::isCreateSql($sql)) {
				if (!Utils::checkTable($tableName)) {
					\App\Log::trace("SQL: $sql ... ", __METHOD__);
					Utils::executeQuery($sql);
					\App\Log::trace('DONE', __METHOD__);
				}
			} else {
				if (Utils::isDestructiveSql($sql)) {
					\App\Log::trace("SQL: $sql ... SKIPPED", __METHOD__);
				} else {
					\App\Log::trace("SQL: $sql ... ", __METHOD__);
					Utils::executeQuery($sql);
					\App\Log::trace('DONE', __METHOD__);
				}
			}
		}
		$adb->query('SET FOREIGN_KEY_CHECKS = 1;');
	}

	/**
	 * Import Blocks of the module.
	 */
	public function importBlocks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->blocks) || empty($modulenode->blocks->block)) {
			return;
		}
		foreach ($modulenode->blocks->block as $blocknode) {
			$blockInstance = $this->importBlock($modulenode, $moduleInstance, $blocknode);
			$this->importFields($blocknode, $blockInstance, $moduleInstance);
		}
	}

	/**
	 * Import Block of the module.
	 */
	public function importBlock($modulenode, $moduleInstance, $blocknode)
	{
		$blocklabel = $blocknode->label;

		$blockInstance = new Block();
		$blockInstance->label = $blocklabel;
		if (isset($blocknode->sequence, $blocknode->display_status)) {
			$blockInstance->sequence = (string) ($blocknode->sequence);
			if ($blockInstance->sequence = '') {
				$blockInstance->sequence = null;
			}
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
		$moduleInstance->addBlock($blockInstance);

		return $blockInstance;
	}

	/**
	 * Import Fields of the module.
	 */
	public function importFields($blocknode, $blockInstance, $moduleInstance)
	{
		if (empty($blocknode->fields) || empty($blocknode->fields->field)) {
			return;
		}

		foreach ($blocknode->fields->field as $fieldnode) {
			$this->importField($blocknode, $blockInstance, $moduleInstance, $fieldnode);
		}
	}

	/**
	 * Import Field of the module.
	 */
	public function importField($blocknode, $blockInstance, $moduleInstance, $fieldnode)
	{
		$fieldInstance = new Field();
		$fieldInstance->name = (string) $fieldnode->fieldname;
		$fieldInstance->label = (string) $fieldnode->fieldlabel;
		$fieldInstance->table = (string) $fieldnode->tablename;
		$fieldInstance->column = (string) $fieldnode->columnname;
		$fieldInstance->uitype = (int) $fieldnode->uitype;
		$fieldInstance->generatedtype = (int) $fieldnode->generatedtype;
		$fieldInstance->readonly = (int) $fieldnode->readonly;
		$fieldInstance->presence = (int) $fieldnode->presence;
		$fieldInstance->defaultvalue = (string) $fieldnode->defaultvalue;
		$fieldInstance->maximumlength = (int) $fieldnode->maximumlength;
		$fieldInstance->sequence = (int) $fieldnode->sequence;
		$fieldInstance->quickcreate = (int) $fieldnode->quickcreate;
		$fieldInstance->quicksequence = (int) $fieldnode->quickcreatesequence;
		$fieldInstance->typeofdata = (string) $fieldnode->typeofdata;
		$fieldInstance->displaytype = (int) $fieldnode->displaytype;
		$fieldInstance->info_type = (string) $fieldnode->info_type;

		if (!empty($fieldnode->fieldparams)) {
			$fieldInstance->fieldparams = (string) $fieldnode->fieldparams;
		}

		if (!empty($fieldnode->helpinfo)) {
			$fieldInstance->helpinfo = (string) $fieldnode->helpinfo;
		}

		if (isset($fieldnode->masseditable)) {
			$fieldInstance->masseditable = (int) $fieldnode->masseditable;
		}

		if (isset($fieldnode->columntype) && !empty($fieldnode->columntype)) {
			$fieldInstance->columntype = (string) ($fieldnode->columntype);
		}

		if (!empty($fieldnode->tree_template)) {
			$templateid = $fieldInstance->setTreeTemplate($fieldnode->tree_template, $moduleInstance);
			$fieldInstance->fieldparams = $templateid;
		}

		$blockInstance->addField($fieldInstance);

		// Set the field as entity identifier if marked.
		if (!empty($fieldnode->entityidentifier)) {
			$moduleInstance->entityidfield = $fieldnode->entityidentifier->entityidfield;
			$moduleInstance->entityidcolumn = $fieldnode->entityidentifier->entityidcolumn;
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

		// Set summary field if marked in xml
		if (!empty($fieldnode->summaryfield)) {
			$fieldInstance->setSummaryField($fieldnode->summaryfield);
		}
		$this->__AddModuleFieldToCache($moduleInstance, $fieldnode->fieldname, $fieldInstance);

		return $fieldInstance;
	}

	/**
	 * Import Custom views of the module.
	 */
	public function importCustomViews($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customviews) || empty($modulenode->customviews->customview)) {
			return;
		}
		foreach ($modulenode->customviews->customview as $customviewnode) {
			$this->importCustomView($modulenode, $moduleInstance, $customviewnode);
		}
	}

	/**
	 * Import Custom View of the module.
	 */
	public function importCustomView($modulenode, $moduleInstance, $customviewnode)
	{
		$filterInstance = new Filter();
		$filterInstance->name = $customviewnode->viewname;
		$filterInstance->isdefault = $customviewnode->setdefault;
		$filterInstance->inmetrics = $customviewnode->setmetrics;
		$filterInstance->presence = $customviewnode->presence;
		$filterInstance->privileges = $customviewnode->privileges;
		$filterInstance->featured = $customviewnode->featured;
		$filterInstance->sequence = $customviewnode->sequence;
		$filterInstance->description = $customviewnode->description;
		$filterInstance->sort = $customviewnode->sort;

		$moduleInstance->addFilter($filterInstance);

		foreach ($customviewnode->fields->field as $fieldnode) {
			$fieldInstance = $this->__GetModuleFieldFromCache($moduleInstance, $fieldnode->fieldname);
			$filterInstance->addField($fieldInstance, $fieldnode->columnindex);

			if (!empty($fieldnode->rules->rule)) {
				foreach ($fieldnode->rules->rule as $rulenode) {
					$filterInstance->addRule($fieldInstance, $rulenode->comparator, $rulenode->value, $rulenode->columnindex);
				}
			}
		}
	}

	/**
	 * Import Sharing Access of the module.
	 */
	public function importSharingAccess($modulenode, $moduleInstance)
	{
		if (empty($modulenode->sharingaccess)) {
			return;
		}

		if (!empty($modulenode->sharingaccess->default)) {
			foreach ($modulenode->sharingaccess->default as $defaultnode) {
				$moduleInstance->setDefaultSharing($defaultnode);
			}
		}
	}

	/**
	 * Import Events of the module.
	 */
	public function importEvents($modulenode, $moduleInstance)
	{
		if (empty($modulenode->eventHandlers) || empty($modulenode->eventHandlers->event)) {
			return;
		}
		$moduleId = \App\Module::getModuleId($moduleInstance->name);
		foreach ($modulenode->eventHandlers->event as &$eventNode) {
			\App\EventHandler::registerHandler($eventNode->eventName, $eventNode->className, $eventNode->includeModules, $eventNode->excludeModules, $eventNode->priority, $eventNode->isActive, $moduleId);
		}
	}

	/**
	 * Import actions of the module.
	 */
	public function importActions($modulenode, $moduleInstance)
	{
		if (empty($modulenode->actions) || empty($modulenode->actions->action)) {
			return;
		}
		foreach ($modulenode->actions->action as $actionnode) {
			$this->importAction($modulenode, $moduleInstance, $actionnode);
		}
	}

	/**
	 * Import action of the module.
	 */
	public function importAction($modulenode, $moduleInstance, $actionnode)
	{
		$actionstatus = (string) $actionnode->status;
		if ($actionstatus === 'enabled') {
			$moduleInstance->enableTools((string) $actionnode->name);
		} else {
			$moduleInstance->disableTools((string) $actionnode->name);
		}
	}

	/**
	 * Import related lists of the module.
	 */
	public function importRelatedLists($modulenode, $moduleInstance)
	{
		if (!empty($modulenode->relatedlists) && !empty($modulenode->relatedlists->relatedlist)) {
			foreach ($modulenode->relatedlists->relatedlist as $relatedlistnode) {
				$this->importRelatedlist($modulenode, $moduleInstance, $relatedlistnode);
			}
		}
		if (!empty($modulenode->inrelatedlists) && !empty($modulenode->inrelatedlists->inrelatedlist)) {
			foreach ($modulenode->inrelatedlists->inrelatedlist as $inRelatedListNode) {
				$this->importInRelatedlist($modulenode, $moduleInstance, $inRelatedListNode);
			}
		}
	}

	/**
	 * Import related list of the module.
	 */
	public function importRelatedlist($modulenode, $moduleInstance, $relatedlistnode)
	{
		$relModuleInstance = Module::getInstance($relatedlistnode->relatedmodule);
		$label = $relatedlistnode->label;
		$actions = false;
		if (!empty($relatedlistnode->actions) && !empty($relatedlistnode->actions->action)) {
			$actions = [];
			foreach ($relatedlistnode->actions->action as $actionnode) {
				$actions[] = "$actionnode";
			}
		}
		if ($relModuleInstance) {
			$moduleInstance->setRelatedList($relModuleInstance, "$label", $actions, "$relatedlistnode->function");
		}

		return $relModuleInstance;
	}

	public function importInRelatedlist($modulenode, $moduleInstance, $inRelatedListNode)
	{
		$inRelModuleInstance = Module::getInstance($inRelatedListNode->inrelatedmodule);
		$label = $inRelatedListNode->label;
		$actions = false;
		if (!empty($inRelatedListNode->actions) && !empty($inRelatedListNode->actions->action)) {
			$actions = [];
			foreach ($inRelatedListNode->actions->action as $actionnode) {
				$actions[] = "$actionnode";
			}
		}
		if ($inRelModuleInstance) {
			$inRelModuleInstance->setRelatedList($moduleInstance, "$label", $actions, "$inRelatedListNode->function");
		}

		return $inRelModuleInstance;
	}

	/**
	 * Import custom links of the module.
	 */
	public function importCustomLinks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customlinks) || empty($modulenode->customlinks->customlink)) {
			return;
		}

		foreach ($modulenode->customlinks->customlink as $customlinknode) {
			$handlerInfo = null;
			if (!empty($customlinknode->handler_path)) {
				$handlerInfo = [];
				$handlerInfo = ['path' => "$customlinknode->handler_path",
					'class' => "$customlinknode->handler_class",
					'method' => "$customlinknode->handler", ];
			}
			$moduleInstance->addLink(
				"$customlinknode->linktype", "$customlinknode->linklabel", "$customlinknode->linkurl", "$customlinknode->linkicon", "$customlinknode->sequence", $handlerInfo
			);
		}
	}

	/**
	 * Import cron jobs of the module.
	 */
	public function importCronTasks($modulenode)
	{
		if (empty($modulenode->crons) || empty($modulenode->crons->cron)) {
			return;
		}
		foreach ($modulenode->crons->cron as $cronTask) {
			if (empty($cronTask->status)) {
				$cronTask->status = Cron::$STATUS_DISABLED;
			} else {
				$cronTask->status = Cron::$STATUS_ENABLED;
			}
			if ((empty($cronTask->sequence))) {
				$cronTask->sequence = Cron::nextSequence();
			}
			Cron::register("$cronTask->name", "$cronTask->handler", "$cronTask->frequency", "$modulenode->name", "$cronTask->status", "$cronTask->sequence", "$cronTask->description");
		}
	}

	public function importUpdate($modulenode)
	{
		$dirName = 'cache/updates';
		$result = false;
		$db = \App\Db::getInstance();
		ob_start();
		if (file_exists($dirName . '/init.php')) {
			require_once $dirName . '/init.php';
			$updateInstance = new \YetiForceUpdate($modulenode);
			$updateInstance->package = $this;
			$result = $updateInstance->preupdate();
			file_put_contents('cache/logs/update.log', ob_get_clean(), FILE_APPEND);
			ob_start();
			if ($result !== false) {
				$updateInstance->update();
				if ($updateInstance->filesToDelete) {
					foreach ($updateInstance->filesToDelete as $path) {
						Functions::recurseDelete($path);
					}
				}
				if (method_exists($updateInstance, 'afterDelete')) {
					$updateInstance->afterDelete();
				}
				Functions::recurseCopy($dirName . '/files', '');
				if (method_exists($updateInstance, 'afterCopy')) {
					$updateInstance->afterCopy();
				}
				if ($content = ob_get_clean()) {
					file_put_contents('cache/logs/update.log', $content, FILE_APPEND);
				}
				ob_start();
				$result = $updateInstance->postupdate();
			}
		} else {
			Functions::recurseCopy($dirName . '/files', '');
		}
		$db->createCommand()->insert('yetiforce_updates', [
			'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => $modulenode->label,
			'from_version' => $modulenode->from_version,
			'to_version' => $modulenode->to_version,
			'result' => $result,
			'time' => date('Y-m-d H:i:s'),
		]);
		if ($result) {
			$db->createCommand()->update('vtiger_version', ['current_version' => $modulenode->to_version]);
		}
		Functions::recurseDelete($dirName);
		Functions::recurseDelete('cache/templates_c');

		\App\Module::createModuleMetaFile();
		\App\Cache::clear();
		\App\Cache::clearOpcache();
		file_put_contents('cache/logs/update.log', ob_get_contents(), FILE_APPEND);
		ob_end_clean();
	}

	/**
	 * Import inventory fields of the module.
	 */
	public function importInventory()
	{
		if (empty($this->_modulexml->inventory) || empty($this->_modulexml->inventory->fields->field)) {
			return false;
		}
		$module = (string) $this->moduleInstance->name;

		$inventoryInstance = \Vtiger_Inventory_Model::getInstance($module);
		$inventoryInstance->createInventoryTables();
		$inventoryFieldInstance = \Vtiger_InventoryField_Model::getInstance($module);
		foreach ($this->_modulexml->inventory->fields->field as $fieldNode) {
			$this->importInventoryField($inventoryFieldInstance, $fieldNode);
		}
	}

	public function importInventoryField($inventoryFieldInstance, $fieldNode)
	{
		$instance = \Vtiger_InventoryField_Model::getFieldInstance($inventoryFieldInstance->get('module'), $fieldNode->invtype);
		$table = $inventoryFieldInstance->getTableName();

		if ($instance->isColumnType()) {
			Utils::addColumn($table, $fieldNode->columnname, $instance->getDBType());
			foreach ($instance->getCustomColumn() as $column => $criteria) {
				Utils::addColumn($table, $column, $criteria);
			}
		}
		$db = \PearDatabase::getInstance();

		return $db->insert($inventoryFieldInstance->getTableName('fields'), [
				'columnname' => $fieldNode->columnname,
				'label' => $fieldNode->label,
				'invtype' => $fieldNode->invtype,
				'defaultvalue' => $fieldNode->defaultvalue,
				'sequence' => $fieldNode->sequence,
				'block' => $fieldNode->block,
				'displaytype' => $fieldNode->displaytype,
				'params' => $fieldNode->params,
				'colspan' => $fieldNode->colspan,
		]);
	}
}
