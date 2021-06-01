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
 * Provides API to import layout into vtiger CRM.
 */
class LayoutImport extends LayoutExport
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
	 *
	 * @param mixed $zipfile
	 * @param mixed $overwrite
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
	 * @param mixed $zipfile
	 * @param mixed $overwrite
	 */
	public function import($zipfile, $overwrite = false)
	{
		$this->initImport($zipfile, $overwrite);

		// Call module import function
		$this->importLayout($zipfile);
	}

	/**
	 * Update Layout from zip file.
	 *
	 * @param object Instance of Layout
	 * @param string Zip file name
	 * @param bool True for overwriting existing module
	 * @param mixed $instance
	 * @param mixed $zipfile
	 * @param mixed $overwrite
	 */
	public function update($instance, $zipfile, $overwrite = true)
	{
		$this->import($zipfile, $overwrite);
	}

	/**
	 * Import Layout.
	 *
	 * @param mixed $zipfile
	 */
	public function importLayout($zipfile)
	{
		$name = $this->_modulexml->name;
		$label = $this->_modulexml->label;
		\App\Log::trace("Importing $name ... STARTED", __METHOD__);
		$vtiger6format = false;

		$zip = \App\Zip::openFile($zipfile, ['illegalExtensions' => array_diff(\App\Config::main('upload_badext'), ['js'])]);
		for ($i = 0; $i < $zip->numFiles; ++$i) {
			$fileName = $zip->getNameIndex($i);
			if (!$zip->isdir($fileName)) {
				if (false === strpos($fileName, '/')) {
					continue;
				}
				$targetdir = substr($fileName, 0, strripos($fileName, '/'));
				$targetfile = basename($fileName);
				$dounzip = false;
				// Case handling for jscalendar
				if (0 === stripos($targetdir, "layouts/$name/skins")) {
					$dounzip = true;
					$vtiger6format = true;
				} // vtiger6 format
				elseif (0 === stripos($targetdir, "layouts/$name/modules")) {
					$vtiger6format = true;
					$dounzip = true;
				} //case handling for the  special library files
				elseif (0 === stripos($targetdir, "layouts/$name/libraries")) {
					$vtiger6format = true;
					$dounzip = true;
				}
				if ($dounzip) {
					// vtiger6 format
					if ($vtiger6format) {
						$targetdir = "layouts/$name/" . str_replace("layouts/$name", '', $targetdir);
						@mkdir($targetdir, 0755, true);
					}
					if (!$zip->checkFile($fileName)) {
						if (false !== $zip->unzipFile($fileName, "$targetdir/$targetfile")) {
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
	}
}
