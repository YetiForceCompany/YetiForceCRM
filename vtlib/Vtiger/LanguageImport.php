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
 * Provides API to import language into vtiger CRM
 * @package vtlib
 */
class LanguageImport extends LanguageExport
{

	/**
	 * Constructor
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
	 * Initialize Import
	 * @access private
	 */
	public function initImport($zipfile, $overwrite = true)
	{
		$this->__initSchema();

		$name = $this->getModuleNameFromZip($zipfile);
		return $name;
	}

	/**
	 * Import Module from zip file
	 * @param String Zip file name
	 * @param Boolean True for overwriting existing module
	 */
	public function import($zipfile, $overwrite = false)
	{
		$this->initImport($zipfile, $overwrite);

		// Call module import function
		$this->import_Language($zipfile);
	}

	/**
	 * Update Module from zip file
	 * @param Object Instance of Language (to keep Module update API consistent)
	 * @param String Zip file name
	 * @param Boolean True for overwriting existing module
	 */
	public function update($instance, $zipfile, $overwrite = true)
	{
		$this->import($zipfile, $overwrite);
	}

	/**
	 * Import Module
	 * @access private
	 */
	public function import_Language($zipfile)
	{
		$name = $this->_modulexml->name;
		$prefix = $this->_modulexml->prefix;
		$label = $this->_modulexml->label;

		self::log("Importing $label [$prefix] ... STARTED");
		$unzip = new Unzip($zipfile);
		$filelist = $unzip->getList();
		$vtiger6format = false;

		foreach ($filelist as $filename => $fileinfo) {
			if (!$unzip->isdir($filename)) {

				if (strpos($filename, '/') === false)
					continue;

				$targetdir = substr($filename, 0, strripos($filename, '/'));
				$targetfile = basename($filename);

				$prefixparts = explode('_', $prefix);

				$dounzip = false;
				if (is_dir($targetdir)) {
					// Case handling for jscalendar
					if (stripos($targetdir, 'jscalendar/lang') === 0 && stripos($targetfile, "calendar-" . $prefixparts[0] . ".js") === 0) {

						if (file_exists("$targetdir/calendar-en.js")) {
							$dounzip = true;
						}
					} else if (preg_match("/$prefix.lang.js/", $targetfile)) {// Handle javascript language file
						$corelangfile = "$targetdir/en_us.lang.js";
						if (file_exists($corelangfile)) {
							$dounzip = true;
						}
					}
					// Handle php language file
					else if (preg_match("/$prefix.lang.php/", $targetfile)) {
						$corelangfile = "$targetdir/en_us.lang.php";
						if (file_exists($corelangfile)) {
							$dounzip = true;
						}
					}
					// vtiger6 format
					else if ($targetdir == "modules" || $targetdir == "modules/Settings" || $targetdir == "modules" . DIRECTORY_SEPARATOR . "Settings") {
						$vtiger6format = true;
						$dounzip = true;
					}
				}

				if ($dounzip) {
					// vtiger6 format
					if ($vtiger6format) {
						$targetdir = "languages/$prefix/" . str_replace("modules", "", $targetdir);
						@mkdir($targetdir, 0777, true);
					}

					if ($unzip->unzip($filename, "$targetdir/$targetfile") !== false) {
						self::log("Copying file $filename ... DONE");
					} else {
						self::log("Copying file $filename ... FAILED");
					}
				} else {
					self::log("Copying file $filename ... SKIPPED");
				}
			}
		}
		if ($unzip)
			$unzip->close();

		self::register($prefix, $label, $name);

		self::log("Importing $label [$prefix] ... DONE");

		return;
	}
}
