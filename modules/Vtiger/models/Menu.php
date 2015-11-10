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
		$breadcrumbsOn = $purl = false;
		$moduleName = $request->getModule();
		$view = $request->get('view');

		if ($request->get('parent') != '' && $request->get('parent') != 'Settings') {
			$parentMenu = self::getParentMenu($parentList, $request->get('parent'), $moduleName);
			if (count($parentMenu) > 0) {
				$breadcrumbs = array_reverse($parentMenu);
			}
			$breadcrumbs[] = [ 'name' => vtranslate($moduleName, $moduleName)];
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
		} elseif ($request->get('parent') == 'Settings') {
			$qualifiedModuleName = $request->getModule(false);
			$breadcrumbs[] = [
				'name' => vtranslate('LBL_VIEW_SETTINGS', $qualifiedModuleName),
				'url' => 'index.php?module=Vtiger&parent=Settings&view=Index',
			];
			$selectedMenuId = $request->get('block');
			$fieldId = $request->get('fieldid');
			$menu = Settings_Vtiger_MenuItem_Model::getAll($this);
			foreach ($menu as &$menuModel) {
				if ($fieldId == $menuModel->getId()) {
					$parent = $menuModel->getMenu();
					$breadcrumbs[] = [ 'name' => vtranslate($parent->get('label'), $qualifiedModuleName)];
					$breadcrumbs[] = [ 'name' => vtranslate($menuModel->get('name'), $qualifiedModuleName)];
				}
			}
			if ($moduleName == 'Users' && count($breadcrumbs) == 1) {
				$breadcrumbs[] = [ 'name' => vtranslate('LBL_USER_MANAGEMENT', $qualifiedModuleName)];
				$breadcrumbs[] = [ 
					'name' => vtranslate('LBL_USERS', $qualifiedModuleName),
					'url' => 'index.php?module=Users&parent=Settings&view=List',
				];
				if ($view == 'Edit' && $request->get('record') == '') {
					$breadcrumbs[] = [ 'name' => vtranslate('LBL_VIEW_CREATE', $moduleName)];
				} elseif ($view != '' && $view != 'List') {
					$breadcrumbs[] = [ 'name' => vtranslate('LBL_VIEW_' . strtoupper($view), $moduleName)];
				}
				if ($request->get('record') != '') {
					$recordLabel = Vtiger_Functions::getUserRecordLabel($request->get('record'));
					if ($recordLabel != '') {
						$breadcrumbs[] = [ 'name' => $recordLabel];
					}
				}
			}
		}
		return $breadcrumbs;
	}

	public static function getParentMenu($parentList, $parent, $module, $return = [])
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

	/**
	 * 
	 * @param type $url
	 * @return type modulename 
	 */
	public static function getModuleNameFromUrl($url)
	{
		$query_str = parse_url(htmlspecialchars_decode($url), PHP_URL_QUERY);
		parse_str($query_str, $query_params);

		if ($query_params[parent]) {
			return ("$query_params[parent]:$query_params[module]");
		}

		return $query_params[module];
	}

	public static function getMenuIcon($menu, $title = '')
	{
		if ($title == '') {
			$title = Vtiger_Menu_Model::vtranslateMenu($menu['label']);
		}
		if (is_string($menu)) {
			$iconName = vimage_path($menu);
			if (file_exists($iconName)) {
				return '<img src="' . $iconName . '" alt="' . $title . '" title="' . $title . '" class="menuIcon" />';
			}
		}

		if (!empty($menu['icon'])) {
			if (strpos($menu['icon'], 'adminIcon-') !== false || strpos($menu['icon'], 'userIcon-') !== false) {
				return '<span class="menuIcon ' . $menu['icon'] . '" aria-hidden="true"></span>';
			}

			$icon = vimage_path($menu['icon']);
			if (file_exists($icon)) {
				return '<img src="' . $icon . '" alt="' . $title . '" title="' . $title . '" class="menuIcon" />';
			}
		}
		if ($menu['type'] == 'Module') {
			$iconName = vimage_path($menu['name'] . '.png');

			if (file_exists($iconName)) {
				return '<img src="' . $iconName . '" alt="' . $title . '" title="' . $title . '" class="menuIcon" />';
			}
		}
		return '';
	}
}
