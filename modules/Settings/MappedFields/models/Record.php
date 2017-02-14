<?php

/**
 * Record Class for MappedFields Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_Record_Model extends Settings_Vtiger_Record_Model
{

	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		return vtlib\Functions::getModuleName($this->get('tabid'));
	}

	public function getEditViewUrl()
	{
		return 'index.php?module=MappedFields&parent=Settings&view=Edit&record=' . $this->getId();
	}

	public function getModule()
	{
		return $this->module;
	}

	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = [];

		$recordLinks = [
				[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			],
				[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EXPORT_RECORD',
				'linkurl' => 'index.php?module=MappedFields&parent=Settings&action=ExportTemplate&id=' . $this->getId(),
				'linkicon' => 'glyphicon glyphicon-export'
			],
				[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'class' => 'deleteMap',
				'linkicon' => 'glyphicon glyphicon-trash'
			]
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $key
	 * @return string
	 */
	public function getDisplayValue($key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'status':
				$value = $value ? 'active' : 'inactive';
				break;
		}
		return $value;
	}
}
