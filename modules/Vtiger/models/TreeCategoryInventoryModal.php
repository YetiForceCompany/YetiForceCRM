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
	 * Creates a tree for records.
	 *
	 * @return array
	 */
	private function getRecords()
	{
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->getModuleName(), $this->get('srcModule'));
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 0);
		$listViewModel->get('query_generator')->setField($this->getTreeField()['fieldname']);
		$tree = [];
		foreach ($listViewModel->getListViewEntries($pagingModel) as $item) {
			++$this->lastIdinTree;
			$parent = (int) ltrim($item->get($this->getTreeField()['fieldname']), 'T');
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
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TreeCategoryInventoryModal', $moduleName);
		$instance = new $modelClassName();
		$instance->set('module', $moduleModel)->set('moduleName', $moduleName)->set('moduleName', $moduleName);
		return $instance;
	}

	/**
	 * Retrieves all records and categories.
	 *
	 * @return array
	 */
	public function getTreeData()
	{
		$recordAttrId = $category = [];
		$treeList = $this->getTreeList();
		foreach ($this->getRecords() as $valueRecord) {
			$recordAttrId[] = $valueRecord;
			foreach ($treeList as $valueCategory) {
				if ($valueCategory['id'] === $valueRecord['parent'] && !\in_array($valueCategory, $category)) {
					$category[] = $valueCategory;
				}
			}
		}
		return array_merge($recordAttrId, $category);
	}
}
