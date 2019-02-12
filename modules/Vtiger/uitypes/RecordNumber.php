<?php

/**
 * UIType Record Number Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RecordNumber_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && App\TextParser::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToSave($value, Vtiger_Record_Model $recordModel)
	{
		$recordNumberInstance = \App\Fields\RecordNumber::getInstance($recordModel->getModuleName());
		$recordNumberInstance->setRecord($recordModel);
		if ($recordNumberInstance->isNewSequence()) {
			$value = $recordNumberInstance->getIncrementNumber();
			$recordModel->set($this->getFieldModel()->getFieldName(), $value);
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}
}
