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
	 * @return <String>
	 */
	public function getName()
	{
		return $this->get('rolename');
	}

	/**
	 * Function to get module of this record instance
	 * @return <Settings_Webforms_Module_Model> $moduleModel
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get the Edit View Url for the Role
	 * @return <String>
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=TreesManager&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current role
	 * @return <String>
	 */
	public function getDeleteUrl()
	{
		return '?module=TreesManager&parent=Settings&action=Delete&record=' . $this->getId();
	}

	/**
	 * Function to get Detail view url
	 * @return <String> Url
	 */
	public function getDetailViewUrl()
	{
		return "index.php?module=TreesManager&parent=Settings&view=Edit&record=" . $this->getId();
	}

	/**
	 * Function to get List view url
	 * @return <String> Url
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
		$db = PearDatabase::getInstance();
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
		$db->insert('vtiger_trees_templates_data', $params);
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
		$tree = array();
		$templateId = $this->getId();
		if (empty($templateId))
			return $tree;

		$adb = PearDatabase::getInstance();
		$lastId = 0;
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?', [$templateId]);
		$module = $this->get('module');
		if (is_numeric($module)) {
			$module = vtlib\Functions::getModuleName($module);
		}
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->raw_query_result_rowdata($result, $i);
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
	 * Function to save the role
	 */
	public function save()
	{
		$adb = PearDatabase::getInstance();
		$templateId = $this->getId();
		$mode = 'edit';
		if (empty($templateId)) {
			$sql = 'INSERT INTO vtiger_trees_templates(name, module) VALUES (?,?)';
			$params = array($this->get('name'), $this->get('module'));
			$adb->pquery($sql, $params);
			$this->set('templateid', $adb->getLastInsertID());
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		} else {
			$sql = 'UPDATE vtiger_trees_templates SET name=?, module=? WHERE templateid=?';
			$params = array($this->get('name'), $this->get('module'), $templateId);
			$adb->pquery($sql, $params);
			$adb->pquery('DELETE FROM vtiger_trees_templates_data WHERE `templateid` = ?', array($templateId));
			foreach ($this->get('tree') as $tree) {
				$this->insertData($tree, 0, '');
			}
		}
		if ($this->get('replace')) {
			$this->replaceValue($this->get('replace'), $this->get('module'), $templateId);
		}
	}

	/**
	 * Function to replaces value in module records
	 * @param <Array> $tree
	 * @param <String> $moduleId
	 * @param <String> $templateId
	 */
	public function replaceValue($tree, $moduleId, $templateId)
	{
		$adb = PearDatabase::getInstance();
		$query = 'SELECT `tablename`,`columnname` FROM `vtiger_field` WHERE `tabid` = ? && `fieldparams` = ? && presence in (0,2)';
		$result = $adb->pquery($query, array($moduleId, $templateId));
		$num_row = $adb->num_rows($result);

		for ($i = 0; $i < $num_row; $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			foreach ($tree as $row) {
				$params = [];
				foreach ($row['old'] as $new) {
					$params[] = 'T' . $new;
				}
				$adb->update($tableName, [$columnName => 'T' . current($row['new'])], $columnName . ' IN ( ' . generateQuestionMarks($row['old']) . ')', $params);
			}
		}
	}

	/**
	 * Function to delete the role
	 * @param <Settings_Roles_Record_Model> $transferToRole
	 */
	public function delete()
	{
		$adb = PearDatabase::getInstance();
		$templateId = $this->getId();
		$adb->pquery('DELETE FROM vtiger_trees_templates WHERE `templateid` = ?', array($templateId));
		$adb->pquery('DELETE FROM vtiger_trees_templates_data WHERE `templateid` = ?', array($templateId));
	}

	public function getChildren($fieldValue, $fieldName, $moduleModel)
	{
		$adb = PearDatabase::getInstance();
		$query = 'SELECT `fieldparams` FROM `vtiger_field` WHERE `tabid` = ? && `columnname` = ? && presence in (0,2)';
		$result = $adb->pquery($query, array($moduleModel->getId(), $fieldName));
		$templateId = $adb->query_result_raw($result, 0, 'fieldparams');
		$values = explode(',', $fieldValue);
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?;', array($templateId));
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$tree = $adb->query_result_raw($result, $i, 'tree');
			$parent = '';
			if ($adb->query_result_raw($result, $i, 'depth') > 0) {
				$parenttrre = $adb->query_result_raw($result, $i, 'parenttrre');
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
	 * @param <Integer> $record
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
}
