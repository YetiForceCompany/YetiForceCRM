<?php

/**
 * Basic TreeView Model Class
 * @package YetiForce.TreeView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TreeView_Model extends Vtiger_Base_Model
{

	static $_cached_instance;

	/**
	 * Function to get the Module Name
	 * @return string Module name
	 */
	public function getModuleName()
	{
		return $this->get('moduleName');
	}

	/**
	 * Active tree tab
	 * @return boolean
	 */
	public function isActive()
	{
		return false;
	}

	/**
	 * Load tree tab label
	 * @return string
	 */
	public function getName()
	{
		return 'LBL_TREE_VIEW';
	}

	/**
	 * Load tree ID
	 * @return type
	 */
	public function getTemplate()
	{
		return $this->getTreeField()['fieldparams'];
	}

	/**
	 * Load tree field info
	 * @return array
	 */
	public function getTreeField()
	{
		if ($this->has('fieldTemp')) {
			return $this->get('fieldTemp');
		}
		$fieldTemp = (new App\Db\Query())->select(['tablename', 'columnname', 'fieldname', 'fieldparams'])
				->from('vtiger_field')
				->where(['uitype' => 302, 'tabid' => \App\Module::getModuleId($this->getModuleName())])
				->one();
		if (!$fieldTemp) {
			vtlib\Functions::throwNewException(vtranslate('ERR_TREE_NOT_FOUND', $this->getModuleName()));
		}
		$this->set('fieldTemp', $fieldTemp);
		return $fieldTemp;
	}

	/**
	 * Load filter parameters
	 * @param array $branches selected tree branche
	 * @return array
	 */
	public function getSearchParams($branches)
	{
		$field = $this->getTreeField();
		$searchParams = [
			['columns' => [[
					'columnname' => $field['tablename'] . ':' . $field['columnname'] . ':' . $field['fieldname'],
					'value' => implode(',', $branches),
					'column_condition' => '',
					'comparator' => 'c',
					]]],
		];
		return $searchParams;
	}

	/**
	 * Load records tree address
	 * @return string - url
	 */
	public function getTreeViewUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=TreeRecords';
	}

	/**
	 * Static Function to get the instance of Vtiger TreeView Model for the given Vtiger Module Model
	 * @param string name of the module
	 * @return Vtiger_TreeView_Model instance
	 */
	public static function getInstance($moduleModel)
	{
		$moduleName = $moduleModel->get('name');
		if (isset(self::$_cached_instance[$moduleName])) {
			return self::$_cached_instance[$moduleName];
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TreeView', $moduleName);
		$instance = new $modelClassName();
		self::$_cached_instance[$moduleName] = $instance->set('module', $moduleModel)->set('moduleName', $moduleName);
		return self::$_cached_instance[$moduleName];
	}

	/**
	 * Load tree
	 * @return String
	 */
	public function getTreeList()
	{
		$tree = [];
		$db = PearDatabase::getInstance();
		$lastId = 0;
		$result = $db->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?', [$this->getTemplate()]);
		while ($row = $db->getRow($result)) {
			$treeID = (int) ltrim($row['tree'], 'T');
			$pieces = explode('::', $row['parenttrre']);
			end($pieces);
			$parent = (int) ltrim(prev($pieces), 'T');
			$tree[] = [
				'id' => $treeID,
				'type' => 'category',
				'record_id' => $row['tree'],
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => vtranslate($row['name'], $this->getModuleName()),
				'state' => ($row['state']) ? $row['state'] : '',
				'icon' => $row['icon']
			];
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastTreeId = $lastId;
		return $tree;
	}
}
