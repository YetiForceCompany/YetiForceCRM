<?php

/**
 * Settings menu record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Menu for Roles.
	 */
	public const SRC_ROLE = 0;
	/**
	 * Menu for Api.
	 */
	public const SRC_API = 1;

	/**
	 * Function to get Id of this record instance.
	 *
	 * @return <Integer> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance.
	 *
	 * @return string Name
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Returns all items for menus.
	 *
	 * @param int $roleId
	 * @param int $source
	 *
	 * @return array
	 */
	public function getAll(int $roleId, int $source): array
	{
		$settingsModel = Settings_Menu_Module_Model::getInstance();
		$query = (new \App\Db\Query())->select(['yetiforce_menu.*', 'vtiger_tab.name'])->from('yetiforce_menu')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = yetiforce_menu.module')->where(['role' => $roleId, 'source' => $source])->orderBy('yetiforce_menu.sequence, yetiforce_menu.parentid');
		$dataReader = $query->createCommand()->query();
		$menu = [];
		while ($row = $dataReader->read()) {
			$row['type'] = (int) $row['type'];
			$menu[] = [
				'id' => $row['id'],
				'parent' => 0 == $row['parentid'] ? '#' : $row['parentid'],
				'text' => Vtiger_Menu_Model::getLabelToDisplay($row),
				'icon' => 'menu-icon-' . $settingsModel->getMenuTypes($row['type']),
			];
		}
		$dataReader->close();

		return $menu;
	}

	public static function getCleanInstance()
	{
		return new self();
	}

	public static function getInstanceById($id)
	{
		$query = (new \App\Db\Query())->from('yetiforce_menu')->where(['id' => $id]);
		$row = $query->one();
		if (false === $row) {
			return false;
		}
		$instance = new self();
		$instance->setData($row);

		return $instance;
	}

	public function initialize($data)
	{
		$this->setData($data);
	}

	public function save()
	{
		$db = \App\Db::getInstance();
		$settingsModel = Settings_Menu_Module_Model::getInstance();
		$edit = $this->get('edit');
		$params = [];
		$sqlCol = '';
		$role = 0;
		$editFields = $settingsModel->getEditFields();
		if ($edit) {
			$data = $this->getData();
			foreach ($data as $key => $item) {
				if (!\in_array($key, $editFields)) {
					throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $key, 406);
				}
				if (\is_array($item)) {
					$item = implode(',', $item);
				}
				if ('id' != $key && 'edit' != $key) {
					$params[$key] = $item;
				}
			}
			if (!isset($data['newwindow'])) {
				$params['newwindow'] = 0;
			}
			if (!isset($data['countentries'])) {
				$params['countentries'] = 0;
			}
			if (!isset($data['filters'])) {
				$params['filters'] = '';
			}
			$db->createCommand()->update('yetiforce_menu', $params, ['id' => $this->getId()])->execute();
		} else {
			foreach ($this->getData() as $key => $item) {
				if (!\in_array($key, $editFields)) {
					throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $key, 406);
				}
				if (\is_array($item)) {
					$item = implode(',', $item);
				}
				$sqlCol .= $key . ',';
				switch ($key) {
					case 'role':
						$role = $item = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
						break;
					case 'type':
						$item = $settingsModel->getMenuTypeKey($item);
						break;
					default:
						break;
				}
				$params[] = $item;
			}
			$maxSequence = (new \App\Db\Query())->from('yetiforce_menu')->where(['role' => $role, 'parentid' => 0])->max('sequence');
			$max = (int) $maxSequence;
			$sqlCol .= 'sequence';
			$params[] = $max + 1;
			$sqlCol = explode(',', $sqlCol);
			foreach ($sqlCol as $key => $value) {
				$insertParams[$value] = $params[$key];
			}
			$db->createCommand()->insert('yetiforce_menu', $insertParams)->execute();
		}
		if (self::SRC_ROLE === $this->get('source')) {
			$this->generateFileMenu($this->get('role'));
		}
	}

	public function saveSequence($data, $generate = false)
	{
		$db = \App\Db::getInstance();
		$role = 0;
		foreach ($data as $item) {
			$db->createCommand()->update('yetiforce_menu', ['sequence' => $item['s'], 'parentid' => $item['p']], ['id' => $item['i']])->execute();
			if (isset($item['c'])) {
				$this->saveSequence($item['c'], false);
			}
			$role = $item['r'];
		}
		if ($generate) {
			$this->generateFileMenu($role);
		}
	}

	/**
	 * Function removes menu items.
	 *
	 * @param int[] $ids
	 */
	public function removeMenu($ids)
	{
		$db = \App\Db::getInstance();
		if (!\is_array($ids)) {
			$ids = [$ids];
		}
		foreach ($ids as $id) {
			if (empty($id)) {
				continue;
			}
			$recordModel = self::getInstanceById($id);
			$query = (new \App\Db\Query())->select(['id'])->from('yetiforce_menu')->where(['parentid' => $id]);
			$dataReader = $query->createCommand()->query();
			while ($childId = $dataReader->readColumn(0)) {
				$this->removeMenu($childId);
			}
			$dataReader->close();
			$db->createCommand()->delete('yetiforce_menu', ['id' => $id])->execute();
			if (self::SRC_ROLE === $recordModel->get('source')) {
				$this->generateFileMenu($recordModel->get('role'));
			}
		}
	}

	public function getChildMenu($roleId, $parent, int $source = 0)
	{
		$settingsModel = Settings_Menu_Module_Model::getInstance();
		$menu = [];
		$query = (new \App\Db\Query())->select(('yetiforce_menu.*, vtiger_tab.name'))
			->from('yetiforce_menu')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = yetiforce_menu.module')
			->where(['role' => $roleId, 'parentid' => $parent, 'source' => $source])
			->orderBy(' yetiforce_menu.sequence', 'yetiforce_menu.parentid');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row = [
				'id' => $row['id'],
				'tabid' => $row['module'],
				'mod' => $row['name'],
				'label' => $row['label'],
				'type' => $settingsModel->getMenuTypes($row['type']),
				'sequence' => $row['sequence'],
				'newwindow' => $row['newwindow'],
				'dataurl' => $settingsModel->getMenuUrl($row),
				'icon' => $row['icon'],
				'parent' => $row['parentid'],
				'hotkey' => $row['hotkey'],
				'filters' => $row['filters'],
				'countentries' => $row['countentries'],
				'childs' => $this->getChildMenu($roleId, $row['id'], $source),
			];
			$menu[] = $row;
		}
		$dataReader->close();
		return $menu;
	}

	/**
	 * Check permissions for display.
	 *
	 * @param array $menus
	 *
	 * @return array
	 */
	public static function parseToDisplay(array $menus): array
	{
		$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$data = [];
		foreach ($menus as $key => $item) {
			if (\in_array($item['type'], ['QuickCreate', 'Module', 'HomeIcon', 'CustomFilter', 'RecycleBin'])) {
				if (!\App\Module::isModuleActive($item['mod']) || (!$userPrivilegesModel->isAdminUser() && !$userPrivilegesModel->hasGlobalReadPermission() && !$userPrivilegesModel->hasModulePermission($item['tabid']))) {
					continue;
				}
				if ('CustomFilter' === $item['type'] && (!($cvId = vtlib\Functions::getQueryParams($item['dataurl'])['viewname'] ?? '') || !\App\CustomView::isPermitted($cvId, $item['mod']))) {
					continue;
				}
				if ('QuickCreate' === $item['type'] && (!Vtiger_Module_Model::getInstance($item['tabid'])->isQuickCreateSupported() || !$userPrivilegesModel->hasModuleActionPermission($item['tabid'], 'CreateView'))) {
					continue;
				}
			}
			$item['name'] = Vtiger_Menu_Model::getLabelToDisplay($item);
			if ($item['childs']) {
				$item['childs'] = self::parseToDisplay($item['childs']);
			}
			$data[$key] = $item;
		}

		return $data;
	}

	public function generateFileMenu($roleId)
	{
		$roleId = filter_var($roleId, FILTER_SANITIZE_NUMBER_INT);
		$menu = $this->getChildMenu($roleId, 0);
		$content = '$menus = [';
		foreach ($menu as $item) {
			$content .= $this->createContentMenu($item);
		}
		$content .= '];' . PHP_EOL . '$parentList = [';
		foreach ($menu as $item) {
			$content .= $this->createParentList($item);
		}
		$content .= '];' . PHP_EOL . '$filterList = [';
		foreach ($menu as $item) {
			$content .= $this->createFilterList($item);
		}
		$content .= '];' . PHP_EOL;
		$file = ROOT_DIRECTORY . '/user_privileges/menu_' . $roleId . '.php';
		\App\Utils::saveToFile($file, $content);
	}

	public function createContentMenu($menu)
	{
		unset($menu['filters']);
		$content = $menu['id'] . '=>[';
		foreach ($menu as $key => $item) {
			if ('childs' == $key && $item) {
				if ($item) {
					$childs = var_export($key, true) . '=>[';
					foreach ($item as $child) {
						$childs .= $this->createContentMenu($child);
					}
					$childs .= '],';
					$content .= trim($childs, ',');
				}
			} else {
				$content .= var_export($key, true) . '=>' . \App\Utils::varExport($item) . ',';
			}
		}
		return trim($content, ',') . '],';
	}

	public function createParentList($menu)
	{
		$content = $menu['id'] . '=>[';
		$content .= "'type'=>" . var_export($menu['type'], true) . ',';
		$content .= "'mod'=>" . var_export($menu['mod'], true) . ',';
		$content .= "'label'=>" . var_export($menu['label'], true) . ',';
		$content .= "'dataurl'=>" . var_export($menu['dataurl'], true) . ',';
		$content .= "'parent'=>" . var_export($menu['parent'], true) . ',';
		$content .= '],';
		if (\count($menu['childs']) > 0) {
			foreach ($menu['childs'] as $child) {
				$content .= $this->createParentList($child);
			}
		}
		return $content;
	}

	public function createFilterList($menu)
	{
		$content = '';
		if (!empty($menu['filters'])) {
			$content = $menu['id'] . '=>[';
			$content .= "'module'=>" . var_export($menu['mod'], true) . ',';
			$content .= "'filters'=>" . var_export($menu['filters'], true) . '],';
		}
		if ('CustomFilter' === $menu['type']) {
			$params = \vtlib\Functions::getQueryParams($menu['dataurl']);
			$content = $menu['id'] . '=>[';
			$content .= "'module'=>" . var_export($menu['mod'], true) . ',';
			$content .= "'filters'=>" . $params['viewname'] . '],';
		}
		if (\count($menu['childs']) > 0) {
			foreach ($menu['childs'] as $child) {
				$content .= $this->createFilterList($child);
			}
		}
		return $content;
	}

	/**
	 * A function used to refresh menu files.
	 */
	public function refreshMenuFiles()
	{
		$allRoles = Settings_Roles_Record_Model::getAll();
		$this->generateFileMenu(0);
		foreach ($allRoles as $role) {
			$roleId = str_replace('H', '', $role->getId());
			if (file_exists('user_privileges/menu_' . $roleId . '.php')) {
				$this->generateFileMenu($roleId);
			}
		}
	}

	public static function getIcons()
	{
		return ['yfm-VirtualDesk', 'fas fa-home', 'yfm-CompaniesAndContact', 'yfm-Campaigns', 'yfm-Support', 'yfm-Project', 'yfm-Bookkeeping', 'yfm-HumanResources', 'yfm-Secretary', 'yfm-Database', 'yfm-Sales', 'yfm-VendorsAccounts'];
	}

	public function getRolesContainMenu()
	{
		$allRoles = Settings_Roles_Record_Model::getAll();
		$menu = [];
		$counter = 0;
		foreach ($allRoles as $roleId => $value) {
			$hasMenu = $this->getAll(filter_var($roleId, FILTER_SANITIZE_NUMBER_INT), static::SRC_ROLE);
			if ($hasMenu) {
				$menu[$counter]['roleName'] = $allRoles[$roleId]->get('rolename');
				$menu[$counter]['roleId'] = $roleId;
				++$counter;
			}
		}
		return $menu;
	}

	/**
	 * Function adds records to task queue that updates reviewing changes in records.
	 *
	 * @param int   $fromRole - Copy from role
	 * @param int   $toRole   - Copy to role
	 * @param mixed $roleId
	 */
	public function copyMenu($fromRole, $toRole, $roleId)
	{
		$db = \App\Db::getInstance();
		$menuData = (new \App\Db\Query())->from('yetiforce_menu')
			->where(['role' => $fromRole])
			->orderBy(['parentid' => SORT_ASC, 'sequence' => SORT_ASC])->createCommand()->queryAllByGroup(1);
		if ($menuData) {
			$related = [];
			foreach ($menuData as $menuId => $menuItem) {
				if (isset($related[$menuId])) {
					$diff = array_diff_key($menuData, $related);
					$menuId = key($diff);
					$menuItem = current($diff);
				}
				if ($menuItem['parentid'] && !isset($related[$menuItem['parentid']])) {
					$menuId = $menuItem['parentid'];
					$menuItem = $menuData[$menuId];
				}
				$menuItem['role'] = $toRole;
				$menuItem['parentid'] = $related[$menuItem['parentid']] ?? $menuItem['parentid'];
				$menuItem['source'] = ($roleId && false === strpos($roleId, 'H')) ? self::SRC_API : self::SRC_ROLE;
				$db->createCommand()->insert('yetiforce_menu', $menuItem)->execute();
				$related[$menuId] = $db->getLastInsertID('yetiforce_menu_id_seq');
			}
			$this->generateFileMenu($toRole);
		}
	}
}
