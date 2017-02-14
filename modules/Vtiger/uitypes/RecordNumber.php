<?php

/**
 * UIType Record Number Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RecordNumber_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Fields\RecordNumber::incrementNumber(\App\Module::getModuleId($recordModel->getModuleName()));
	}
}
