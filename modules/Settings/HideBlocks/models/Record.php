<?php

/**
 * Settings HideBlocks record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get Id of this record instance.
	 *
	 * @return <Integer> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance.
	 *
	 * @return string Name
	 */
	public function getName()
	{
		return 'HideBlocks';
	}

	/**
	 * Function to get module instance of this record.
	 *
	 * @return <type>
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get Detail view url.
	 *
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		return $this->getEditViewUrl();
	}

	/**
	 * Function to get Edit view url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=HideBlocks&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get record links.
	 *
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
				'linkicon' => 'fas fa-edit',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to delete this record.
	 */
	public function delete()
	{
		$recordId = $this->getId();
		\App\Db::getInstance()->createCommand()->delete('vtiger_blocks_hide', ['id' => $recordId])->execute();

		return true;
	}

	/**
	 * Function to save the record.
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$conditions = $this->get('conditions');
		$wfCondition = [];

		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ($index == '1' && empty($columns)) {
					$wfCondition[] = ['fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0', ];
				}
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = ['fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'] ?? '', 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'] ?? '', 'groupid' => $column['groupid'], ];
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
			'view' => $views,
		];
		if ($this->getId()) {
			$db->createCommand()->update('vtiger_blocks_hide', $params, ['id' => $this->getId()])->execute();
		} else {
			$db->createCommand()->insert('vtiger_blocks_hide', $params)->execute();
		}
	}

	/**
	 * Function to get record instance by using id and moduleName.
	 *
	 * @param int    $recordId
	 * @param string $qualifiedModuleName
	 *
	 * @return Settings_HideBlocks_Record_Model RecordModel
	 */
	public static function getInstanceById($recordId, $qualifiedModuleName)
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

	public static function getCleanInstance($qualifiedModuleName)
	{
		$className = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		return new $className();
	}

	/**
	 * Function to get display value of every field from this record.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getDisplayValue($fieldName)
	{
		$fieldValue = $this->get($fieldName);

		switch ($fieldName) {
			case 'name':
				$fieldValue = \App\Language::translate($fieldValue, $fieldValue);
				break;
			case 'blocklabel':
				$fieldValue = \App\Language::translate($fieldValue, $this->get('name'));
				break;
			case 'enabled':
				$fieldValue = \App\Language::translate($this->get('enabled') == 1 ? 'LBL_YES' : 'LBL_NO', $this->get('name'));
				break;
			case 'view':
				$fieldValue = '';
				if ($this->get('view') != '') {
					$selectedViews = explode(',', $this->get('view'));
					foreach ($selectedViews as $view) {
						$views[] = \App\Language::translate('LBL_VIEW_' . strtoupper($view), $this->get('name'));
					}
					$fieldValue = implode($views, ',');
				}
				break;
			default:
				break;
		}
		return $fieldValue;
	}

	public static function getModuleInstanceByBlockId($blockId)
	{
		$tabid = (new \App\Db\Query())->select(['tabid'])
			->from('vtiger_blocks')
			->where(['blockid' => $blockId])->scalar();
		if (!empty($tabid)) {
			return Vtiger_Module_Model::getInstance($tabid);
		}
		return false;
	}
}
