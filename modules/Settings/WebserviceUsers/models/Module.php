<?php

/**
 * WebserviceUsers Module Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceUsers_Module_Model extends Settings_Vtiger_Module_Model
{
	/** @var string Api type. */
	public $typeApi;

	/** @var string Module Name. */
	public $name = 'WebserviceUsers';

	/**
	 * Gets service record instance.
	 *
	 * @return \Settings_WebserviceUsers_Record_Model
	 */
	public function getService()
	{
		$recordService = null;
		$class = "Settings_WebserviceUsers_{$this->typeApi}_Service";
		if (class_exists($class)) {
			$recordService = new $class();
			$recordService->typeApi = $this->typeApi;
		}
		return $recordService;
	}

	/**
	 * Function to retrieve name fields of a module.
	 *
	 * @return array - array which contains fields which together construct name fields
	 */
	public function getNameFields()
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			$fieldObjects = [];
			$service = $this->getService();
			if ($service) {
				foreach ($service->listFields as $fieldName => $fieldLabel) {
					$fieldObjects[$fieldName] = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
				}
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function returns name of table in database.
	 *
	 * @return type
	 */
	public function getBaseTable()
	{
		return $this->getService()->baseTable;
	}

	/**
	 * Function to get table primary key.
	 *
	 * @return string
	 */
	public function getTableIndex()
	{
		return $this->getService()->baseIndex;
	}

	/**
	 * Function to get the url for edit view of the module.
	 *
	 * @return string - url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit&typeApi=' . $this->typeApi;
	}

	/**
	 * Function to get the url for session view of the user.
	 *
	 * @return string URL
	 */
	public function getSessionViewUrl(): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=ListViewSession&typeApi=' . $this->typeApi;
	}

	/**
	 * Function to get the url for history activity view of the user.
	 *
	 * @return string URL
	 */
	public function getHistoryAccessActivityUrl(): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=HistoryAccessActivity&typeApi=' . $this->typeApi;
	}
}
