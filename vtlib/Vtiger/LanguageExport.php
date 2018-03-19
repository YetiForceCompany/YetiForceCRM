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
 * Provides API to package vtiger CRM language files.
 */
class LanguageExport extends Package
{
	const TABLENAME = 'vtiger_language';

	/**
	 * Generate unique id for insertion.
	 */
	public static function __getUniqueId()
	{
		$adb = \PearDatabase::getInstance();

		return $adb->getUniqueID(self::TABLENAME);
	}

	/**
	 * Initialize Export.
	 */
	public function __initExport($languageCode, $moduleInstance = null)
	{
		// Security check to ensure file is withing the web folder.
		Utils::checkFileAccessForInclusion("languages/$languageCode/_Base.json");
		$this->_export_modulexml_file = fopen($this->__getManifestFilePath(), 'w');
		$this->__write("<?xml version='1.0'?>\n");
	}

	/**
	 * Export Module as a zip file.
	 *
	 * @param Module Instance of module
	 * @param Path Output directory path
	 * @param string Zipfilename to use
	 * @param bool True for sending the output as download
	 */
	public function exportLanguage($languageCode, $todir = '', $zipfilename = '', $directDownload = false)
	{
		$this->__initExport($languageCode);
		// Call language export function
		$this->generateLangMainfest($languageCode);
		$this->__finishExport();
		// Export as Zip
		if ($zipfilename === '') {
			$zipfilename = "$languageCode-" . date('YmdHis') . '.zip';
		}
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = \App\Zip::createFile($zipfilename);
		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');
		// Copy module directory
		$zip->addDirectory("languages/$languageCode", 'modules');
		if ($directDownload) {
			$zip->download();
		} else {
			$zip->close();
			if ($todir) {
				copy($zipfilename, $todir);
			}
		}
		$this->__cleanupExport();
	}

	/**
	 * Export Language Handler.
	 */
	private function generateLangMainfest($prefix)
	{
		$db = \PearDatabase::getInstance();
		$sqlresult = $db->pquery('SELECT * FROM vtiger_language WHERE prefix = ?', [$prefix]);
		$languageresultrow = $db->fetchArray($sqlresult);
		$langname = \App\Purifier::decodeHtml($languageresultrow['name']);
		$langlabel = \App\Purifier::decodeHtml($languageresultrow['label']);
		$this->openNode('module');
		$this->outputNode('language', 'type');
		$this->outputNode($langname, 'name');
		$this->outputNode($langlabel, 'label');
		$this->outputNode($prefix, 'prefix');
		$this->outputNode('language', 'type');
		$this->outputNode(\AppConfig::main('default_charset'), 'encoding');
		$this->outputNode('YetiForce - yetiforce.com', 'author');
		$this->outputNode('YetiForce - yetiforce.com', 'license');
		// Export dependency information
		$minVersion = current(explode('.', \App\Version::get())) . '.*';
		$this->openNode('dependencies');
		if ($minVersion !== false) {
			$this->outputNode($minVersion, 'vtiger_version');
		}
		$this->closeNode('dependencies');
		$this->closeNode('module');
	}

	/**
	 * Register language pack information.
	 */
	public static function register($prefix, $label, $name = '', $isdefault = false, $isactive = true, $overrideCore = false)
	{
		$prefix = trim($prefix);
		// We will not allow registering core language unless forced
		if (strtolower($prefix) == 'en_us' && $overrideCore === false) {
			return;
		}

		$useisdefault = ($isdefault) ? 1 : 0;
		$useisactive = ($isactive) ? 1 : 0;

		$adb = \PearDatabase::getInstance();
		$checkres = $adb->pquery(sprintf('SELECT id FROM %s WHERE prefix = ?', self::TABLENAME), [$prefix]);
		$datetime = date('Y-m-d H:i:s');
		if ($adb->numRows($checkres)) {
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
				'prefix' => $prefix,
				'label' => $label,
				'lastupdated' => $datetime,
				'isdefault' => $useisdefault,
				'active' => $useisactive,
			]);
		}
		\App\Log::trace("Registering Language $label [$prefix] ... DONE", __METHOD__);
	}

	/**
	 * De-Register language pack information.
	 *
	 * @param string Language prefix like (de_de) etc
	 */
	public static function deregister($prefix)
	{
		$prefix = trim($prefix);
		// We will not allow deregistering core language
		if (strtolower($prefix) == 'en_us') {
			return;
		}

		$adb = \PearDatabase::getInstance();
		$adb->delete(self::TABLENAME, 'prefix=?', [$prefix]);
		\App\Log::trace("Deregistering Language $prefix ... DONE", __METHOD__);
	}

	/**
	 * Get all the language information.
	 *
	 * @param bool true to include in-active languages also, false (default)
	 */
	public static function getAll($includeInActive = false)
	{
		$query = (new \App\Db\Query())->from(self::TABLENAME)->select('prefix,label');
		if (!$includeInActive) {
			$query->where(['active' => 1]);
		}
		$languages = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$languages[$row['prefix']] = $row['label'];
		}
		asort($languages);

		return $languages;
	}
}
