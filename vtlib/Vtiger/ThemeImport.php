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
class ThemeImport extends ThemeExport
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_export_tmpdir;
	}

	/**
	 * Initialize Import.
	 */
	public function initImport($zipfile, $overwrite = true)
	{
		$this->__initSchema();
		$name = $this->getModuleNameFromZip($zipfile);

		return $name;
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
		$this->importTheme($zipfile);
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
	public function importTheme($zipfile)
	{
		$name = $this->_modulexml->name;
		$label = $this->_modulexml->label;
		$parent = $this->_modulexml->parent;
		\App\Log::trace("Importing $label ... STARTED", __METHOD__);
		$vtiger6format = false;

		$zip = new \App\Zip($zipfile);
		for ($i = 0; $i < $zip->numFiles; ++$i) {
			$fileName = $zip->getNameIndex($i);
			if (!$zip->isdir($fileName)) {
				if (strpos($fileName, '/') === false) {
					continue;
				}
				$targetdir = substr($fileName, 0, strripos($fileName, '/'));
				$targetfile = basename($fileName);
				$dounzip = false;
				// Case handling for jscalendar
				if (stripos($targetdir, "layouts/$parent/skins/$label") === 0) {
					$dounzip = true;
					$vtiger6format = true;
				}
				if ($dounzip) {
					// vtiger6 format
					if ($vtiger6format) {
						$targetdir = "layouts/$parent/skins/" . str_replace("layouts/$parent/skins", '', $targetdir);
						@mkdir($targetdir, 0777, true);
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
		}
		if ($zip) {
			$zip->close();
		}
		self::register($label, $name, $parent);
		\App\Log::trace("Importing $label [$prefix] ... DONE", __METHOD__);
	}
}
