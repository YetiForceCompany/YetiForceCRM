<?php

/**
 * Updater Field Class
 * @package YetiForce.Helpers
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_UpdaterField_Helper
{

	private $fieldModel = false;

	/**
	 * Function to get instance of class
	 * @return \self
	 */
	public static function getInstance()
	{
		return new self;
	}

	/**
	 * Function to set field model
	 * @param Vtiger_Field_Model $fieldModel
	 */
	public function setFieldModel(Vtiger_Field_Model $fieldModel)
	{
		$this->fieldModel = $fieldModel;
	}

	/**
	 * Function to get value for field
	 * @return mixed
	 * @throws Exception\NotAllowedMethod
	 */
	public function getValue()
	{
		$fieldName = $this->fieldModel->getFieldName();
		$functionName = 'get' . ucwords($fieldName) . 'Value';
		if (!method_exists($this, $functionName)) {
			throw new Exception\NotAllowedMethod();
		}
		return $this->$functionName();
	}
}
