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
	 * Function to set parent to this model
	 * @param Vtiger_Record_Model
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Function to get parent
	 * @return Vtiger_Record_Model
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Function to set Field instance
	 * @param Vtiger_Field_Model
	 */
	public function setFieldInstance($fieldModel)
	{
		$this->fieldInstance = $fieldModel;
		return $this;
	}

	/**
	 * Function to get Field instance
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstance()
	{
		return $this->fieldInstance;
	}

	/**
	 * Function to get Old value of this Field
	 * @return string
	 */
	public function getOldValue()
	{
		$value = $this->getDisplayValue($this->get('prevalue'));
		if ($this->getFieldInstance()->getFieldDataType() != 'text') {
			return $value;
		}
		$teaser = vtlib\Functions::textLength($value, AppConfig::module('ModTracker', 'TEASER_TEXT_LENGTH'));
		if (substr($teaser, -3) == '...') {
			$value = App\Purifier::purify(vtlib\Functions::removeHtmlTags(array('br', 'link', 'style', 'a', 'img', 'script', 'base'), $value));
			$this->set('fullPreValue', $value);
		}
		return $teaser;
	}

	/**
	 * Function to get new(updated) value of this Field
	 * @return string
	 */
	public function getNewValue()
	{
		$value = $this->getDisplayValue($this->get('postvalue'));
		if ($this->getFieldInstance()->getFieldDataType() != 'text') {
			return $value;
		}
		$teaser = vtlib\Functions::textLength($value, AppConfig::module('ModTracker', 'TEASER_TEXT_LENGTH'));
		if (substr($teaser, -3) == '...') {
			$value = App\Purifier::purify(vtlib\Functions::removeHtmlTags(array('br', 'link', 'style', 'a', 'img', 'script', 'base'), $value));
			$this->set('fullPostValue', $value);
		}
		return $teaser;
	}

	/**
	 * Function to get name
	 * @return <type>
	 */
	public function getName()
	{
		return $this->getFieldInstance()->get('label');
	}

	/**
	 * Function to get Display Value
	 * @param <type> $value
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return $this->getFieldInstance()->getDisplayValue($value, $record, $recordInstance, $rawText);
	}

	/**
	 * Function returns the module name of the field
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->getParent()->getParent()->getModule()->getName();
	}
}
