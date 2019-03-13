<?php

/**
 * Settings TreesManager record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_TreesManager_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return <Number> Role Id
	 */
	public function getId()
	{
		return $this->get('templateid');
	}

	/**
	 * Function to get the Role Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rolename');
	}

	/**
	 * Function to get module of this record instance.
	 *
	 * @return Settings_TreesManager_Record_Model $moduleModel
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get the Edit View Url for the Role.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=TreesManager&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current role.
	 *
	 * @return string
	 */
	public function getDeleteUrl()
	{
		return '?module=TreesManager&parent=Settings&action=Delete&record=' . $this->getId();
	}

	/**
	 * Function to get Detail view url.
	 *
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		return 'index.php?module=TreesManager&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get List view url.
	 *
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=TreesManager&parent=Settings&view=List';
	}

	/**
	 * Function to get record links.
	 *
	 * @return <Array> list of link models <Vtiger_Link_Model>
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn btn-sm btn-info',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'" . $this->getDeleteUrl() . "');",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger text-white',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to save the role.
	 *
	 * @param array  $tree
	 * @param int    $depth
	 * @param string $parentTree
	 */
	public function insertData($tree, $depth, $parentTree)
	{
		$label = $tree['text'];
		$id = $tree['id'];
		$treeID = 'T' . $id;
		$icon = (int) $tree['icon'] === 1 ? '' : $tree['icon'];
		if ($parentTree != '') {
			$parentTree = $parentTree . '::';
		}
		$parentTree = $parentTree . $treeID;
		$params = [
			'templateid' => $this->getId(),
			'name' => $label,
			'tree' => $treeID,
			'parentTree' => $parentTree,
			'depth' => $depth,
			'label' => $label,
			'state' => $tree['state'] ? \App\Json::encode($tree['state']) : '',
			'icon' => $icon
		];
		App\Db::getInstance()->createCommand()->insert('vtiger_trees_templates_data', $params)->execute();
		if (!empty($tree['children'])) {
			foreach ($tree['children'] as $treeChild) {
				$this->insertData($treeChild, $depth + 1, $parentTree);
			}
		}
	}

	/**
	 * Get tree.
	 *
	 * @param string $category
	 * @param string $treeValue
	 *
	 * @return bool|array
	 */
	public function getTree($category = false, $treeValue = false)
	{
		$tree = [];
		$templateId = $this->getId();
		if (empty($templateId)) {
			return $tree;
		}

		$lastId = 0;
		$dataReader = (new App\Db\Query())->from('vtiger_trees_templates_data')
			->where(['templateid' => $templateId])
			->createCommand()->query();
		$module = $this->get('module');
		if (is_numeric($module)) {
			$module = App\Module::getModuleName($module);
		}
		$treeValue = $treeValue ? explode(',', $treeValue) : [];
		while ($row = $dataReader->read()) {
			$treeID = (int) str_replace('T', '', $row['tree']);
			$cut = strlen('::' . $row['tree']);
			$parentTree = substr($row['parentTree'], 0, -$cut);
			$pieces = explode('::', $parentTree);
			$parent = (int) str_replace('T', '', end($pieces));
			$icon = false;
			if (!empty($row['icon'])) {
				$basePath = '';
				if ($row['icon'] && strpos($row['icon'], 'layouts') === 0 && !IS_PUBLIC_DIR) {
					$basePath = 'public_html/';
				}
				$icon = $basePath . $row['icon'];
			}
			$parameters = [
				'id' => $treeID,
				'parent' => $parent === 0 ? '#' : $parent,
				'text' => \App\Language::translate($row['name'], $module),
				'li_attr' => [
					'text' => \App\Language::translate($row['name'], $module),
					'key' => $row['name'],
				],
				'state' => ($row['state']) ? \App\Json::decode($row['state']) : '',
				'icon' => $icon,
			];
			if ($category) {
				$parameters['type'] = $category;
				if ($treeValue && in_array($row['tree'], $treeValue)) {
					$parameters[$category] = ['checked' => true];
				}
			}
			$tree[] = $parameters;
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$dataReader->close();
		$this->set('lastId', $lastId);

		return $tree;
	}

	/**
	 * Get.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		$val = parent::get($key);
		if ($key === 'share') {
			if ($val) {
				$val = !is_array($val) ? array_filter(explode(',', $val)) : $val;
			} else {
				$val = [];
			}
		}
		return $val;
	}

	/**
	 * Function to save the tree.
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$templateId = $this->getId();
		$share = static::getShareFromArray($this->get('share'));
		if (empty($templateId)) {
			$db->createCommand()
				->insert('vtiger_trees_templates', ['name' => $this->get('name'), 'module' => $this->get('module'), 'share' => $share])
				->execute();
			$this->set('templateid', $db->getLastInsertID('vtiger_trees_templates_templateid_seq'));
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		} else {
			$db->createCommand()
				->update('vtiger_trees_templates', ['name' => $this->get('name'), 'module' => $this->get('module'), 'share' => $share], ['templateid' => $templateId])
				->execute();
			$db->createCommand()->delete('vtiger_trees_templates_data', ['templateid' => $templateId])
				->execute();
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		}
		if ($this->get('replace')) {
			$this->replaceValue($this->get('replace'), $templateId);
		}
		$this->clearCache();
	}

	/**
	 * Function to replaces value in module records.
	 *
	 * @param array $tree
	 * @param int   $templateId
	 */
	public function replaceValue($tree, $templateId)
	{
		$db = App\Db::getInstance();
		$modules = $this->get('share');
		$modules[] = $this->get('module');
		$dataReader = (new App\Db\Query())->select(['tablename', 'columnname', 'uitype'])
			->from('vtiger_field')
			->where(['tabid' => $modules, 'fieldparams' => (string) $templateId, 'presence' => [0, 2]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$uiType = (int) $row['uitype'];
			if (309 === $uiType) {
				$this->updateCategoryMultipicklist($tree, $tableName, $columnName);
			} else {
				foreach ($tree as $treeRow) {
					$params = [];
					foreach ($treeRow['old'] as $new) {
						$params[] = 'T' . $new;
					}
					$db->createCommand()
						->update($tableName, [$columnName => 'T' . current($treeRow['new'])], [$columnName => $params])
						->execute();
				}
			}
		}
		$dataReader->close();
	}

	/**
	 * Update category multipicklist.
	 *
	 * @param array  $tree
	 * @param string $tableName
	 * @param string $columnName
	 *
	 * @throws \yii\db\Exception
	 */
	private function updateCategoryMultipicklist(array $tree, string $tableName, string $columnName)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($tree as $treeRow) {
			$query = (new \App\Db\Query())->from($tableName);
			$query->orWhere(['like', $columnName, ",T{$treeRow['old'][0]},"]);
			$dataReaderTree = $query->createCommand()->query();
			while ($rowTree = $dataReaderTree->read()) {
				$dbCommand->update(
					$tableName,
					[$columnName => str_replace(",T{$treeRow['old'][0]},", ",T{$treeRow['new'][0]},", $rowTree[$columnName])],
					[$columnName => $rowTree[$columnName]]
				)->execute();
			}
			$dataReaderTree->close();
		}
	}

	/**
	 * Function to delete the role.
	 */
	public function delete()
	{
		$db = App\Db::getInstance();
		$templateId = $this->getId();
		$db->createCommand()
			->delete('vtiger_trees_templates', ['templateid' => $templateId])
			->execute();
		$db->createCommand()
			->delete('vtiger_trees_templates_data', ['templateid' => $templateId])
			->execute();
		$this->clearCache();
	}

	/**
	 * Gets elements of tree with given value.
	 *
	 * @param string               $fieldValue
	 * @param string               $fieldName
	 * @param \Vtiger_Module_Model $moduleMode
	 *
	 * @return string
	 */
	public static function getChildren(string $fieldValue, string $fieldName, \Vtiger_Module_Model $moduleModel)
	{
		$templateId = (new App\Db\Query())->select(['fieldparams'])
			->from('vtiger_field')
			->where(['tabid' => $moduleModel->getId(), 'columnname' => $fieldName, 'presence' => [0, 2]])
			->scalar();
		$values = explode('##', $fieldValue);
		$dataReader = (new App\Db\Query())->from('vtiger_trees_templates_data')
			->where(['templateid' => $templateId])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tree = $row['tree'];
			$parent = '';
			if ($row['depth'] > 0) {
				$parentTree = $row['parentTree'];
				$cut = strlen('::' . $tree);
				$parentTree = substr($parentTree, 0, -$cut);
				$pieces = explode('::', $parentTree);
				$parent = end($pieces);
			}
			if ($parent && in_array($parent, $values) && !in_array($tree, $values)) {
				$values[] = $tree;
			}
		}
		$dataReader->close();
		return implode('##', $values);
	}

	/**
	 * Function to get the instance of Role model, given role id.
	 *
	 * @param int $record
	 *
	 * @return Settings_Roles_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($record)
	{
		$row = (new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => $record])
			->one();
		if ($row) {
			$instance = new self();
			$instance->setData($row);

			return $instance;
		}
		return null;
	}

	/**
	 * Get share string from array.
	 *
	 * @param array()|null $share
	 *
	 * @return string
	 */
	public static function getShareFromArray($share)
	{
		return $share ? ',' . implode(',', $share) . ',' : '';
	}

	/**
	 * Function clears cache.
	 */
	public function clearCache()
	{
		\App\Cache::delete('TreeValuesById', $this->getId());
	}
}
