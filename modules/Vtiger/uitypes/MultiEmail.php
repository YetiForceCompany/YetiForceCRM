<?php

/**
 * UIType multi email Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_MultiEmail_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		\App\DebugerEx::log($value);
		if (is_string($value)) {
			$value = \App\Json::decode($value);
			\App\DebugerEx::log($value);
		}
		foreach ($value as $item) {
			if (!array_key_exists('e', $item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
			}
			if (!filter_var($item['e'], FILTER_VALIDATE_EMAIL) || $item['e'] !== filter_var($item['e'], FILTER_SANITIZE_EMAIL)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
			}
		}
		parent::validate($value, $isUserFormat);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiEmail.tpl';
	}
}
