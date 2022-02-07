<?php

/**
 * Tree category inventory model file.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Tree category inventory model class.
 */
class Vtiger_TreeInventoryModal_Model extends Vtiger_TreeCategoryModal_Model
{
	/**
	 * @var bool Auto register events
	 */
	public $autoRegisterEvents = false;

	/**
	 * Creates a tree for records.
	 *
	 * @return array
	 */
	private function getRecords()
	{
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->getModuleName(), $this->get('srcModule'));
		$listViewModel->getQueryGenerator()->setFields(['id', $this->getTreeField()['fieldname']]);
		$tree = [];
		foreach ($listViewModel->getAllEntries() as $item) {
			++$this->lastTreeId;
			$parent = (int) ltrim($item->get($this->getTreeField()['fieldname']), 'T');
			$tree[] = [
				'id' => $this->lastTreeId,
				'type' => 'category',
				'attr' => 'record',
				'record_id' => $item->getId(),
				'parent' => 0 === $parent ? '#' : $parent,
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
				'parent' => 0 === $parent ? '#' : $parent,
				'text' => \App\Language::translate($row['name'], $this->getModuleName()),
				'state' => ['disabled' => true]
			];
			if (!empty($row['icon'])) {
				$tree['icon'] = $row['icon'];
			}
			$trees[$treeID] = $tree;
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastTreeId = $lastId;
		return $trees;
	}

	/** {@inheritdoc} */
	public static function getInstance(Vtiger_Module_Model $moduleModel)
	{
		$moduleName = $moduleModel->get('name');
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TreeInventoryModal', $moduleName);
		$instance = new $modelClassName();
		$instance->set('module', $moduleModel)->set('moduleName', $moduleName);
		return $instance;
	}

	/**
	 * Retrieves all records and categories.
	 *
	 * @return array
	 */
	public function getTreeData()
	{
		$treeData = [];
		$treeList = $this->getTreeList();
		$records = $this->getRecords();
		foreach ($records as $tree) {
			while (isset($treeList[$tree['parent']]) && !\in_array($treeList[$tree['parent']], $treeData)) {
				$tree = $treeList[$tree['parent']];
				$treeData[] = $tree;
			}
		}
		return array_merge($treeData, $records);
	}
}
