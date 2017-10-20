<?php

/**
 * UIType Record Number Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RecordNumber_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Fields\RecordNumber::incrementNumber(\App\Module::getModuleId($recordModel->getModuleName()));
	}
}
