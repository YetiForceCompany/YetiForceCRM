<?php

/**
 * Field Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Vtiger_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Variables.
	 *
	 * @var string[]
	 */
	public $referenceList = [];
	public $picklistValues = [];

	/**
	 * Initialize.
	 *
	 * @param string $module
	 * @param array  $data
	 *
	 * @return \Settings_Vtiger_Field_Model
	 */
	public static function init($module = 'Vtiger', $data = [])
	{
		$modelClassName = \Vtiger_Loader::getComponentClassName('Model', 'Module', $module);
		$moduleInstance = new $modelClassName();
		$modelClassName = \Vtiger_Loader::getComponentClassName('Model', 'Field', $module);
		$instance = new $modelClassName();
		$instance->setModule($moduleInstance);
		foreach ($data as $key => $value) {
			$instance->set($key, $value);
		}
		return $instance;
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @param bool $skipCheckingRole
	 *
	 * @return array List of picklist values if the field is of type picklist or multipicklist, null otherwise
	 */
	public function getPicklistValues($skipCheckingRole = false)
	{
		return $this->picklistValues;
	}

	/**
	 * Function to get list of modules the field refernced to.
	 *
	 * @return string[] list of modules for which field is refered to
	 */
	public function getReferenceList()
	{
		return $this->referenceList;
	}

	/**
	 * Function to check if the field is named field of the module.
	 *
	 * @return bool - True/False
	 */
	public function isNameField()
	{
		return false;
	}

	/**
	 * Function to check whether the current field is read-only.
	 *
	 * @return bool - true/false
	 */
	public function isReadOnly()
	{
		return $this->isReadOnly ?? false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValidator()
	{
		return $this->validator ?? parent::getValidator();
	}
}
