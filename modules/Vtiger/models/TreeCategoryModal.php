<?php

/**
 * Basic TreeCategoryModal Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TreeCategoryModal_Model extends \App\Base
{
	public static $_cached_instance;

	/**
	 * Function to get the Module Name.
	 *
	 * @return string Module name
	 */
	public function getModuleName()
	{
		return $this->get('moduleName');
	}

	/**
	 * Load tree ID.
	 *
	 * @return type
	 */
	public function getTemplate()
	{
		return $this->getTreeField()['fieldparams'];
	}

	/**
	 * Load tree field info.
	 *
	 * @return array
	 */
	public function getTreeField()
	{
		if ($this->has('fieldTemp')) {
			return $this->get('fieldTemp');
		}
		$fieldTemp = (new \App\Db\Query())->select(['tablename', 'columnname', 'fieldname', 'fieldlabel', 'fieldparams'])->from('vtiger_field')->where(['uitype' => 302, 'tabid' => \App\Module::getModuleId($this->getModuleName())])->one();
		$this->set('fieldTemp', $fieldTemp);

		return $fieldTemp;
	}

	/**
	 * Static Function to get the instance of Vtiger TreeView Model for the given Vtiger Module Model.
	 *
	 * @param string name of the module
	 * @param Vtiger_Module_Model $moduleModel
	 *
	 * @return Vtiger_TreeView_Model instance
	 */
	public static function getInstance(Vtiger_Module_Model $moduleModel)
	{
		$moduleName = $moduleModel->get('name');
		if (isset(self::$_cached_instance[$moduleName])) {
			return self::$_cached_instance[$moduleName];
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TreeCategoryModal', $moduleName);
		$instance = new $modelClassName();
		$instance->set('module', $moduleModel)->set('moduleName', $moduleName);
		self::$_cached_instance[$moduleName] = $instance;

		return self::$_cached_instance[$moduleName];
	}

	/**
	 * Gets relation model.
	 *
	 * @return bool|\Vtiger_Relation_Model
	 */
	public function getRelationModel()
	{
		if (!isset($this->relationModel)) {
			$srcModuleModel = Vtiger_Module_Model::getInstance($this->get('srcModule'));
			$this->relationModel = Vtiger_Relation_Model::getInstance($srcModuleModel, $this->get('module'));
		}
		return $this->relationModel;
	}

	/**
	 * Gets relation type.
	 *
	 * @return mixed
	 */
	public function getRelationType()
	{
		if ($this->has('relationType')) {
			return $this->get('relationType');
		}
		$this->set('relationType', $this->getRelationModel()->getRelationType());
		return $this->get('relationType');
	}

	/**
	 * Function check if record is deletable.
	 *
	 * @return bool
	 */
	public function isDeletable()
	{
		return $this->getRelationModel()->privilegeToDelete();
	}

	public function getTreeData()
	{
		return array_merge($this->getTreeList(), $this->getRecords());
	}

	/**
	 * Load tree.
	 *
	 * @return string
	 */
	private function getTreeList()
	{
		$trees = [];
		$isDeletable = $this->getRelationModel()->privilegeToTreeDelete();
		$lastId = 0;
		$dataReader = (new App\Db\Query())
			->from('vtiger_trees_templates_data')
			->where(['templateid' => $this->getTemplate()])
			->createCommand()->query();
		$selected = $this->getSelectedTreeList();
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
			$checked = \in_array($row['tree'], $selected);
			if ($checked) {
				$tree['category'] = ['checked' => true];
			}
			if (!$isDeletable && $checked) {
				$tree['state']['disabled'] = true;
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
	 * Function to get selected item in the tree.
	 *
	 * @return array
	 */
	private function getSelectedTreeList()
	{
		return (new App\Db\Query())->select(['tree'])->from('u_#__crmentity_rel_tree')
			->where(['crmid' => $this->get('srcRecord'), 'relmodule' => $this->get('module')->getId()])
			->column();
	}

	private function getSelectedRecords($onlyKeys = true)
	{
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($this->get('srcRecord'), $this->get('srcModule'));
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $this->getModuleName());
		$entries = $relationListView->getAllEntries();
		if ($onlyKeys) {
			return array_keys($entries);
		}
		return $entries;
	}

	private function getAllRecords()
	{
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($this->getModuleName(), $this->get('srcModule'));
		if (!empty($this->get('srcModule'))) {
			$listViewModel->set('src_module', $this->get('srcModule'));
			$listViewModel->set('src_record', $this->get('srcRecord'));
		}
		$listViewModel->getQueryGenerator()->setField($this->getTreeField()['fieldname']);
		return $listViewModel->getAllEntries();
	}

	private function getRecords()
	{
		$selectedRecords = $this->getSelectedRecords();
		$isDeletable = $this->isDeletable();
		if (2 == $this->getRelationType()) {
			$listEntries = $this->getAllRecords();
		} else {
			$listEntries = $this->getSelectedRecords(false);
		}

		$fieldName = $this->getTreeField()['fieldname'];
		$tree = [];
		foreach ($listEntries as $item) {
			++$this->lastIdinTree;
			$parent = (int) ltrim($item->get($fieldName), 'T');
			$selected = \in_array($item->getId(), $selectedRecords);
			$state = ['selected' => $selected];
			if (!$isDeletable && $selected) {
				$state['disabled'] = true;
			}
			$tree[] = [
				'id' => $this->lastIdinTree,
				'type' => 'category',
				'attr' => 'record',
				'record_id' => $item->getId(),
				'parent' => 0 == $parent ? '#' : $parent,
				'text' => $item->getName(),
				'state' => $state,
				'icon' => "js-detail__icon yfm-{$this->getModuleName()}",
				'category' => ['checked' => $selected]
			];
		}
		return $tree;
	}
}
