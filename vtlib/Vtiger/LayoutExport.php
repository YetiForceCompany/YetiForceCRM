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
 */
class LayoutExport extends Package
{
	const TABLENAME = 'vtiger_layout';

	/**
	 * Initialize Export.
	 *
	 * @param string $layoutName
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
	 *
	 * @param string $layoutName
	 * @param string $todir
	 * @param string $zipfilename
	 * @param bool   $directDownload
	 */
	public function export($layoutName, $todir = '', $zipfilename = '', $directDownload = false)
	{
		$this->__initExport($layoutName);

		// Call layout export function
		$this->exportLayout($layoutName);

		$this->__finishExport();

		// Export as Zip
		if ('' === $zipfilename) {
			$zipfilename = "$layoutName-" . date('YmdHis') . '.zip';
		}
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = \App\Zip::createFile($zipfilename);
		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');
		// Copy module directory
		$zip->addDirectory('layouts/' . $layoutName);
		if ($directDownload) {
			$zip->download($layoutName);
		} else {
			$zip->close();
			if ($todir) {
				copy($zipfilename, $todir);
			}
		}
		$this->__cleanupExport();
	}

	/**
	 * Export Layout Handler.
	 *
	 * @param string $layoutName
	 */
	public function exportLayout($layoutName)
	{
		$layoutresultrow = (new \App\Db\Query())->from(self::TABLENAME)->where(['name' => $layoutName])->one();

		$layoutname = \App\Purifier::decodeHtml($layoutresultrow['name']);
		$layoutlabel = \App\Purifier::decodeHtml($layoutresultrow['label']);

		$this->openNode('module');
		$this->outputNode(date('Y-m-d H:i:s'), 'exporttime');
		$this->outputNode($layoutname, 'name');
		$this->outputNode($layoutlabel, 'label');
		$this->outputNode('layout', 'type');

		// Export dependency information
		$this->exportLayoutDependencies();
		$this->closeNode('module');
	}

	/**
	 * Export vtiger dependencies.
	 */
	public function exportLayoutDependencies()
	{
		$maxVersion = false;
		$this->openNode('dependencies');
		$this->outputNode(\App\Version::get(), 'vtiger_version');
		if (false !== $maxVersion) {
			$this->outputNode($maxVersion, 'vtiger_max_version');
		}
		$this->closeNode('dependencies');
	}

	/**
	 * Register layout pack information.
	 *
	 * @param string $name
	 * @param string $label
	 * @param bool   $isdefault
	 * @param bool   $isactive
	 * @param bool   $overrideCore
	 */
	public static function register($name, $label = '', $isdefault = false, $isactive = true, $overrideCore = false)
	{
		$prefix = trim($prefix);
		// We will not allow registering core layouts unless forced
		if ('basic' == strtolower($name) && false === $overrideCore) {
			return;
		}

		$resId = (new \App\Db\Query())->select(['id'])->from(self::TABLENAME)->where(['name' => $name])->scalar();
		$db = \App\Db::getInstance()->createCommand();
		$params = [
			'label' => $label,
			'name' => $name,
			'lastupdated' => date('Y-m-d H:i:s'),
			'isdefault' => ($isdefault) ? 1 : 0,
			'active' => ($isactive) ? 1 : 0,
		];
		if ($resId) {
			$db->update(self::TABLENAME, $params, ['id' => $resId])->execute();
		} else {
			$params['id'] = \App\Db::getInstance()->getUniqueID(self::TABLENAME, 'id', false);
			$db->insert(self::TABLENAME, $params)->execute();
		}
		\App\Log::trace("Registering Layout $name ... DONE", __METHOD__);
	}

	public static function deregister($name)
	{
		if ('basic' == strtolower($name)) {
			return;
		}

		\App\Db::getInstance()->createCommand()->delete(self::TABLENAME, ['name' => $name])->execute();
		Functions::recurseDelete('layouts' . \DIRECTORY_SEPARATOR . $name);
		\App\Log::trace("Deregistering Layout $name ... DONE", __METHOD__);
	}
}
