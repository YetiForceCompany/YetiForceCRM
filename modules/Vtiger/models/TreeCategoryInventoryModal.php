<?php

/**
 * Tree category inventory model file.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Tree category inventory model class.
 */
class Vtiger_TreeCategoryInventoryModal_Model extends Vtiger_TreeCategoryModal_Model
{
	/**
	 * Get all records.
	 *
	 * @return array
	 */
	private function getAllRecords()
	{
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->getModuleName(), $this->get('srcModule'));
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 0);
		$listViewModel->get('query_generator')->setField($this->getTreeField()['fieldname']);
		return $listViewModel->getListViewEntries($pagingModel);
	}

	/**
	 * Creates a tree for records.
	 *
	 * @return array
	 */
	private function getRecordsAll()
	{
		$tree = [];
		foreach ($this->getAllRecords() as $item) {
			++$this->lastIdinTree;
			$parent = (int) ltrim($item->get($this->getTreeField()['fieldname']), 'T');
			$selected = $item->getId();
			$state = ['selected' => $selected];
			$tree[] = [
				'id' => $this->lastIdinTree,
				'type' => 'category',
				'attr' => 'record',
				'record_id' => $item->getId(),
				'parent' => 0 == $parent ? '#' : $parent,
				'text' => $item->getName(),
				'icon' => "js-detail__icon yfm-{$this->getModuleName()}",
				'category' => ['checked' => false]
			];
		}
		return $tree;
	}

	/**
	 * Creates a tree for category.
	 *
	 * @return array
	 */
	private function getTreeList()
	{
		$trees = [];
		$lastId = 0;
		$dataReader = (new App\Db\Query())
			->from('vtiger_trees_templates_data')
			->where(['templateid' => $this->getTemplate()])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$treeID = (int) ltrim($row['tree'], 'T');
			$pieces = explode('::', $row['parentTree']);
			end($pieces);
			$parent = (int) ltrim(prev($pieces), 'T');
			$tree = [
				'id' => $treeID,
				'type' => 'category',
				'record_id' => $row['tree'],
				'parent' => 0 == $parent ? '#' : $parent,
				'text' => \App\Language::translate($row['name'], $this->getModuleName()),
			];
			if (!empty($row['icon'])) {
				$tree['icon'] = $row['icon'];
			}
			$checked = $row['tree'];
			if ($checked) {
				$tree['category'] = ['checked' => false];
			}
			$trees[] = $tree;
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastIdinTree = $lastId;
		return $trees;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getInstance(Vtiger_Module_Model $moduleModel)
	{
		$moduleName = $moduleModel->get('name');
		if (isset(self::$_cached_instance[$moduleName])) {
			return self::$_cached_instance[$moduleName];
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TreeCategoryInventoryModal', $moduleName);
		$instance = new $modelClassName();
		$instance->set('module', $moduleModel)->set('moduleName', $moduleName)->set('moduleName', $moduleName);
		self::$_cached_instance[$moduleName] = $instance;

		return self::$_cached_instance[$moduleName];
	}

	/**
	 * Retrieves all records and categories.
	 *
	 * @return array
	 */
	public function getTreeData()
	{
		$recordAttrId = $category = [];
		$dataToTree = array_merge($this->getTreeList(), $this->getRecordsAll());
		foreach ($dataToTree as $value) {
			if (isset($value['attr']) && 'record' === $value['attr']) {
				$recordAttrId[] = $value;
			}
		}
		foreach ($recordAttrId as $valueRecord) {
			if (\is_int($valueRecord['parent'])) {
				foreach ($dataToTree as $valueCategory) {
					if ($valueCategory['id'] === $valueRecord['parent'] && !\in_array($valueCategory, $category)) {
						$category[] = $valueCategory;
					}
				}
			}
		}
		return array_merge($recordAttrId, $category);
	}
}
