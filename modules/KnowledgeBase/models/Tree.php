<?php
/**
 * Model of tree.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class tree model for module knowledge base.
 */
class KnowledgeBase_Tree_Model extends \App\Base
{
	/**
	 * Last id in tree.
	 *
	 * @var int
	 */
	private $lastIdinTree;

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->get('moduleName');
	}

	/**
	 * Get folders.
	 *
	 * @return array
	 */
	public function getFolders()
	{
		$folders = [];
		$lastId = 0;
		$dataReader = (new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => $this->getTemplate()])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$treeID = (int) ltrim($row['tree'], 'T');
			$pieces = explode('::', $row['parentTree']);
			end($pieces);
			$parent = (int) ltrim(prev($pieces), 'T');
			$tree = [
				'id' => $treeID,
				'type' => 'folder',
				'record_id' => $row['tree'],
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => \App\Language::translate($row['name'], $this->getModuleName()),
			];
			if (!empty($row['icon'])) {
				$tree['icon'] = $row['icon'];
			}
			$folders[] = $tree;
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$dataReader->close();
		$this->lastIdinTree = $lastId;

		return $folders;
	}

	/**
	 * Get template.
	 *
	 * @return array
	 */
	public function getTemplate()
	{
		return $this->getTreeField()['fieldparams'];
	}

	/**
	 * Get tree field.
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
	 * Get all records.
	 *
	 * @return array
	 */
	public function getAllRecords()
	{
		$queryGenerator = new App\QueryGenerator($this->getModuleName());
		$queryGenerator->setFields(['id', 'category', 'knowledgebase_view', 'subject']);

		return $queryGenerator->createQuery()->all();
	}

	/**
	 * Get documents.
	 *
	 * @return array
	 */
	public function getDocuments()
	{
		$records = $this->getAllRecords();
		$fieldName = $this->getTreeField()['fieldname'];
		foreach ($records as &$item) {
			++$this->lastIdinTree;
			$parent = (int) ltrim($item[$fieldName], 'T');
			$tree[] = [
				'id' => $this->lastIdinTree,
				'type' => $item['knowledgebase_view'],
				'record_id' => $item['id'],
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => $item['subject'],
				'icon' => 'fas fa-file',
			];
		}
		return $tree;
	}

	/**
	 * Get instance.
	 *
	 * @param KnowledgeBase_Module_Model $moduleModel
	 *
	 * @return \self
	 */
	public static function getInstance($moduleModel)
	{
		$model = new self();
		$moduleName = $moduleModel->get('name');
		$model->set('module', $moduleModel)->set('moduleName', $moduleName);

		return $model;
	}
}
