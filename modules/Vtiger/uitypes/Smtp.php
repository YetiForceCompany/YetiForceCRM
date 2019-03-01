<?php

/**
 * UIType Smtp Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_Smtp_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!isset(\App\Mail::getAll()[$value])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return parent::getDisplayValue(
			\App\Mail::getAll()[$value]['name'] ?? '',
			$record,
			$recordModel,
			$rawText,
			$length
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPicklistValues()
	{
		return \App\Mail::getAll();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Smtp.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['integer', 'smallint'];
	}
}
