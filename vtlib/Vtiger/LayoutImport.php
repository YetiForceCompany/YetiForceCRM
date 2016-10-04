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
	public function initImport($zipfile, $overwrite)
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
		$this->import_Layout($zipfile);
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
	public function import_Layout($zipfile)
	{
		$name = $this->_modulexml->name;
		$label = $this->_modulexml->label;

		self::log("Importing $name ... STARTED");
		$unzip = new Unzip($zipfile);
		$filelist = $unzip->getList();
		$vtiger6format = false;

		$badFileExtensions = array_diff(vglobal('upload_badext'), ['js']);

		foreach ($filelist as $filename => $fileinfo) {
			if (!$unzip->isdir($filename)) {

				if (strpos($filename, '/') === false)
					continue;


				$targetdir = substr($filename, 0, strripos($filename, '/'));
				$targetfile = basename($filename);
				$dounzip = false;
				$fileValidation = true;
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
					$filepath = 'zip://' . ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $zipfile . '#' . $filename;
					$fileInfo = pathinfo($filepath);
					if (in_array($fileInfo['extension'], $badFileExtensions)) {
						$fileValidation = false;
					}
					// Check for php code injection
					if (preg_match('/(<\?php?(.*?))/i', file_get_contents($filepath)) == 1) {
						$fileValidation = false;
					}
					if ($fileValidation) {
						if ($unzip->unzip($filename, "$targetdir/$targetfile") !== false) {
							self::log("Copying file $filename ... DONE");
						} else {
							self::log("Copying file $filename ... FAILED");
						}
					} else {
						self::log("Incorrect file $filename ... SKIPPED");
					}
				} else {
					self::log("Copying file $filename ... SKIPPED");
				}
			}
		}
		if ($unzip)
			$unzip->close();

		self::register($name, $label);

		self::log("Importing $name($label) ... DONE");
		return;
	}
}
