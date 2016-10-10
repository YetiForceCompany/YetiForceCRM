<?php

/**
 * Uitype model 
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_String_UIType extends Vtiger_Base_UIType
{

	/**
	 * If edit by ajax
	 * @return boolean
	 */
	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $value
	 * @param int $record id record
	 * @param <Vtiger_Record_Model> $recordInstance 
	 * @param mixed $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return $recordInstance->getParseField($this->get('field')->getName());
	}
}
