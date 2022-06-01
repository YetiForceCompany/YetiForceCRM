<?php

/**
 * Field Model Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Vtiger_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to check if the field is named field of the module.
	 *
	 * @return bool
	 */
	public function isNameField(): bool
	{
		return false;
	}

	/**
	 * Function to check whether the current field is read-only.
	 *
	 * @return bool
	 */
	public function isReadOnly(): bool
	{
		return $this->isReadOnly ?? false;
	}

	/** {@inheritdoc} */
	public function getValidator()
	{
		return $this->validator ?? parent::getValidator();
	}

	/** {@inheritdoc} */
	public function getModuleName()
	{
		$moduleName = '';
		if (!empty($this->module) && method_exists($this->module, 'getParentName')) {
			$moduleName = $this->module->getParentName() . ':' . $this->module->getName();
		} else {
			$moduleName = parent::getModuleName();
		}
		return $moduleName;
	}

	/** {@inheritdoc} */
	public function getFieldDataType()
	{
		if (!isset($this->fieldDataType) && 62 === $this->uitype) {
			$this->fieldDataType = 'icon';
		}

		return parent::getFieldDataType();
	}
}
