<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Vtiger_Menu_Model
{

	/**
	 * Static Function to get all the accessible menu models with/without ordering them by sequence
	 * @param <Boolean> $sequenced - true/false
	 * @return <Array> - List of Vtiger_Menu_Model instances
	 */
	public static function getAll($sequenced = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$roleMenu = 'user_privileges/menu_' . filter_var($userPrivModel->get('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
		if (file_exists($roleMenu)) {
			require($roleMenu);
		} else {
			require('user_privileges/menu_0.php');
		}
		if (count($menus) == 0) {
			require('user_privileges/menu_0.php');
		}
		return $menus;
	}

	public static function vtranslateMenu($key, $module)
	{
		$language = Vtiger_Language_Handler::getLanguage();
		$moduleStrings = Vtiger_Language_Handler::getModuleStringsFromFile($language, 'Menu');
		if (array_key_exists($key, $moduleStrings['languageStrings'])) {
			return stripslashes($moduleStrings['languageStrings'][$key]);
		}
		return vtranslate($key, $module);
	}

	public static function getBreadcrumbs()
	{
		$breadcrumbs = false;
		$request = new Vtiger_Request($_REQUEST, $_REQUEST);

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$roleMenu = 'user_privileges/menu_' . filter_var($userPrivModel->get('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
		if (file_exists($roleMenu)) {
			require($roleMenu);
		} else {
			require('user_privileges/menu_0.php');
		}
		if (count($menus) == 0) {
			require('user_privileges/menu_0.php');
		}
		if ($request->get('parent') == 'Settings') {
			$moduleName = 'Settings:';
		}
		$breadcrumbsOn = $purl = false;
		$moduleName .= $module = $request->get('module');
		$view = $request->get('view');

		if ($request->get('parent') != '' && $request->get('parent') != 'Settings') {
			$parentMenu = self::getParentMenu($parentList, $request->get('parent'), $module);
			if (count($parentMenu) > 0) {
				$breadcrumbs = array_reverse($parentMenu);
			}
		} elseif ($request->get('parent') == 'Settings') {
			$breadcrumbs[] = [ 'name' => vtranslate('LBL_VIEW_SETTINGS', $moduleName)];
		}
		$breadcrumbs[] = [ 'name' => vtranslate($module, $moduleName)];
		if ($view == 'Edit' && $request->get('record') == '') {
			$breadcrumbs[] = [ 'name' => vtranslate('LBL_VIEW_CREATE', $moduleName)];
		} elseif ($view != '' && $view != 'index' && $view != 'Index') {
			$breadcrumbs[] = [ 'name' => vtranslate('LBL_VIEW_' . strtoupper($view), $moduleName)];
		} elseif ($view == '') {
			$breadcrumbs[] = [ 'name' => vtranslate('LBL_HOME', $moduleName)];
		}
		if ($request->get('record') != '') {
			$recordLabel = Vtiger_Functions::getCRMRecordLabel($request->get('record'));
			if ($recordLabel != '') {
				$breadcrumbs[] = [ 'name' => $recordLabel];
			}
		}
		return $breadcrumbs;
	}

	public function getParentMenu($parentList, $parent, $module, $return = [])
	{
		if ($parent != 0 && key_exists($parent, $parentList)) {
			$return [] = [
				'name' => self::vtranslateMenu($parentList[$parent]['name'], $module),
				'url' => $parentList[$parent]['url'],
			];
			if ($parentList[$parent]['parent'] != 0 && key_exists($parentList[$parent]['parent'], $parentList)) {
				$return = self::getParentMenu($parentList, $parentList[$parent]['parent'], $module, $return);
			}
		}
		return $return;
	}
}
