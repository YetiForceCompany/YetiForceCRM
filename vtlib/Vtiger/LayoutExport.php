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
 * Provides API to package vtiger CRM layout files.
 * @package vtlib
 */
class LayoutExport extends Package
{

	const TABLENAME = 'vtiger_layout';

	/**
	 * Generate unique id for insertion
	 * @access private
	 */
	static function __getUniqueId()
	{
		$adb = \PearDatabase::getInstance();
		return $adb->getUniqueID(self::TABLENAME);
	}

	/**
	 * Initialize Export
	 * @access private
	 */
	public function __initExport($layoutName)
	{
		// Security check to ensure file is withing the web folder.
		Utils::checkFileAccessForInclusion("layouts/$layoutName/skins/style.less");

		$this->_export_modulexml_file = fopen($this->__getManifestFilePath(), 'w');
		$this->__write("<?xml version='1.0'?>\n");
	}

	/**
	 * Export Module as a zip file.
	 * @param Layout name to be export
	 * @param Path Output directory path
	 * @param String Zipfilename to use
	 * @param Boolean True for sending the output as download
	 */
	public function export($layoutName, $todir = '', $zipfilename = '', $directDownload = false)
	{
		$this->__initExport($layoutName);

		// Call layout export function
		$this->export_Layout($layoutName);

		$this->__finishExport();

		// Export as Zip
		if ($zipfilename == '')
			$zipfilename = "$layoutName-" . date('YmdHis') . ".zip";
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = new Zip($zipfilename);

		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');

		// Copy module directory
		$zip->copyDirectoryFromDisk('layouts/' . $layoutName);

		$zip->save();

		if ($todir) {
			copy($zipfilename, $todir);
		}

		if ($directDownload) {
			$zip->forceDownload($zipfilename);
			unlink($zipfilename);
		}
		$this->__cleanupExport();
	}

	/**
	 * Export Layout Handler
	 * @access private
	 */
	public function export_Layout($layoutName)
	{
		$adb = \PearDatabase::getInstance();
		$query = sprintf('SELECT * FROM %s WHERE name = ?', self::TABLENAME);
		$sqlresult = $adb->pquery($query, [$layoutName]);
		$layoutresultrow = $adb->fetch_array($sqlresult);

		$layoutname = decode_html($layoutresultrow['name']);
		$layoutlabel = decode_html($layoutresultrow['label']);

		$this->openNode('module');
		$this->outputNode(date('Y-m-d H:i:s'), 'exporttime');
		$this->outputNode($layoutname, 'name');
		$this->outputNode($layoutlabel, 'label');
		$this->outputNode('layout', 'type');

		// Export dependency information
		$this->export_Dependencies();
		$this->closeNode('module');
	}

	/**
	 * Export vtiger dependencies
	 * @access private
	 */
	public function export_Dependencies()
	{
		$maxVersion = false;
		$this->openNode('dependencies');
		$this->outputNode(\App\Version::get(), 'vtiger_version');
		if ($maxVersion !== false)
			$this->outputNode($maxVersion, 'vtiger_max_version');
		$this->closeNode('dependencies');
	}

	/**
	 * Register layout pack information.
	 */
	static function register($name, $label = '', $isdefault = false, $isactive = true, $overrideCore = false)
	{
		$prefix = trim($prefix);
		// We will not allow registering core layouts unless forced
		if (strtolower($name) == 'basic' && $overrideCore === false)
			return;

		$useisdefault = ($isdefault) ? 1 : 0;
		$useisactive = ($isactive) ? 1 : 0;

		$adb = \PearDatabase::getInstance();
		$query = sprintf('SELECT id FROM %s WHERE name = ?', self::TABLENAME);
		$checkres = $adb->pquery($query, [$name]);
		$datetime = date('Y-m-d H:i:s');
		if ($checkres->rowCount()) {
			$adb->update(self::TABLENAME, [
				'label' => $label,
				'name' => $name,
				'lastupdated' => $datetime,
				'isdefault' => $useisdefault,
				'active' => $useisactive,
				], 'id=?', [$adb->getSingleValue($checkres)]
			);
		} else {
			$adb->insert(self::TABLENAME, [
				'id' => self::__getUniqueId(),
				'name' => $name,
				'label' => $label,
				'lastupdated' => $datetime,
				'isdefault' => $useisdefault,
				'active' => $useisactive,
			]);
		}
		self::log("Registering Layout $name ... DONE");
	}

	static function deregister($name)
	{
		if (strtolower($name) == 'basic')
			return;

		$adb = \PearDatabase::getInstance();
		$adb->delete(self::TABLENAME, 'name = ?', [$name]);
		Functions::recurseDelete('layouts' . DIRECTORY_SEPARATOR . $name);
		self::log("Deregistering Layout $name ... DONE");
	}
}
