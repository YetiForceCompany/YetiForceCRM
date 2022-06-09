<?php

/**
 * Record Class for MappedFields Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_Record_Model extends Settings_Vtiger_Record_Model
{
	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		return \App\Module::getModuleName($this->get('tabid'));
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

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];

		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-info btn-sm'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EXPORT_RECORD',
				'linkurl' => 'index.php?module=MappedFields&parent=Settings&action=ExportTemplate&id=' . $this->getId(),
				'linkicon' => 'fas fa-upload',
				'linkclass' => 'btn btn-primary btn-sm'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'class' => 'deleteMap',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-danger btn-sm deleteMap'
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		if ('status' === $key) {
			$value = $value ? 'active' : 'inactive';
		}
		return $value;
	}
}
