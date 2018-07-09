<?php

/**
 * WebserviceUsers Module Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Api type.
	 *
	 * @var string
	 */
	public $typeApi;

	/**
	 * Table name.
	 *
	 * @var string[]
	 */
	public $baseTable = ['Portal' => 'w_#__portal_user'];

	/**
	 * Table name.
	 *
	 * @var string[]
	 */
	public $baseIndex = ['Portal' => 'id'];

	/**
	 * Module Name.
	 *
	 * @var string
	 */
	public $name = 'WebserviceUsers';

	/**
	 * List of fields displayed in list view.
	 *
	 * @var string[]
	 */
	public $listFields = ['Portal' => ['server_id' => 'FL_SERVER', 'status' => 'FL_STATUS', 'user_name' => 'FL_LOGIN', 'type' => 'FL_TYPE', 'login_time' => 'FL_LOGIN_TIME', 'logout_time' => 'FL_LOGOUT_TIME', 'language' => 'FL_LANGUAGE', 'crmid' => 'FL_RECORD_NAME', 'user_id' => 'FL_USER']];

	/**
	 * Function to retrieve name fields of a module.
	 *
	 * @return array - array which contains fields which together construct name fields
	 */
	public function getNameFields()
	{
		return [];
	}

	/**
	 * List of fields available in listview.
	 *
	 * @return \App\Base[]
	 */
	public function getListFields()
	{
		if (!isset($this->listFieldModels)) {
			$fieldObjects = [];
			if ($this->typeApi && isset($this->listFields[$this->typeApi])) {
				$fields = $this->listFields[$this->typeApi];
				foreach ($fields as $fieldName => $fieldLabel) {
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
		return $this->baseTable[$this->typeApi];
	}

	/**
	 * Function to get table primary key.
	 *
	 * @return string
	 */
	public function getTableIndex()
	{
		return $this->baseIndex[$this->typeApi];
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
}
