<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModTracker_Field_Model extends Vtiger_Record_Model
{
	/**
	 * Function to set parent to this model.
	 *
	 * @param Vtiger_Record_Model
	 * @param mixed $parent
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Function to get parent.
	 *
	 * @return Vtiger_Record_Model
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Function to set Field instance.
	 *
	 * @param Vtiger_Field_Model
	 * @param mixed $fieldModel
	 */
	public function setFieldInstance($fieldModel)
	{
		$this->fieldInstance = $fieldModel;

		return $this;
	}

	/**
	 * Function to get Field instance.
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstance()
	{
		return $this->fieldInstance;
	}

	/**
	 * Function to get Old value of this Field.
	 *
	 * @return string
	 */
	public function getOldValue()
	{
		return $this->fieldInstance->getUITypeModel()->getHistoryDisplayValue($this->get('prevalue'), $this->parent);
	}

	/**
	 * Function to get new(updated) value of this Field.
	 *
	 * @return string
	 */
	public function getNewValue()
	{
		return $this->fieldInstance->getUITypeModel()->getHistoryDisplayValue($this->get('postvalue'), $this->parent);
	}

	/**
	 * Function to get name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->getFieldInstance()->get('label');
	}

	/**
	 * Function returns the module name of the field.
	 *
	 * @return string
	 */
	public function getModuleName(): string
	{
		return $this->getParent()->getModule()->getName();
	}
}
