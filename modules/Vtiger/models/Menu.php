<?php

/**
 * Vtiger menu model class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Menu_Model
{
	/**
	 * Static Function to get all the accessible menu models with/without ordering them by sequence.
	 *
	 * @return array
	 */
	public static function getAll(): array
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$roleMenu = 'user_privileges/menu_' . filter_var($userPrivModel->get('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
		if (file_exists($roleMenu)) {
			require $roleMenu;
		} else {
			require 'user_privileges/menu_0.php';
		}
		if (0 === \count($menus)) {
			require 'user_privileges/menu_0.php';
		}
		return \Settings_Menu_Record_Model::parseToDisplay($menus);
	}

	public static function vtranslateMenu($key, $module)
	{
		$string = \App\Language::translateSingleMod($key, 'Other.Menu');
		if ($string !== $key) {
			return $string;
		}
		return \App\Language::translate($key, $module);
	}

	public static function getBreadcrumbs($pageTitle = false)
	{
		$breadcrumbs = [];
		$request = App\Request::init();
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$roleMenu = 'user_privileges/menu_' . filter_var($userPrivModel->get('roleid'), FILTER_SANITIZE_NUMBER_INT) . '.php';
		if (file_exists($roleMenu)) {
			require $roleMenu;
		} else {
			require 'user_privileges/menu_0.php';
		}
		if (empty($menus)) {
			require 'user_privileges/menu_0.php';
		}
		$moduleName = $request->getModule();
		$view = $request->getByType('view', 'Alnum');
		$parent = $request->getByType('parent', 'Alnum');
		$mid = $request->isEmpty('mid', 'Alnum') ? null : $request->getInteger('mid');
		if ('Settings' !== $parent) {
			$parent = (!$parent && $mid) ? ($parentList[$mid]['parent'] ?? null) : $parent;
			if (empty($parent)) {
				foreach ($parentList as &$parentItem) {
					if ($moduleName === $parentItem['mod']) {
						$parent = $parentItem['parent'];
						break;
					}
				}
			}
			$parentMenu = self::getParentMenu($parentList, $parent, $moduleName);
			if (\count($parentMenu) > 0) {
				$breadcrumbs = array_reverse($parentMenu);
			}
			if ('AppComponents' !== $moduleName) {
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
				if ($moduleModel && $moduleModel->getDefaultUrl()) {
					if ($mid) {
						$url = $menus[$mid]['dataurl'] ?? $parentList[$mid]['dataurl'] ?? $moduleModel->getDefaultUrl();
					} else {
						$url = $moduleModel->getDefaultUrl();
					}
					$breadcrumbs[] = [
						'name' => \App\Language::translate($moduleName, $moduleName),
						'url' => $url,
					];
				} else {
					$breadcrumbs[] = [
						'name' => \App\Language::translate($moduleName, $moduleName),
					];
				}
			}
			if ($pageTitle) {
				$breadcrumbs[] = ['name' => $pageTitle];
			} elseif ('Edit' === $view && $request->isEmpty('record')) {
				$breadcrumbs[] = ['name' => App\Language::translate('LBL_VIEW_CREATE', $moduleName)];
			} elseif (!empty($view) && 'index' !== $view && 'Index' !== $view) {
				$breadcrumbs[] = ['name' => App\Language::translate('LBL_VIEW_' . strtoupper($view), $moduleName)];
			} elseif (empty($view)) {
				$breadcrumbs[] = ['name' => App\Language::translate('LBL_HOME', $moduleName)];
			}
			if ($moduleModel && !$request->isEmpty('record', true) && $moduleModel->isEntityModule()) {
				$recordLabel = vtlib\Functions::getCRMRecordLabel($request->getInteger('record'));
				if (!empty($recordLabel)) {
					$breadcrumbs[] = ['name' => $recordLabel];
				}
			}
		} elseif ('Settings' === $parent) {
			$qualifiedModuleName = $request->getModule(false);
			$breadcrumbs[] = [
				'name' => \App\Language::translate('LBL_VIEW_SETTINGS', $qualifiedModuleName),
				'url' => 'index.php?module=Vtiger&parent=Settings&view=Index',
				'icon' => 'fas fa-cog fa-fw',
			];
			$menu = Settings_Vtiger_MenuItem_Model::getAll();
			foreach ($menu as $menuModel) {
				if ($menuModel->isPermitted() && (
						(($request->has('record') || 'Edit' === $view) && $menuModel->getModuleName() === $qualifiedModuleName)
						|| $menuModel->isSelected($moduleName, $view, $request->getMode())
					)
				) {
					$parent = $menuModel->getBlock();
					$breadcrumbs[] = [
						'name' => App\Language::translate($parent->getLabel(), $qualifiedModuleName),
						'icon' => $parent->get('icon'),
					];
					$breadcrumbs[] = [
						'name' => App\Language::translate($menuModel->get('name'), $qualifiedModuleName),
						'url' => $menuModel->getUrl(),
						'icon' => $menuModel->get('iconpath'),
					];
					break;
				}
			}
			if (\is_array($pageTitle)) {
				foreach ($pageTitle as $title) {
					$breadcrumbs[] = $title;
				}
			} else {
				if ($pageTitle) {
					$breadcrumbs[] = ['name' => App\Language::translate($pageTitle, $qualifiedModuleName)];
				} elseif ('Edit' === $view && $request->isEmpty('record') && $request->isEmpty('parent_roleid')) {
					$breadcrumbs[] = ['name' => App\Language::translate('LBL_VIEW_CREATE', $qualifiedModuleName)];
				}
				if (!$request->isEmpty('record') && 'Users' === $moduleName) {
					$recordLabel = \App\Fields\Owner::getUserLabel($request->getInteger('record'));
					if (!empty($recordLabel)) {
						$breadcrumbs[] = ['name' => $recordLabel];
					}
				}
			}
		}
		return $breadcrumbs;
	}

	public static function getParentMenu($parentList, $parent, $module, $return = []): array
	{
		$return = [];
		if (!empty($parent) && !empty($parentList[$parent])) {
			$return[] = [
				'name' => self::getLabelToDisplay($parentList[$parent]),
				'url' => $parentList[$parent]['dataurl'],
			];
			if (0 !== $parentList[$parent]['parent'] && \array_key_exists($parentList[$parent]['parent'], $parentList)) {
				$return = self::getParentMenu($parentList, $parentList[$parent]['parent'], $module, $return);
			}
		}
		return $return;
	}

	/**
	 * Function to get icon of element in menu.
	 *
	 * @param string|array $menu
	 * @param string       $title
	 *
	 * @return string
	 */
	public static function getMenuIcon($menu, $title = ''): string
	{
		if (empty($title) && !empty($menu['label'])) {
			$title = self::getLabelToDisplay($menu);
		}
		if (\is_string($menu)) {
			$iconName = \Vtiger_Theme::getImagePath($menu);
			if (file_exists($iconName)) {
				return '<img src="' . $iconName . '" alt="' . $title . '" title="' . $title . '" class="c-menu__item__icon" />';
			}
		}
		if (!empty($menu['icon'])) {
			if (false !== strpos($menu['icon'], 'fa-')) {
				return '<span class="' . $menu['icon'] . ' c-menu__item__icon"></span>';
			}
			if (false !== strpos($menu['icon'], 'adminIcon-') || false !== strpos($menu['icon'], 'AdditionalIcon-') || false !== strpos($menu['icon'], 'yfi-') || false !== strpos($menu['icon'], 'yfm-') || false !== strpos($menu['icon'], 'mdi-')) {
				return '<span class="c-menu__item__icon ' . $menu['icon'] . '" aria-hidden="true"></span>';
			}
			$icon = \Vtiger_Theme::getImagePath($menu['icon']);
			if ($icon) {
				return '<img src="' . $icon . '" alt="' . $title . '" title="' . $title . '" class="c-menu__item__icon" />';
			}
		}
		if (isset($menu['type']) && 'Module' === $menu['type']) {
			return '<span class="c-menu__item__icon yfm-' . $menu['mod'] . '" aria-hidden="true"></span>';
		}
		return '';
	}

	/**
	 * Get label to display.
	 *
	 * @param array $row
	 *
	 * @return string
	 */
	public static function getLabelToDisplay(array $row): string
	{
		$name = '';
		$type = $row['type'];
		if (\is_int($type)) {
			$type = \App\Menu::TYPES[$type];
			$moduleName = $row['name'];
		} else {
			$moduleName = $row['mod'] ?? '';
		}
		switch ($type) {
			case 'Module':
				$name = self::vtranslateMenu((empty($row['label']) ? $moduleName : $row['label']), $moduleName);
				break;
			case 'Separator':
				$name = self::vtranslateMenu('LBL_SEPARATOR', 'Menu');
				break;
			case 'QuickCreate':
				if ('' != $row['label']) {
					$name = self::vtranslateMenu($row['label'], 'Menu');
				} else {
					$name = \App\Language::translate('LBL_QUICK_CREATE_MODULE', 'Menu') . ': ' . self::vtranslateMenu('SINGLE_' . $moduleName, $moduleName);
				}
				break;
			case 'HomeIcon':
				$name = self::vtranslateMenu('LBL_HOME', 'Menu');
				break;
			case 'CustomFilter':
				$cvid = \is_int($row['type']) ? $row['dataurl'] : vtlib\Functions::getQueryParams($row['dataurl'])['viewname'];
				$data = \App\CustomView::getCustomViewById($cvid);
				$name = self::vtranslateMenu($data['entitytype'], $data['entitytype']) . ': ' . \App\Language::translate($data['viewname'], $data['entitytype']);
				break;
			case 'RecycleBin':
				$name = self::vtranslateMenu($moduleName, $moduleName);
				break;
			default:
				$name = self::vtranslateMenu($row['label'], 'Menu');
				break;
		}
		return $name;
	}
}
