<?php

/**
 * UIType Base Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Base_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if ($isUserFormat) {
			$value = \App\Purifier::decodeHtml($value);
		}
		$fieldName = $this->getFieldModel()->getName();
		if (!is_numeric($value) && (\is_string($value) && 'uid' !== $fieldName && $value !== strip_tags($value))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $fieldName . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		if (($maximumLength = $this->getFieldModel()->getMaxValue()) && App\TextUtils::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $fieldName . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate = true;
	}
}
