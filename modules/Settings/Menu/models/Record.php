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

class Settings_Menu_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get Id of this record instance
	 * @return <Integer> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance
	 * @return <String> Name
	 */
	public function getName()
	{
		return $this->get('name');
	}

	public function getAll($roleId)
	{
		$db = PearDatabase::getInstance();
		$settingsModel = Settings_Menu_Module_Model::getInstance();

		$result = $db->pquery('SELECT yetiforce_menu.*, vtiger_tab.name FROM yetiforce_menu LEFT JOIN vtiger_tab ON vtiger_tab.tabid = yetiforce_menu.module WHERE role = ? ORDER BY yetiforce_menu.sequence, yetiforce_menu.parentid;', [$roleId]);
		$menu = [];
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$menu[] = [
				'id' => $row['id'],
				'parent' => $row['parentid'] == 0 ? '#' : $row['parentid'],
				'text' => Vtiger_Menu_Model::vtranslateMenu($settingsModel->getMenuName($row, true), $row['name']),
				'icon' => 'menu-icon-' . $settingsModel->getMenuTypes($row['type'])
			];
		}
		return $menu;
	}

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public static function getInstanceById($id)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM yetiforce_menu WHERE id = ?;', [$id]);
		if ($db->num_rows($result) == 0)
			return false;

		$instance = new self();
		$instance->setData($db->raw_query_result_rowdata($result, 0));
		return $instance;
	}

	public function initialize($data)
	{
		$this->setData($data);
	}

	public function save()
	{
		$db = PearDatabase::getInstance();
		$settingsModel = Settings_Menu_Module_Model::getInstance();
		$edit = $this->get('edit');
		$params = [];
		$sqlCol = '';
		$role = 0;
		if ($edit) {
			$data = $this->getData();
			foreach ($data as $key => $item) {
				if (is_array($item)) {
					$item = implode(',', $item);
				}
				if ($key != 'id' && $key != 'edit') {
					$params[$key] = $item;
				}
			}
			if (!isset($data['newwindow'])) {
				$params['newwindow'] = 0;
			}
			$db->update('yetiforce_menu', $params, 'id = ?', [$this->getId()]);
		} else {
			foreach ($this->getData() as $key => $item) {
				if (is_array($item)) {
					$item = implode(',', $item);
				}
				$sqlCol .= $key . ',';
				switch ($key) {
					case 'role': $role = $item = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
						break;
					case 'type': $item = $settingsModel->getMenuTypeKey($item);
						break;
				}
				$params[] = $item;
			}
			$result = $db->pquery('SELECT MAX(sequence) AS max FROM yetiforce_menu WHERE role = ? && parentid = ?;', [$role, 0]);
			$max = (int) $db->query_result_raw($result, 0, 'max');
			$sqlCol .= 'sequence,';
			$params[] = $max + 1;
			$sql = 'INSERT INTO yetiforce_menu(' . trim($sqlCol, ',') . ') VALUES(' . generateQuestionMarks($params) . ')';
			$db->pquery($sql, $params);
		}
		$this->generateFileMenu($this->get('role'));
	}

	public function saveSequence($data, $generate = false)
	{
		$db = PearDatabase::getInstance();
		$role = 0;
		foreach ($data as $item) {
			$sql = 'UPDATE yetiforce_menu SET sequence = ?, parentid = ? WHERE id = ?';
			$db->pquery($sql, [$item['s'], $item['p'], $item['i']]);
			if (isset($item['c'])) {
				$this->saveSequence($item['c'], false);
			}
			$role = $item['r'];
		}
		if ($generate)
			$this->generateFileMenu($role);
	}

	public function removeMenu($id)
	{
		$db = PearDatabase::getInstance();
		$recordModel = Settings_Menu_Record_Model::getInstanceById($id);
		$result = $db->pquery('SELECT id FROM yetiforce_menu WHERE parentid = ?;', [$id]);
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
			$this->removeMenu($db->query_result_raw($result, $i, 'id'));
		}
		$db->pquery('DELETE FROM yetiforce_menu WHERE id = ?;', [$id]);
		$this->generateFileMenu($recordModel->get('role'));
	}

	public function getChildMenu($roleId, $parent)
	{
		$db = PearDatabase::getInstance();
		$settingsModel = Settings_Menu_Module_Model::getInstance();
		$result = $db->pquery('SELECT yetiforce_menu.*, vtiger_tab.name '
			. 'FROM yetiforce_menu LEFT JOIN vtiger_tab ON vtiger_tab.tabid = yetiforce_menu.module '
			. 'WHERE role = ? && parentid = ? '
			. 'ORDER BY yetiforce_menu.sequence, yetiforce_menu.parentid;', [$roleId, $parent]);
		$menu = [];
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$menu[] = [
				'id' => $row['id'],
				'tabid' => $row['module'],
				'mod' => $row['name'],
				'name' => $settingsModel->getMenuName($row),
				'type' => $settingsModel->getMenuTypes($row['type']),
				'sequence' => $row['sequence'],
				'newwindow' => $row['newwindow'],
				'dataurl' => $settingsModel->getMenuUrl($row),
				//'showicon' => $row['showicon'],
				'icon' => $row['icon'],
				//'sizeicon' => $row['sizeicon'],
				'parent' => $row['parentid'],
				'hotkey' => $row['hotkey'],
				'filters' => $row['filters'],
				'childs' => $this->getChildMenu($roleId, $row['id'])
			];
		}
		return $menu;
	}

	public function generateFileMenu($roleId)
	{
		$roleId = filter_var($roleId, FILTER_SANITIZE_NUMBER_INT);
		$menu = $this->getChildMenu($roleId, 0);
		$content = '<?php' . PHP_EOL . '$menus = [';
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
		$content .= '];';
		$file = ROOT_DIRECTORY . '/user_privileges/menu_' . $roleId . '.php';
		file_put_contents($file, $content);
	}

	public function createContentMenu($menu)
	{
		unset($menu['filters']);
		$content = $menu['id'] . '=>[';
		foreach ($menu as $key => $item) {
			if ($key == 'childs') {
				if (count($item) > 0) {
					$childs = "'" . $key . "'=>[";
					foreach ($item as $child) {
						$childs .= $this->createContentMenu($child);
					}
					$childs .= '],';
					$content .= trim($childs, ',');
				}
			} else {
				$content .= "'" . $key . "'=>'" . $item . "',";
			}
		}
		$content = trim($content, ',') . '],';
		return $content;
	}

	public function createParentList($menu)
	{
		$content = $menu['id'] . '=>[';
		$content .= "'name'=>'" . $menu['name'] . "',";
		$content .= "'url'=>'" . $menu['dataurl'] . "',";
		$content .= "'parent'=>'" . $menu['parent'] . "',";
		$content .= "'mod'=>'" . $menu['mod'] . "'";
		$content .= '],';
		if (count($menu['childs']) > 0) {
			foreach ($menu['childs'] as $child) {
				$content .= $this->createParentList($child);
			}
		}
		return $content;
	}

	public function createFilterList($menu)
	{
		if (!empty($menu['filters'])) {
			$content = $menu['id'] . '=>[';
			$content .= "'module'=>'" . $menu['mod'] . "',";
			$content .= "'filters'=>'" . $menu['filters'] . "'";
			$content .= '],';
		}
		if (count($menu['childs']) > 0) {
			foreach ($menu['childs'] as $child) {
				$content .= $this->createFilterList($child);
			}
		}
		return $content;
	}

	/**
	 * A function used to refresh menu files
	 */
	public function refreshMenuFiles()
	{
		$allRoles = Settings_Roles_Record_Model::getAll();
		$this->generateFileMenu(0);
		foreach ($allRoles as $role) {
			$roleId = str_replace('H', '', $role->getId());
			if (file_exists('user_privileges/menu_' . $roleId . '.php'))
				$this->generateFileMenu($roleId);
		}
	}

	public static function getIcons()
	{
		return ['userIcon-VirtualDesk', 'userIcon-Home', 'userIcon-CompaniesAndContact', 'userIcon-Campaigns', 'userIcon-Support', 'userIcon-Project', 'userIcon-Bookkeeping', 'userIcon-HumanResources', 'userIcon-Secretary', 'userIcon-Database', 'userIcon-Sales', 'userIcon-VendorsAccounts'];
	}

	public function getRolesContainMenu()
	{
		$db = PearDatabase::getInstance();
		$allRoles = Settings_Roles_Record_Model::getAll();
		$menu = [];
		$counter = 0;
		foreach ($allRoles as $roleId => $value) {
			$hasMenu = $this->getAll(filter_var($roleId, FILTER_SANITIZE_NUMBER_INT));
			if ($hasMenu) {
				$menu[$counter]['roleName'] = $allRoles[$roleId]->get('rolename');
				$menu[$counter]['roleId'] = $roleId;
				$counter++;
			}
		}
		return $menu;
	}
}
