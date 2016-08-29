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

class Settings_HideBlocks_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get Id of this record instance
	 * @return <Integer> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance
	 * @return <String> Name
	 */
	public function getName()
	{
		return 'HideBlocks';
	}

	/**
	 * Function to get module instance of this record
	 * @return <type>
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get Detail view url
	 * @return <String> Url
	 */
	public function getDetailViewUrl()
	{
		return $this->getEditViewUrl();
	}

	/**
	 * Function to get Edit view url
	 * @return <String> Url
	 */
	public function getEditViewUrl()
	{
		$moduleModel = $this->getModule();
		return "index.php?module=HideBlocks&parent=Settings&view=Edit&record=" . $this->getId();
	}

	/**
	 * Function to get Delete url
	 * @return <String> Url
	 */
	public function getDeleteUrl()
	{
		$moduleModel = $this->getModule();
		return "index.php?module=HideBlocks&parent=Settings&action=Delete&record=" . $this->getId();
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
				'linkurl' => $this->getDeleteUrl(),
				'linkicon' => 'glyphicon glyphicon-trash'
			)
		);
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to delete this record
	 */
	public function delete()
	{
		$recordId = $this->getId();
		$db = PearDatabase::getInstance();
		$db->pquery("DELETE from vtiger_blocks_hide WHERE id = ?", array($recordId));
		return true;
	}

	/**
	 * Function to save the record
	 */
	public function save()
	{
		$db = PearDatabase::getInstance();
		$conditions = $this->get('conditions');
		$wfCondition = array();

		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ($index == '1' && empty($columns)) {
					$wfCondition[] = array('fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0');
				}
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = array('fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'], 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'], 'groupid' => $column['groupid']);
					}
				}
			}
		}

		$conditions = \includes\utils\Json::encode($wfCondition);
		$views = $this->get('views');
		if ($this->getId()) {
			$updateQuery = "UPDATE vtiger_blocks_hide SET `blockid` = ?,`conditions` = ?,`enabled` = ?,`view` = ? WHERE `id` = ?;";
			$params = array(
				$this->get('blockid'),
				$conditions,
				($this->get('enabled') == 'true') ? 1 : 0,
				$views,
				$this->getId(),
			);
			$db->pquery($updateQuery, $params);
		} else {
			$updateQuery = "INSERT INTO vtiger_blocks_hide (`blockid`, `conditions`, `enabled`, `view`) VALUES (?, ?, ?, ?);";
			$params = array(
				$this->get('blockid'),
				$conditions,
				($this->get('enabled') == 'true') ? 1 : 0,
				$views,
			);
			$db->pquery($updateQuery, $params);
		}
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param <Integer> $recordId
	 * @param <String> $qualifiedModuleName
	 * @return <Settings_Webforms_Record_Model> RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName)
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_blocks_hide WHERE id = ?", array($recordId));
		if ($db->num_rows($result)) {
			$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
			$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
			$rowData = $db->raw_query_result_rowdata($result, 0);
			$recordModel = new $recordModelClass();
			$recordModel->setData($rowData);
			return $recordModel;
		}
		return false;
	}

	static public function getCleanInstance($qualifiedModuleName)
	{
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		$recordModel = new $recordModelClass();
		return $recordModel;
	}

	/**
	 * Function to get display value of every field from this record
	 * @param <String> $fieldName
	 * @return <String>
	 */
	public function getDisplayValue($fieldName)
	{
		$fieldValue = $this->get($fieldName);

		switch ($fieldName) {
			case 'name' :
				$fieldValue = vtranslate($fieldValue, $fieldValue);
				break;
			case 'blocklabel' :
				$fieldValue = vtranslate($fieldValue, $this->get('name'));
				break;
			case 'enabled' :
				$fieldValue = vtranslate($this->get('enabled') == 1 ? 'LBL_YES' : 'LBL_NO', $this->get('name'));
				break;
			case 'view' :
				$fieldValue = '';
				if ($this->get('view') != '') {
					$selectedViews = explode(',', $this->get('view'));
					foreach ($selectedViews as $view) {
						$views[] = vtranslate('LBL_VIEW_' . strtoupper($view), $this->get('name'));
					}
					$fieldValue = implode($views, ',');
				}
				break;
		}
		return $fieldValue;
	}

	public function getModuleInstanceByBlockId($blockId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT * FROM vtiger_blocks WHERE blockid = ?", array($blockId));

		if ($db->num_rows($result) > 0) {
			$rowData = $db->query_result_rowdata($result, 0);
			$moduleInstance = Vtiger_Module_Model::getInstance($rowData['tabid']);
			return $moduleInstance;
		}
		return false;
	}
}
