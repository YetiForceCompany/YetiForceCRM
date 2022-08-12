<?php

/**
 * Settings TreesManager record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * @return Settings_TreesManager_Module_Model $moduleModel
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set module instance to this record instance.
	 *
	 * @param Settings_Vtiger_Module_Model $moduleModel
	 *
	 * @return $this
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
		return $this;
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

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
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
		if ('' != $parentTree) {
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
			'icon' => $tree['icon'],
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
		$module = $this->get('tabid');
		if (is_numeric($module)) {
			$module = App\Module::getModuleName($module);
		}
		$treeValue = $treeValue ? explode(',', $treeValue) : [];
		while ($row = $dataReader->read()) {
			$treeID = (int) str_replace('T', '', $row['tree']);
			$cut = \strlen('::' . $row['tree']);
			$parentTree = substr($row['parentTree'], 0, -$cut);
			$pieces = explode('::', $parentTree);
			$parent = (int) str_replace('T', '', end($pieces));
			$icon = $row['icon'] ?: false;
			if ($icon && false !== strpos($icon, '/') && !IS_PUBLIC_DIR) {
				$icon = 'public_html/' . $icon;
			}
			$parameters = [
				'id' => $treeID,
				'parent' => 0 === $parent ? '#' : $parent,
				'text' => \App\Language::translate($row['name'], $module, null, false),
				'li_attr' => [
					'text' => \App\Language::translate($row['name'], $module, null, false),
					'key' => $row['name'],
				],
				'state' => ($row['state']) ? \App\Json::decode($row['state']) : '',
				'icon' => $icon,
			];
			if ($category) {
				$parameters['type'] = $category;
				if ($treeValue && \in_array($row['tree'], $treeValue)) {
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
		if ('share' === $key) {
			if ($val) {
				$val = !\is_array($val) ? array_filter(explode(',', $val)) : $val;
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
				->insert('vtiger_trees_templates', ['name' => $this->get('name'), 'tabid' => $this->get('tabid'), 'share' => $share])
				->execute();
			$this->set('templateid', $db->getLastInsertID('vtiger_trees_templates_templateid_seq'));
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		} else {
			$db->createCommand()
				->update('vtiger_trees_templates', ['name' => $this->get('name'), 'tabid' => $this->get('tabid'), 'share' => $share], ['templateid' => $templateId])
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
		$modules[] = $this->get('tabid');
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
		$db->createCommand()->delete('vtiger_trees_templates', ['templateid' => $templateId])->execute();
		$this->clearCache();
	}

	/**
	 * Gets elements of tree with given value.
	 *
	 * @param string               $fieldValue
	 * @param string               $fieldName
	 * @param \Vtiger_Module_Model $moduleMode
	 * @param \Vtiger_Module_Model $moduleModel
	 *
	 * @return string
	 */
	public static function getChildren(string $fieldValue, string $fieldName, Vtiger_Module_Model $moduleModel)
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
				$cut = \strlen('::' . $tree);
				$parentTree = substr($parentTree, 0, -$cut);
				$pieces = explode('::', $parentTree);
				$parent = end($pieces);
			}
			if ($parent && \in_array($parent, $values) && !\in_array($tree, $values)) {
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
	 * @return $this|null instance, if exists. Null otherwise
	 */
	public static function getInstanceById($record)
	{
		$instance = null;
		$row = (new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => $record])->one();
		if ($row) {
			$instance = self::getCleanInstance();
			$instance->setData($row);
		}

		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$cacheName = __CLASS__;
		$key = 'Clean';
		if (\App\Cache::staticHas($cacheName, $key)) {
			return clone \App\Cache::staticGet($cacheName, $key);
		}
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:TreesManager');
		$instance = new self();
		$instance->module = $moduleInstance;
		\App\Cache::staticSave($cacheName, $key, clone $instance);

		return $instance;
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
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		\App\Cache::delete('TreeValuesById', $this->getId());
	}

	/** @var string[] Fields to edit */
	public $editFields = ['name', 'tabid', 'share'];

	/**
	 * Get structure fields.
	 *
	 * @return array
	 */
	public function getEditViewStructure(): array
	{
		$structure = [];
		foreach ($this->editFields as $fieldName) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($this->has($fieldName)) {
				$fieldModel->set('fieldvalue', $this->get($fieldName));
			} else {
				$defaultValue = $fieldModel->get('defaultvalue');
				$fieldModel->set('fieldvalue', $defaultValue ?? '');
			}
			$structure[$fieldName] = $fieldModel;
		}

		return $structure;
	}

	/**
	 * Get field instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name)
	{
		$params = [];
		$qualifiedModuleName = 'Settings:TreesManager';
		switch ($name) {
			case 'name':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_NAME',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => 255,
					'purifyType' => \App\Purifier::TEXT,
				];
				break;
			case 'tabid':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_MODULE',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '32767',
					'purifyType' => \App\Purifier::INTEGER,
					'picklistValues' => [],
					'isEditableReadOnly' => !empty($this->getId())
				];
				foreach ($this->getModule()->getSupportedModules() as $moduleModel) {
					$params['picklistValues'][$moduleModel->getId()] = \App\Language::translate($moduleModel->getName(), $moduleModel->getName());
				}
				break;
			case 'share':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_SHARE_WITH',
					'uitype' => 33,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::INTEGER,
					'picklistValues' => []
				];
				foreach ($this->getModule()->getSupportedModules() as $moduleModel) {
					$params['picklistValues'][$moduleModel->getId()] = \App\Language::translate($moduleModel->getName(), $moduleModel->getName());
				}
				break;
			case 'tree':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_PREFIX',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '25',
					'purifyType' => \App\Purifier::TEXT,
					'tooltip' => 'LBL_DESCRIPTION_PREFIXES'
				];
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($qualifiedModuleName, $params, $name) : null;
	}

	/**
	 * Parse tree data for save.
	 *
	 * @param array $tree
	 *
	 * @return array
	 */
	public function parseTreeDataForSave(array $tree): array
	{
		$values = [];
		foreach ($tree as $branch) {
			$value = [];
			$value['id'] = (int) $branch['id'];
			$value['text'] = \App\Purifier::decodeHtml($branch['text']);

			$value['state'] = [
				'loaded' => \App\Validator::bool($branch['state']['loaded']) ? $branch['state']['loaded'] : false,
				'opened' => \App\Validator::bool($branch['state']['opened']) ? $branch['state']['opened'] : false,
				'selected' => \App\Validator::bool($branch['state']['selected']) ? $branch['state']['selected'] : false,
				'disabled' => \App\Validator::bool($branch['state']['disabled']) ? $branch['state']['disabled'] : false,
			];
			$icon = \App\Purifier::decodeHtml($branch['icon'] ?? '');
			if ($icon && false !== strpos($icon, '/')) {
				$icon = \App\Purifier::purifyByType($icon, \App\Purifier::PATH);
				if (0 === strpos($icon, 'public_html/')) {
					$icon = substr($icon, 12);
				}
			} elseif ($icon && ('1' === $icon || !\App\Validator::fontIcon($icon))) {
				$icon = '';
			}
			$value['icon'] = $icon;
			if (!empty($branch['children'])) {
				$value['children'] = $this->parseTreeDataForSave($branch['children']);
			}
			$values[] = $value;
		}

		return $values;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getDisplayValue(string $name)
	{
		switch ($name) {
			case 'tabid':
				$moduleName = \App\Module::getModuleName($this->get($name));
				$value = \App\Language::translate($moduleName, $moduleName);
				break;
			case 'name':
				$value = \App\Language::translate($this->get($name), $this->getModule()->getName(true));
				break;
			default:
				$fieldInstance = $this->getFieldInstanceByName($name);
				$value = $fieldInstance->getDisplayValue($this->get($name), false, false, true);
				break;
		}

		return $value;
	}
}
