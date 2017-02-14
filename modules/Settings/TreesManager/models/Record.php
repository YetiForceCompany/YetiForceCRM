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

class Settings_TreesManager_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get the Id
	 * @return <Number> Role Id
	 */
	public function getId()
	{
		return $this->get('templateid');
	}

	/**
	 * Function to get the Role Name
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rolename');
	}

	/**
	 * Function to get module of this record instance
	 * @return Settings_TreesManager_Record_Model $moduleModel
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get the Edit View Url for the Role
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=TreesManager&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current role
	 * @return string
	 */
	public function getDeleteUrl()
	{
		return '?module=TreesManager&parent=Settings&action=Delete&record=' . $this->getId();
	}

	/**
	 * Function to get Detail view url
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		return "index.php?module=TreesManager&parent=Settings&view=Edit&record=" . $this->getId();
	}

	/**
	 * Function to get List view url
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return "index.php?module=TreesManager&parent=Settings&view=List";
	}

	/**
	 * Function to get record links
	 * @return <Array> list of link models <Vtiger_Link_Model>
	 */
	public function getRecordLinks()
	{
		$links = array();
		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'" . $this->getDeleteUrl() . "');",
				'linkicon' => 'glyphicon glyphicon-trash'
			)
		);
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to save the role
	 */
	public function insertData($tree, $depth, $parenttrre)
	{
		$label = $tree['text'];
		$id = $tree['id'];
		$state = '';
		$treeID = 'T' . $id;
		$icon = $tree['icon'] == 1 ? '' : $tree['icon'];
		if ($parenttrre != '')
			$parenttrre = $parenttrre . '::';
		$parenttrre = $parenttrre . $treeID;
		$params = [
			'templateid' => $this->getId(),
			'name' => $label,
			'tree' => $treeID,
			'parenttrre' => $parenttrre,
			'depth' => $depth,
			'label' => $label,
			'state' => $state,
			'icon' => $icon
		];
		App\Db::getInstance()->createCommand()->insert('vtiger_trees_templates_data', $params)->execute();
		if (!empty($tree['children'])) {
			foreach ($tree['children'] as $tree) {
				$this->insertData($tree, $depth + 1, $parenttrre);
				if ($tree['metadata']['replaceid'])
					$this->replaceValue($tree, $this->get('module'), $this->getId());
			}
		}
	}

	public function getTree($category = false)
	{
		$tree = [];
		$templateId = $this->getId();
		if (empty($templateId))
			return $tree;

		$lastId = 0;
		$dataReader = (new App\Db\Query())->from('vtiger_trees_templates_data')
				->where(['templateid' => $templateId])
				->createCommand()->query();
		$module = $this->get('module');
		if (is_numeric($module)) {
			$module = App\Module::getModuleName($module);
		}
		while ($row = $dataReader->read()) {
			$treeID = (int) str_replace('T', '', $row['tree']);
			$cut = strlen('::' . $row['tree']);
			$parenttrre = substr($row['parenttrre'], 0, - $cut);
			$pieces = explode('::', $parenttrre);
			$parent = (int) str_replace('T', '', end($pieces));
			$parameters = [
				'id' => $treeID,
				'parent' => $parent == 0 ? '#' : $parent,
				'text' => vtranslate($row['name'], $module),
				'state' => ($row['state']) ? $row['state'] : '',
				'icon' => $row['icon'],
			];
			if ($category) {
				$parameters['type'] = $category;
			}
			$tree[] = $parameters;
			if ($treeID > $lastId)
				$lastId = $treeID;
		}
		$this->set('lastId', $lastId);
		return $tree;
	}

	/**
	 * Function to save the tree
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$templateId = $this->getId();
		if (empty($templateId)) {
			$db->createCommand()
				->insert('vtiger_trees_templates', ['name' => $this->get('name'), 'module' => $this->get('module')])
				->execute();
			$this->set('templateid', $db->getLastInsertID('vtiger_trees_templates_templateid_seq'));
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		} else {
			$db->createCommand()
				->update('vtiger_trees_templates', ['name' => $this->get('name'), 'module' => $this->get('module')], ['templateid' => $templateId])
				->execute();
			$db->createCommand()->delete('vtiger_trees_templates_data', ['templateid' => $templateId])
				->execute();
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		}
		if ($this->get('replace')) {
			$this->replaceValue($this->get('replace'), $this->get('module'), $templateId);
		}
		$this->clearCache();
	}

	/**
	 * Function to replaces value in module records
	 * @param array $tree
	 * @param string $moduleId
	 * @param string $templateId
	 */
	public function replaceValue($tree, $moduleId, $templateId)
	{
		$db = App\Db::getInstance();
		$dataReader = (new App\Db\Query())->select(['tablename', 'columnname'])
				->from('vtiger_field')
				->where(['tabid' => $moduleId, 'fieldparams' => $templateId, 'presence' => [0, 2]])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			foreach ($tree as $row) {
				$params = [];
				foreach ($row['old'] as $new) {
					$params[] = 'T' . $new;
				}
				$db->createCommand()
					->update($tableName, [$columnName => 'T' . current($row['new'])], [$columnName => $params])
					->execute();
			}
		}
	}

	/**
	 * Function to delete the role
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

	public static function getChildren($fieldValue, $fieldName, $moduleModel)
	{

		$templateId = (new App\Db\Query())->select(['fieldparams'])
			->from('vtiger_field')
			->where(['tabid' => $moduleModel->getId(), 'columnname' => $fieldName, 'presence' => [0, 2]])
			->scalar();
		$values = explode(',', $fieldValue);
		$dataReader = (new App\Db\Query())->from('vtiger_trees_templates_data')
				->where(['templateid' => $templateId])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tree = $row['tree'];
			$parent = '';
			if ($row['depth'] > 0) {
				$parenttrre = $row['parenttrre'];
				$cut = strlen('::' . $tree);
				$parenttrre = substr($parenttrre, 0, - $cut);
				$pieces = explode('::', $parenttrre);
				$parent = end($pieces);
			}
			if ($parent && in_array($parent, $values) && !in_array($tree, $values)) {
				$values[] = $tree;
			}
		}
		return implode(',', $values);
	}

	/**
	 * Function to get the instance of Role model, given role id
	 * @param integer $record
	 * @return Settings_Roles_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($record)
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_trees_templates WHERE templateid = ?';
		$params = array($record);
		$result = $db->pquery($sql, $params);
		if ($db->getRowCount($result) > 0) {
			$instance = new self();
			$instance->setData($db->getRow($result));
			return $instance;
		}
		return null;
	}

	/**
	 * Function clears cache
	 */
	public function clearCache()
	{
		\App\Cache::delete('TreeData', $this->getId());
	}
}
