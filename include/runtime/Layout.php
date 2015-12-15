<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Yeti_Layout
{

	public static function getActiveLayout()
	{
		$layout = Vtiger_Session::get('layout');
		if (!empty($layout)) {
			return $layout;
		}
		return AppConfig::main('defaultLayout');
	}

	public static function getLayoutFile($name)
	{
		$basePath = 'layouts' . '/' . AppConfig::main('defaultLayout') . '/';
		$filePath = Vtiger_Loader::resolveNameToPath('~' . $basePath . $name);
		if (is_file($filePath)) {
			return $basePath . $name;
		}
		$basePath = 'layouts' . '/' . Vtiger_Viewer::getDefaultLayoutName() . '/';
		return $basePath . $name;
	}

	public static function getAllLayouts()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT name,label FROM vtiger_layout');
		$folders = [
			'basic' => vtranslate('LBL_DEFAULT')
		];
		while ($row = $db->fetch_array($result)) {
			$folders[$row['name']] = vtranslate($row['label']);
		}
		return $folders;
	}
}
