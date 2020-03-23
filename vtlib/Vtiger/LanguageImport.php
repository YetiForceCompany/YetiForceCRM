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
 * Provides API to import language into vtiger CRM.
 */
class LanguageImport extends LanguageExport
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_export_tmpdir;
	}

	public function getPrefix()
	{
		return (string) $this->_modulexml->prefix;
	}

	/**
	 * Initialize Import.
	 */
	public function initImport($zipfile, $overwrite = true)
	{
		return $this->getModuleNameFromZip($zipfile);
	}

	/**
	 * Import Module from zip file.
	 *
	 * @param string Zip file name
	 * @param bool True for overwriting existing module
	 */
	public function import($zipfile, $overwrite = false)
	{
		if ($this->initImport($zipfile, $overwrite)) {
			$this->importLanguage($zipfile);
		}
	}

	/**
	 * Update Module from zip file.
	 *
	 * @param object Instance of Language (to keep Module update API consistent)
	 * @param string Zip file name
	 * @param bool True for overwriting existing module
	 */
	public function update($instance, $zipfile, $overwrite = true)
	{
		$this->import($zipfile, $overwrite);
	}

	/**
	 * Import Module.
	 *
	 * @param string $zipfile
	 */
	public function importLanguage(string $zipfile)
	{
		$prefix = $this->_modulexml->prefix;
		$label = $this->_modulexml->name;
		\App\Log::trace("Importing $label [$prefix] ... STARTED", __METHOD__);
		$zip = \App\Zip::openFile($zipfile, ['onlyExtensions' => ['json']]);
		$languages = 'languages/' . $prefix;
		$custom = 'custom/' . $languages;
		$zip->unzip([
			$custom => $custom,
			$languages => $languages,
		]);
		self::register($prefix, $label, null, true, (int)$this->_modulexml->progress);
		\App\Cache::clear();
		\App\Log::trace("Importing $label [$prefix] ... DONE", __METHOD__);
	}
}
