<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once('vtlib/Vtiger/Package.php');

/**
 * Provides API to package vtiger CRM layout files.
 * @package vtlib
 */
class Vtiger_LayoutExport extends Vtiger_Package
{

	const TABLENAME = 'vtiger_layout';

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Generate unique id for insertion
	 * @access private
	 */
	static function __getUniqueId()
	{
		$adb = PearDatabase::getInstance();
		return $adb->getUniqueID(self::TABLENAME);
	}

	/**
	 * Initialize Export
	 * @access private
	 */
	function __initExport($layoutName)
	{
		// Security check to ensure file is withing the web folder.
		Vtiger_Utils::checkFileAccessForInclusion("layouts/$layoutName/skins/vtiger/style.less");

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
	function export($layoutName, $todir = '', $zipfilename = '', $directDownload = false)
	{
		$this->__initExport($layoutName);

		// Call layout export function
		$this->export_Layout($layoutName);

		$this->__finishExport();

		// Export as Zip
		if ($zipfilename == '')
			$zipfilename = "$layoutName-" . date('YmdHis') . ".zip";
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = new Vtiger_Zip($zipfilename);

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
	function export_Layout($layoutName)
	{
		$adb = PearDatabase::getInstance();

		$sqlresult = $adb->pquery('SELECT * FROM ' . self::TABLENAME . ' WHERE name = ?', [$layoutName]);
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
	function export_Dependencies()
	{
		$vtigerMinVersion = vglobal('YetiForce_current_version');
		$vtigerMaxVersion = false;

		$this->openNode('dependencies');
		$this->outputNode($vtigerMinVersion, 'vtiger_version');
		if ($vtigerMaxVersion !== false)
			$this->outputNode($vtigerMaxVersion, 'vtiger_max_version');
		$this->closeNode('dependencies');
	}

	/**
	 * Register layout pack information.
	 */
	static function register($name, $label = '', $isdefault = false, $isactive = true, $overrideCore = false)
	{
		$prefix = trim($prefix);
		// We will not allow registering core layouts unless forced
		if (strtolower($name) == 'vlayout' && $overrideCore == false)
			return;

		$useisdefault = ($isdefault) ? 1 : 0;
		$useisactive = ($isactive) ? 1 : 0;

		$adb = PearDatabase::getInstance();
		$checkres = $adb->pquery('SELECT id FROM ' . self::TABLENAME . ' WHERE name=?', [$name]);
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
		if (strtolower($name) == 'vlayout')
			return;

		$adb = PearDatabase::getInstance();
		$adb->delete(self::TABLENAME, 'name = ?', [$name]);
		Vtiger_Functions::recurseDelete('layouts' . DIRECTORY_SEPARATOR . $name);
		self::log("Deregistering Layout $name ... DONE");
	}
}
