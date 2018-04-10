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
		$this->initImport($zipfile, $overwrite);

		// Call module import function
		$this->importLanguage($zipfile);
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
	 */
	public function importLanguage($zipfile)
	{
		$name = $this->_modulexml->name;
		$prefix = $this->_modulexml->prefix;
		$label = $this->_modulexml->label;
		\App\Log::trace("Importing $label [$prefix] ... STARTED", __METHOD__);
		if (strpos($prefix, '/') !== false) {
			\App\Log::error("Importing $label ... Wrong prefix - [$prefix]");

			return;
		}
		$vtiger6format = false;
		$zip = \App\Zip::openFile($zipfile);
		for ($i = 0; $i < $zip->numFiles; ++$i) {
			$fileName = $zip->getNameIndex($i);
			if ($zip->isdir($fileName)) {
				continue;
			}
			if (strpos($fileName, '/') === false) {
				continue;
			}
			$targetdir = substr($fileName, 0, strripos($fileName, '/'));
			$targetfile = basename($fileName);
			$prefixparts = explode('_', $prefix);
			$dounzip = false;
			if (is_dir($targetdir)) {
				// Case handling for jscalendar
				if (stripos($targetdir, 'jscalendar/lang') === 0 && stripos($targetfile, 'calendar-' . $prefixparts[0] . '.js') === 0) {
					if (file_exists("$targetdir/calendar-en.js")) {
						$dounzip = true;
					}
				} elseif (preg_match("/$prefix.lang.js/", $targetfile)) {
					// Handle javascript language file
					$corelangfile = "$targetdir/en_us.lang.js";
					if (file_exists($corelangfile)) {
						$dounzip = true;
					}
				}
				// Handle php language file
				elseif (preg_match("/$prefix.lang.php/", $targetfile)) {
					$corelangfile = "$targetdir/en_us.lang.php";
					if (file_exists($corelangfile)) {
						$dounzip = true;
					}
				}
				// vtiger6 format
				elseif (in_array($targetdir, ['modules', 'modules' . DIRECTORY_SEPARATOR . 'Settings', 'modules' . DIRECTORY_SEPARATOR . 'Other'])) {
					$vtiger6format = true;
					$dounzip = true;
				}
			}
			if ($dounzip) {
				// vtiger6 format
				if ($vtiger6format) {
					$targetdir = "languages/$prefix/" . str_replace('modules', '', $targetdir);
					@mkdir($targetdir, 0755, true);
				}
				if ($zip->unzipFile($fileName, "$targetdir/$targetfile") !== false) {
					\App\Log::trace("Copying file $fileName ... DONE", __METHOD__);
				} else {
					\App\Log::trace("Copying file $fileName ... FAILED", __METHOD__);
				}
			} else {
				\App\Log::trace("Copying file $fileName ... SKIPPED", __METHOD__);
			}
		}
		if ($zip) {
			$zip->close();
		}
		self::register($prefix, $label, $name);
		\App\Log::trace("Importing $label [$prefix] ... DONE", __METHOD__);
	}
}
