<?php

/**
 * Basic TreeView Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TreeView_Model extends \App\Base
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
	 * Active tree tab.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return false;
	}

	/**
	 * Load tree tab label.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'LBL_TREE_VIEW';
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
		$fieldTemp = (new App\Db\Query())->select(['tablename', 'columnname', 'fieldname', 'fieldparams'])
			->from('vtiger_field')
			->where(['uitype' => 302, 'tabid' => \App\Module::getModuleId($this->getModuleName())])
			->one();
		if (!$fieldTemp) {
			throw new \App\Exceptions\AppException('ERR_TREE_NOT_FOUND');
		}
		$this->set('fieldTemp', $fieldTemp);

		return $fieldTemp;
	}

	/**
	 * Load records tree address.
	 *
	 * @return string - url
	 */
	public function getTreeViewUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=TreeRecords';
	}

	/**
	 * Static Function to get the instance of Vtiger TreeView Model for the given Vtiger Module Model.
	 *
	 * @param string $moduleName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return Vtiger_TreeView_Model
	 */
	public static function getInstance(string $moduleName)
	{
		if (isset(self::$_cached_instance[$moduleName])) {
			return self::$_cached_instance[$moduleName];
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TreeView', $moduleName);
		$instance = new $modelClassName();
		self::$_cached_instance[$moduleName] = $instance->set('moduleName', $moduleName);

		return self::$_cached_instance[$moduleName];
	}

	/**
	 * Load tree.
	 *
	 * @return string
	 */
	public function getTreeList()
	{
		$tree = [];
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
			$tree[] = [
				'id' => $treeID,
				'type' => 'category',
				'record_id' => $row['tree'],
				'parent' => 0 == $parent ? '#' : $parent,
				'text' => \App\Language::translate($row['name'], $this->getModuleName()),
				'state' => ($row['state']) ? $row['state'] : '',
				'icon' => $row['icon'],
			];
			if ($treeID > $lastId) {
				$lastId = $treeID;
			}
		}
		$this->lastTreeId = $lastId;

		return $tree;
	}
}
