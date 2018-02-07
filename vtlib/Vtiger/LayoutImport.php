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
 * Provides API to import layout into vtiger CRM
 * @package vtlib
 */
class LayoutImport extends LayoutExport
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_export_tmpdir;
	}

	/**
	 * Initialize Import
	 * @access private
	 */
	public function initImport($zipfile, $overwrite = true)
	{
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
		$this->importLayout($zipfile);
	}

	/**
	 * Update Layout from zip file
	 * @param Object Instance of Layout
	 * @param String Zip file name
	 * @param Boolean True for overwriting existing module
	 */
	public function update($instance, $zipfile, $overwrite = true)
	{
		$this->import($zipfile, $overwrite);
	}

	/**
	 * Import Layout
	 * @access private
	 */
	public function importLayout($zipfile)
	{
		$name = $this->_modulexml->name;
		$label = $this->_modulexml->label;
		\App\Log::trace("Importing $name ... STARTED", __METHOD__);
		$vtiger6format = false;

		$zip = new \App\Zip($zipfile, ['illegalExtensions' => array_diff(\AppConfig::main('upload_badext'), ['js'])]);
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$fileName = $zip->getNameIndex($i);
			if (!$zip->isdir($fileName)) {
				if (strpos($fileName, '/') === false) {
					continue;
				}
				$targetdir = substr($fileName, 0, strripos($fileName, '/'));
				$targetfile = basename($fileName);
				$dounzip = false;
				// Case handling for jscalendar
				if (stripos($targetdir, "layouts/$name/skins") === 0) {
					$dounzip = true;
					$vtiger6format = true;
				}
				// vtiger6 format
				else if (stripos($targetdir, "layouts/$name/modules") === 0) {
					$vtiger6format = true;
					$dounzip = true;
				}
				//case handling for the  special library files
				else if (stripos($targetdir, "layouts/$name/libraries") === 0) {
					$vtiger6format = true;
					$dounzip = true;
				}
				if ($dounzip) {
					// vtiger6 format
					if ($vtiger6format) {
						$targetdir = "layouts/$name/" . str_replace("layouts/$name", "", $targetdir);
						@mkdir($targetdir, 0755, true);
					}
					if (!$zip->checkFile($fileName)) {
						if ($zip->unzipFile($fileName, "$targetdir/$targetfile") !== false) {
							\App\Log::trace("Copying file $fileName ... DONE", __METHOD__);
						} else {
							\App\Log::trace("Copying file $fileName ... FAILED", __METHOD__);
						}
					} else {
						\App\Log::trace("Incorrect file $fileName ... SKIPPED", __METHOD__);
					}
				} else {
					\App\Log::trace("Copying file $fileName ... SKIPPED", __METHOD__);
				}
			}
		}
		if ($zip) {
			$zip->close();
		}
		self::register($name, $label);
		\App\Log::trace("Importing $name($label) ... DONE", __METHOD__);
		return;
	}
}
