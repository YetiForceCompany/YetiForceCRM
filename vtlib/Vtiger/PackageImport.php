<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
		if ($content = $zip->getFromName('manifest.xml')) {
			$this->_modulexml = simplexml_load_string($content);
			return true;
		}
		return false;
	}

	/**
	 * Get type of package (as specified in manifest).
	 *
	 * @return false|string
	 */
	public function type()
	{
		if (!empty($this->_modulexml) && !empty($this->_modulexml->type)) {
			return (string) $this->_modulexml->type;
		}
		return false;
	}

	/**
	 * Get type of package (as specified in manifest).
	 */
	public function getTypeName()
	{
		if (!empty($this->_modulexml) && !empty($this->_modulexml->type)) {
			$type = strtolower($this->_modulexml->type);
			switch ($type) {
				case 'extension':
					$type = 'LBL_EXTENSION_MODULE';
					break;
				case 'entity':
					$type = 'LBL_BASE_MODULE';
					break;
				case 'inventory':
					$type = 'LBL_INVENTORY_MODULE';
					break;
				case 'language':
					$type = 'LBL_LANGUAGE_MODULE';
					break;
				default:
					break;
			}

			return $type;
		}
		return '';
	}

	/**
	 * XPath evaluation on the root module node.
	 *
	 * @param string Path expression
	 * @param mixed $path
	 */
	public function xpath($path)
	{
		return $this->_modulexml->xpath($path);
	}

	/**
	 * Are we trying to import language package?
	 *
	 * @param mixed|null $zipfile
	 */
	public function isLanguageType($zipfile = null)
	{
		if (!empty($zipfile) && !$this->checkZip($zipfile)) {
			return false;
		}
		$packagetype = $this->type();
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ('language' === $lcasetype) {
				return true;
			}
		}
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ('layout' === $lcasetype) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Are we trying to import extension package?
	 *
	 * @param mixed|null $zipfile
	 */
	public function isExtensionType($zipfile = null)
	{
		if (!empty($zipfile) && !$this->checkZip($zipfile)) {
			return false;
		}
		$packagetype = $this->type();
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ('extension' === $lcasetype) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks font package type.
	 *
	 * @param null $zipfile
	 *
	 * @return bool
	 */
	public function isFontType($zipfile = null)
	{
		if (!empty($zipfile) && !$this->checkZip($zipfile)) {
			return false;
		}
		$packagetype = $this->type();
		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ('font' === $lcasetype) {
				return true;
			}
		}
		return false;
	}

	public function isUpdateType($zipfile = null)
	{
		if (!empty($zipfile) && !$this->checkZip($zipfile)) {
			return false;
		}
		$packagetype = $this->type();

		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ('update' === $lcasetype) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Are we trying to import language package?
	 *
	 * @param mixed|null $zipfile
	 */
	public function isLayoutType($zipfile = null)
	{
		if (!empty($zipfile) && !$this->checkZip($zipfile)) {
			return false;
		}
		$packagetype = $this->type();

		if ($packagetype) {
			$lcasetype = strtolower($packagetype);
			if ('layout' === $lcasetype) {
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
		if (!empty($zipfile) && !$this->checkZip($zipfile)) {
			return false;
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
		$params = [];
		if (empty($this->_modulexml->parameters)) {
			return $params;
		}
		foreach ($this->_modulexml->parameters->parameter as $parameter) {
			$params[] = $parameter;
		}
		return $params;
	}

	public function initParameters(\App\Request $request)
	{
		$data = [];
		foreach ($request->getAll() as $name => $value) {
			if (false !== strpos($name, 'param_')) {
				$name = str_replace('param_', '', $name);
				$data[$name] = $value;
			}
		}
		$this->parameters = $data;
	}

	/**
	 * Check if zipfile is a valid package.
	 *
	 * @param mixed $zipfile
	 */
	public function checkZip($zipfile)
	{
		$manifestFound = $languagefile_found = $layoutfile_found = $updatefile_found = $extensionfile_found = $moduleVersionFound = $fontfile_found = false;
		$moduleName = null;
		$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
		if ($this->__parseManifestFile($zip)) {
			$manifestFound = true;
			$moduleName = (string) $this->_modulexml->name;
			$isModuleBundle = (string) $this->_modulexml->modulebundle;
			if ('true' === $isModuleBundle && (!empty($this->_modulexml))
					&& (!empty($this->_modulexml->dependencies))
					&& (!empty($this->_modulexml->dependencies->vtiger_version))) {
				$languagefile_found = true;
			}
			// Do we need to check the zip further?
			if ($this->isLanguageType()) {
				$languagefile_found = true; // No need to search for module language file.
			}
			if ($this->isLayoutType()) {
				$layoutfile_found = true; // No need to search for module language file.
			}
			if ($this->isExtensionType()) {
				$extensionfile_found = true; // No need to search for module language file.
			}
			if ($this->isUpdateType()) {
				$updatefile_found = true; // No need to search for module language file.
			}
			if ($this->isFontType()) {
				$fontfile_found = true; // No need to search for module language file.
			}
		}
		for ($i = 0; $i < $zip->numFiles; ++$i) {
			$fileName = $zip->getNameIndex($i);
			$matches = [];
			$pattern = '/languages[\/\\\]' . \App\Config::main('default_language') . '[\/\\\]([^\/]+)\.json/';
			preg_match($pattern, $fileName, $matches);
			if (\count($matches) && \in_array($moduleName, $matches)) {
				$languagefile_found = true;
			}
			$settingsPattern = '/languages[\/\\\]' . \App\Config::main('default_language') . '[\/\\\]Settings[\/\\\]([^\/]+)\.json/';
			preg_match($settingsPattern, $fileName, $matches);
			if (\count($matches) && \in_array($moduleName, $matches)) {
				$languagefile_found = true;
			}
		}
		// Verify module language file.
		if (!$fontfile_found && !$updatefile_found && !$layoutfile_found && !$languagefile_found) {
			$errorText = \App\Language::translate('LBL_ERROR_NO_DEFAULT_LANGUAGE', 'Settings:ModuleManager');
			$errorText = str_replace('__DEFAULTLANGUAGE__', \App\Config::main('default_language'), $errorText);
			$this->_errorText = $errorText;
		}
		if (!empty($this->_modulexml)
			&& !empty($this->_modulexml->dependencies)
			&& !empty($this->_modulexml->dependencies->vtiger_version)) {
			$moduleVersion = (string) $this->_modulexml->dependencies->vtiger_version;
			$versionCheck = \App\Version::compare(\App\Version::get(), $moduleVersion);
			if (false !== $versionCheck && $versionCheck >= 0) {
				$moduleVersionFound = true;
			} else {
				$errorText = \App\Language::translate('LBL_ERROR_VERSION', 'Settings:ModuleManager');
				$errorText = str_replace('__MODULEVERSION__', $moduleVersion, $errorText);
				$errorText = str_replace('__CRMVERSION__', \App\Version::get(), $errorText);
				$this->_errorText = $errorText;
			}
		}
		$validzip = false;
		if ($manifestFound) {
			if ($languagefile_found && $moduleVersionFound) {
				$validzip = true;
			}
			if ($layoutfile_found && $moduleVersionFound) {
				$validzip = true;
			}
			if ($extensionfile_found && $moduleVersionFound) {
				$validzip = true;
			}
			if ($updatefile_found && $moduleVersionFound) {
				$validzip = true;
			}
			if ($fontfile_found) {
				$validzip = true;
			}
			if ($this->isLanguageType() && false !== strpos($this->_modulexml->prefix, '/')) {
				$validzip = false;
				$this->_errorText = \App\Language::translate('LBL_ERROR_NO_VALID_PREFIX', 'Settings:ModuleManager');
			}
			if (!empty($moduleName) && !empty($this->_modulexml->type) && \Settings_ModuleManager_Module_Model::checkModuleName($moduleName) && \in_array(strtolower($this->_modulexml->type), ['entity', 'inventory', 'extension'])) {
				$validzip = false;
				$this->_errorText = \App\Language::translate('LBL_INVALID_MODULE_NAME', 'Settings:ModuleManager');
			}
		}
		if ($validzip && !empty($this->_modulexml->license)) {
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
		if ($zip) {
			$zip->close();
		}
		return $validzip;
	}

	/**
	 * Get module name packaged in the zip file.
	 *
	 * @param mixed $zipfile
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
	 *
	 * @param mixed $moduleInstance
	 * @param mixed $fieldname
	 * @param mixed $fieldInstance
	 */
	public function __AddModuleFieldToCache($moduleInstance, $fieldname, $fieldInstance)
	{
		$this->_modulefields_cache["$moduleInstance->name"]["$fieldname"] = $fieldInstance;
	}

	/**
	 * Get field instance from cache.
	 *
	 * @param mixed $moduleInstance
	 * @param mixed $fieldname
	 */
	public function __GetModuleFieldFromCache($moduleInstance, $fieldname)
	{
		return $this->_modulefields_cache["$moduleInstance->name"]["$fieldname"];
	}

	/**
	 * Initialize Import.
	 *
	 * @param mixed $zipfile
	 * @param mixed $overwrite
	 */
	public function initImport($zipfile, $overwrite = true)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if (null !== $module) {
			$defaultLayout = \Vtiger_Viewer::getDefaultLayoutName();
			$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
			if ($zip->statName("$module.png")) {
				$zip->unzipFile("$module.png", "layouts/$defaultLayout/images/$module.png");
			}
			$zip->unzip([
				// Templates folder
				'templates' => "layouts/$defaultLayout/modules/$module",
				'public_resources' => "public_html/layouts/$defaultLayout/modules/$module/resources",
				// Cron folder
				'cron' => "cron/modules/$module",
				// Config
				'config' => 'config/Modules',
				// Modules folder
				'modules' => 'modules',
				// Settings folder
				'settings/modules' => "modules/Settings/$module",
				// Settings templates folder
				'settings/templates' => "layouts/$defaultLayout/modules/Settings/$module",
				'settings/public_resources' => "public_html/layouts/$defaultLayout/modules/Settings/$module/resources",
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
	 *
	 * @return string
	 */
	public function getDependentVtigerVersion(): string
	{
		return $this->_modulexml->dependencies->vtiger_version ?? '';
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

	/**
	 * Get premium.
	 *
	 * @return int
	 */
	public function getPremium(): int
	{
		return (int) $this->_modulexml->premium;
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
	 * @param mixed $zipfile
	 * @param mixed $overwrite
	 */
	public function import($zipfile, $overwrite = false)
	{
		$module = $this->getModuleNameFromZip($zipfile);
		if (null !== $module) {
			$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
			// If data is not yet available
			if (empty($this->_modulexml)) {
				$this->__parseManifestFile($zip);
			}
			$buildModuleArray = [];
			$installSequenceArray = [];
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
							$this->import($this->getTemporaryFilePath($moduleInfo['filepath']), $overwrite);
						}
					}
				}
			} else {
				$this->packageType = strtolower($this->_modulexml->type);
				switch ((string) $this->_modulexml->type) {
					case 'update':
						Functions::recurseDelete('cache/updates');
						$zip = \App\Zip::openFile($zipfile, ['checkFiles' => false]);
						$zip->extract('cache/updates');
						$this->importUpdate();
						break;
					case 'font':
						$this->importFont($zipfile);
						break;
					default:
						$this->initImport($zipfile, $overwrite);
						// Call module import function
						$this->importModule();
						break;
				}
			}
		}
		return $module;
	}

	/**
	 * Import Module.
	 */
	public function importModule()
	{
		$moduleName = (string) $this->_modulexml->name;
		$tabLabel = $this->_modulexml->label;
		$tabVersion = $this->_modulexml->version;
		$isextension = false;
		$moduleType = 0;
		if (!empty($this->_modulexml->type)) {
			$this->packageType = strtolower($this->_modulexml->type);
			if ('extension' == $this->packageType || 'language' == $this->packageType) {
				$isextension = true;
			}
			if ('inventory' == $this->packageType) {
				$moduleType = 1;
			}
		}

		$vtigerMinVersion = $this->_modulexml->dependencies->vtiger_version;
		$vtigerMaxVersion = $this->_modulexml->dependencies->vtiger_max_version;

		$moduleInstance = new Module();
		$moduleInstance->name = $moduleName;
		$moduleInstance->label = $tabLabel;
		$moduleInstance->isentitytype = (true !== $isextension);
		$moduleInstance->version = (!$tabVersion) ? 0 : $tabVersion;
		$moduleInstance->minversion = (!$vtigerMinVersion) ? false : $vtigerMinVersion;
		$moduleInstance->maxversion = (!$vtigerMaxVersion) ? false : $vtigerMaxVersion;
		$moduleInstance->type = $moduleType;
		$moduleInstance->premium = $this->getPremium();

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
		register_shutdown_function(function () {
			try {
				chdir(ROOT_DIRECTORY);
				(new \App\BatchMethod(['method' => '\App\UserPrivilegesFile::recalculateAll', 'params' => []]))->save();
			} catch (\Throwable $e) {
				\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
				throw $e;
			}
		});
	}

	/**
	 * Import Tables of the module.
	 *
	 * @param mixed $modulenode
	 */
	public function importTables($modulenode)
	{
		if (empty($modulenode->tables) || empty($modulenode->tables->table)) {
			return;
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();

		// Import the table via queries
		foreach ($modulenode->tables->table as $tablenode) {
			$tableName = $tablenode->name;
			$sql = (string) $tablenode->sql; // Convert to string format
			// Avoid executing SQL that will DELETE or DROP table data
			if (Utils::isCreateSql($sql)) {
				if (!Utils::checkTable($tableName)) {
					\App\Log::trace("SQL: $sql ... ", __METHOD__);
					$db->createCommand($sql)->execute();
					\App\Log::trace('DONE', __METHOD__);
				}
			} else {
				if (Utils::isDestructiveSql($sql)) {
					\App\Log::trace("SQL: $sql ... SKIPPED", __METHOD__);
				} else {
					\App\Log::trace("SQL: $sql ... ", __METHOD__);
					$db->createCommand($sql)->execute();
					\App\Log::trace('DONE', __METHOD__);
				}
			}
		}
		$db->createCommand()->checkIntegrity(true)->execute();
	}

	/**
	 * Import Blocks of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $blocknode
	 */
	public function importBlock($modulenode, $moduleInstance, $blocknode)
	{
		$blocklabel = $blocknode->blocklabel;
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
	 *
	 * @param mixed $blocknode
	 * @param mixed $blockInstance
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $blocknode
	 * @param mixed $blockInstance
	 * @param mixed $moduleInstance
	 * @param mixed $fieldnode
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
		$fieldInstance->maximumlength = (string) $fieldnode->maximumlength;
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
		if (!empty($fieldnode->numberInfo)) {
			$numberInfo = $fieldnode->numberInfo;
			\App\Fields\RecordNumber::getInstance($moduleInstance->id)->set('tabid', $moduleInstance->id)->set('prefix', $numberInfo->prefix)->set('leading_zeros', $numberInfo->leading_zeros)->set('postfix', $numberInfo->postfix)->set('start_id', $numberInfo->start_id)->set('cur_id', $numberInfo->cur_id)->set('reset_sequence', $numberInfo->reset_sequence)->set('cur_sequence', $numberInfo->cur_sequence)->save();
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $customviewnode
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
			if ((string) $fieldnode->modulename === $moduleInstance->name) {
				$fieldInstance = $this->__GetModuleFieldFromCache($moduleInstance, $fieldnode->fieldname);
			} else {
				$fieldInstance = Field::getInstance((string) $fieldnode->fieldname, Module::getInstance((string) $fieldnode->modulename));
			}
			if ($sourceFieldName = (string) $fieldnode->sourcefieldname ?? '') {
				$fieldInstance->sourcefieldname = $sourceFieldName;
			}
			$filterInstance->addField($fieldInstance, $fieldnode->columnindex);
		}
		if (!empty($customviewnode->rules)) {
			$filterInstance->addRule(\App\Json::decode($customviewnode->rules));
		}
	}

	/**
	 * Import Sharing Access of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $actionnode
	 */
	public function importAction($modulenode, $moduleInstance, $actionnode)
	{
		$actionstatus = (string) $actionnode->status;
		if ('enabled' === $actionstatus) {
			$moduleInstance->enableTools((string) $actionnode->name);
		} else {
			$moduleInstance->disableTools((string) $actionnode->name);
		}
	}

	/**
	 * Import related lists of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
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
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 * @param mixed $relatedlistnode
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
		$fields = [];
		if (!empty($relatedlistnode->fields)) {
			foreach ($relatedlistnode->fields->field as $fieldNode) {
				$fields[] = "$fieldNode";
			}
		}
		if ($relModuleInstance) {
			$moduleInstance->setRelatedList($relModuleInstance, "$label", $actions, "$relatedlistnode->function", "$relatedlistnode->field_name", $fields);
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
		$fields = [];
		if (!empty($inRelatedListNode->fields)) {
			foreach ($inRelatedListNode->fields->field as $fieldNode) {
				$fields[] = "$fieldNode";
			}
		}
		if ($inRelModuleInstance) {
			$inRelModuleInstance->setRelatedList($moduleInstance, "$label", $actions, "$inRelatedListNode->function", "$inRelatedListNode->field_name", $fields);
		}
		return $inRelModuleInstance;
	}

	/**
	 * Import custom links of the module.
	 *
	 * @param mixed $modulenode
	 * @param mixed $moduleInstance
	 */
	public function importCustomLinks($modulenode, $moduleInstance)
	{
		if (empty($modulenode->customlinks) || empty($modulenode->customlinks->customlink)) {
			return;
		}

		foreach ($modulenode->customlinks->customlink as $customlinknode) {
			$handlerInfo = null;
			if (isset($customlinknode->handler_path)) {
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
	 *
	 * @param mixed $modulenode
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

	public function importUpdate()
	{
		$dirName = 'cache/updates/updates';
		$db = \App\Db::getInstance();
		$startTime = microtime(true);
		file_put_contents('cache/logs/update.log', PHP_EOL . ((string) $this->_modulexml->label) . ' - ' . date('Y-m-d H:i:s'), FILE_APPEND);
		ob_start();
		if (file_exists($dirName . '/init.php')) {
			require_once $dirName . '/init.php';
			$updateInstance = new \YetiForceUpdate($this->_modulexml);
			$updateInstance->package = $this;
			$result = $updateInstance->preupdate();
			file_put_contents('cache/logs/update.log', PHP_EOL . ' | ' . ob_get_clean(), FILE_APPEND);
			ob_start();
			if (false !== $result) {
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
			$result = true;
		}
		$db->createCommand()->insert('yetiforce_updates', [
			'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => (string) $this->_modulexml->label,
			'from_version' => (string) $this->_modulexml->from_version,
			'to_version' => (string) $this->_modulexml->to_version,
			'result' => $result,
			'time' => date('Y-m-d H:i:s'),
		])->execute();
		if ($result) {
			$db->createCommand()->update('vtiger_version', ['current_version' => (string) $this->_modulexml->to_version])->execute();
		}
		Functions::recurseDelete($dirName);
		register_shutdown_function(function () {
			try {
				Functions::recurseDelete('cache/templates_c');
			} catch (\Exception $e) {
				\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
			}
		});
		\App\Module::createModuleMetaFile();
		\App\Cache::clear();
		\App\Cache::clearOpcache();
		Functions::recurseDelete('app_data/LanguagesUpdater.json');
		Functions::recurseDelete('app_data/SystemUpdater.json');
		Functions::recurseDelete('app_data/cron.php');
		Functions::recurseDelete('app_data/ConfReport_AllErrors.php');
		Functions::recurseDelete('app_data/shop.php');
		file_put_contents('cache/logs/update.log', PHP_EOL . date('Y-m-d H:i:s') . ' (' . round(microtime(true) - $startTime, 2) . ') | ' . ob_get_clean(), FILE_APPEND);
	}

	/**
	 * Import inventory fields of the module.
	 */
	public function importInventory()
	{
		if (1 !== $this->moduleInstance->type) {
			return false;
		}
		$module = (string) $this->moduleInstance->name;
		$inventory = \Vtiger_Inventory_Model::getInstance($module);
		$inventory->createInventoryTables();
		if (empty($this->_modulexml->inventory) || empty($this->_modulexml->inventory->fields->field)) {
			return false;
		}
		foreach ($this->_modulexml->inventory->fields->field as $fieldNode) {
			$fieldModel = $inventory->getFieldCleanInstance((string) $fieldNode->invtype);
			$fieldModel->setDefaultDataConfig();
			$fields = ['label', 'defaultValue', 'block', 'displayType', 'params', 'colSpan', 'columnName', 'sequence'];
			foreach ($fields as $name) {
				switch ($name) {
					case 'label':
						$value = \App\Purifier::purifyByType((string) $fieldNode->label, 'Text');
						$fieldModel->set($name, $value);
						break;
					case 'defaultValue':
						$value = \App\Purifier::purifyByType((string) $fieldNode->defaultvalue, 'Text');
						$fieldModel->set($name, $value);
						break;
					case 'block':
						$blockId = (int) $fieldNode->block;
						if (!\in_array($blockId, $fieldModel->getBlocks())) {
							throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$name}||" . $blockId, 406);
						}
						$fieldModel->set($name, $blockId);
						break;
					case 'displayType':
						$displayType = (int) $fieldNode->displaytype;
						if (!\in_array($displayType, $fieldModel->displayTypeBase())) {
							throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$name}||" . $displayType, 406);
						}
						$fieldModel->set($name, $displayType);
						break;
					case 'params':
						$value = \App\Purifier::purifyByType((string) $fieldNode->params, 'Text');
						$fieldModel->set($name, $value);
						break;
					case 'colSpan':
						$fieldModel->set($name, (int) $fieldNode->colspan);
						break;
					case 'columnName':
						$fieldModel->set($name, \App\Purifier::purifyByType((string) $fieldNode->columnname, 'Alnum'));
						break;
					case 'sequence':
						$fieldModel->set($name, (int) $fieldNode->sequence);
						break;
					default:
						break;
				}
			}
			$inventory->saveField($fieldModel);
		}
	}

	/**
	 * Import font package.
	 *
	 * @param string $zipfile
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function importFont($zipfile)
	{
		$fontsDir = ROOT_DIRECTORY . '/public_html/layouts/resources/fonts';
		$zip = \App\Zip::openFile($zipfile, ['onlyExtensions' => ['ttf', 'txt', 'woff']]);
		$files = $zip->unzip(['fonts' => $fontsDir]);
		$fonts = \App\Json::read($fontsDir . '/fonts.json');
		$tempFonts = [];
		foreach ($fonts as $font) {
			$tempFonts[$font['family']][$font['weight']][$font['style']] = $font['file'];
		}
		foreach ($files as $key => &$file) {
			$file = \str_replace('fonts/', '', $file);
			if (empty($file)) {
				unset($files[$key]);
			}
		}
		$files = \array_flip($files);
		$missing = [];
		if (!empty($this->_modulexml->fonts->font)) {
			foreach ($this->_modulexml->fonts->font as $font) {
				if (!isset($files[(string) $font->file])) {
					$missing[] = (string) $font->file;
				}
				if (!isset($tempFonts[(string) $font->family][(string) $font->weight][(string) $font->style])) {
					$fonts[] = [
						'family' => (string) $font->family,
						'weight' => (string) $font->weight,
						'style' => (string) $font->style,
						'file' => (string) $font->file,
					];
				}
			}
		}
		if ($missing) {
			$this->_errorText = \App\Language::translate('LBL_ERROR_MISSING_FILES', 'Settings:ModuleManager') . ' ' . \implode(',', $missing);
		}
		$css = [];
		foreach ($fonts as $key => $font) {
			if (!\file_exists("$fontsDir/{$font['file']}")) {
				unset($fonts[$key]);
			} else {
				$woff = pathinfo($font['file'], PATHINFO_FILENAME) . '.woff';
				$fontCss = "@font-face {\n";
				$fontCss .= "    font-family: '{$font['family']}';\n";
				$fontCss .= "    font-style: {$font['style']};\n";
				$fontCss .= "    font-weight: {$font['weight']};\n";
				$fontCss .= "    src: local('{$font['family']}'), url({$woff}) format('woff');\n";
				$fontCss .= '}';
				$css[] = $fontCss;
			}
		}
		$css[] = '@font-face {
			font-family: \'DejaVu Sans\';
			font-style: normal;
			font-weight: 100;
			src: local(\'DejaVu Sans\'), url(\'DejaVuSans-ExtraLight.woff\') format(\'woff\');
		}
		@font-face {
			font-family: \'DejaVu Sans\';
			font-style: normal;
			font-weight: 400;
			src: local(\'DejaVu Sans\'), url(\'DejaVuSans.woff\') format(\'woff\');
		}
		@font-face {
			font-family: \'DejaVu Sans\';
			font-style: normal;
			font-weight: 700;
			src: local(\'DejaVu Sans\'), url(\'DejaVuSans-Bold.woff\') format(\'woff\');
		}
		@font-face {
			font-family: \'DejaVu Sans\';
			font-style: italic;
			font-weight: 700;
			src: local(\'DejaVu Sans\'), url(\'DejaVuSans-BoldOblique.woff\') format(\'woff\');
		}
		@font-face {
			font-family: \'DejaVu Sans\';
			font-style: italic;
			font-weight: 400;
			src: local(\'DejaVu Sans\'), url(\'DejaVuSans-Oblique.woff\') format(\'woff\');
		}
		* {
			font-family: \'DejaVu Sans\';
		}';
		file_put_contents($fontsDir . '/fonts.css', implode("\n", $css));
		\App\Json::save($fontsDir . '/fonts.json', array_values($fonts));
	}
}
