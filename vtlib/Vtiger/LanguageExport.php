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
		if ('' === $zipfilename) {
			$zipfilename = "$languageCode-" . date('YmdHis') . '.zip';
		}
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = \App\Zip::createFile($zipfilename);
		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');
		// Copy module directory
		foreach (['languages', 'custom' . \DIRECTORY_SEPARATOR . 'languages'] as $dir) {
			$path = $dir . \DIRECTORY_SEPARATOR . $languageCode;
			if (file_exists($path)) {
				$zip->addDirectory($path);
			}
		}
		if ($directDownload) {
			$zip->download($languageCode);
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
	 *
	 * @param string $prefix
	 */
	private function generateLangMainfest($prefix)
	{
		$langInfo = \App\Language::getLangInfo($prefix);
		$this->openNode('module');
		$this->outputNode('language', 'type');
		$this->outputNode(\App\Purifier::decodeHtml($langInfo['name']), 'name');
		$this->outputNode($prefix, 'prefix');
		$this->outputNode('language', 'type');
		$this->outputNode(\AppConfig::main('default_charset'), 'encoding');
		$this->outputNode('YetiForce - yetiforce.com', 'author');
		$this->outputNode('YetiForce - yetiforce.com', 'license');
		// Export dependency information
		$this->openNode('dependencies');
		$this->outputNode(\App\Version::get(), 'vtiger_version');
		$this->closeNode('dependencies');
		$this->closeNode('module');
	}

	/**
	 * Register language pack information.
	 *
	 * @param string $prefix
	 * @param string $name
	 * @param bool $isDefault
	 * @param bool $isActive
	 * @param int $progress
	 *
	 * @throws \yii\db\Exception
	 */
	public static function register(string $prefix, string $name = '', bool $isDefault = false, bool $isActive = true, int $progress = 0)
	{
		$prefix = trim($prefix);
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ((new \App\Db\Query())->from(static::TABLENAME)->where(['prefix' => $prefix])->exists()) {
			$dbCommand->update(
				static::TABLENAME,
				[
					'name' => $name,
					'lastupdated' => date('Y-m-d H:i:s'),
					'isdefault' => (int) $isDefault,
					'active' => (int) $isActive,
					'progress' => $progress
				],
				['prefix' => $prefix]
			)->execute();
		} else {
			$dbCommand->insert(
				static::TABLENAME,
				[
					'name' => $name,
					'lastupdated' => date('Y-m-d H:i:s'),
					'isdefault' => (int) $isDefault,
					'active' => (int) $isActive,
					'prefix' => $prefix,
					'progress' => $progress
				]
			)->execute();
		}
		\App\Log::trace("Registering Language $name [$prefix] ... DONE", __METHOD__);
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
		if (strtolower($prefix) === strtolower(\App\Language::DEFAULT_LANG)) {
			return;
		}

		\App\Db::getInstance()->createCommand()->delete(self::TABLENAME, ['prefix' => $prefix])->execute();
		\App\Log::trace("Deregistering Language $prefix ... DONE", __METHOD__);
	}

	/**
	 * Get all the language information.
	 *
	 * @param bool true to include in-active languages also, false (default)
	 */
	public static function getAll($includeInActive = false)
	{
		$query = (new \App\Db\Query())->from(self::TABLENAME)->select(['prefix', 'name']);
		if (!$includeInActive) {
			$query->where(['active' => 1]);
		}
		$languages = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$languages[$row['prefix']] = $row['name'];
		}
		asort($languages);

		return $languages;
	}
}
