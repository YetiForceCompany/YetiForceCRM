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
	 * @return string Name
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
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		return $this->getEditViewUrl();
	}

	/**
	 * Function to get Edit view url
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		return "index.php?module=HideBlocks&parent=Settings&view=Edit&record=" . $this->getId();
	}

	/**
	 * Function to get Delete url
	 * @return string Url
	 */
	public function getDeleteUrl()
	{
		return "index.php?module=HideBlocks&parent=Settings&action=Delete&record=" . $this->getId();
	}

	/**
	 * Function to get record links
	 * @return <Array> list of link models <Vtiger_Link_Model>
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => $this->getDeleteUrl(),
				'linkicon' => 'glyphicon glyphicon-trash'
			]
		];
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
		\App\Db::getInstance()->createCommand()->delete('vtiger_blocks_hide', ['id' => $recordId])->execute();
		return true;
	}

	/**
	 * Function to save the record
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
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

		$conditions = \App\Json::encode($wfCondition);
		$views = $this->get('views');
		$params = [
			'blockid' => $this->get('blockid'),
			'conditions' => $conditions,
			'enabled' => ($this->get('enabled') == 'true') ? 1 : 0,
			'view' => $views
		];
		if ($this->getId()) {
			$db->createCommand()->update('vtiger_blocks_hide', $params, ['id' => $this->getId()])->execute();
		} else {
			$db->createCommand()->insert('vtiger_blocks_hide', $params)->execute();
		}
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param int $recordId
	 * @param string $qualifiedModuleName
	 * @return Settings_HideBlocks_Record_Model RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName)
	{
		$rowData = [];
		if (!empty($recordId)) {
			$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
			$recordModel = new $recordModelClass();
			$rowData = (new \App\Db\Query())
				->from('vtiger_blocks_hide')
				->where(['id' => $recordId])
				->one();
			if ($rowData) {
				$recordModel->setData($rowData);
			}
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
	 * @param string $fieldName
	 * @return string
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
		$tabid = (new \App\Db\Query())->select('tabid')
				->from('vtiger_blocks')
				->where(['blockid' => $blockId])->scalar();
		if (!empty($tabid)) {
			return Vtiger_Module_Model::getInstance($tabid);
		}
		return false;
	}
}
