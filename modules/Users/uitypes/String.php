<?php
/**
 * UIType Boolean Field Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Users_String_UIType class.
 */
class Users_String_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (106 === $this->getFieldModel()->getUIType()) {
			$textLength = App\TextUtils::getTextLength($value);
			$maximumLength = $this->getFieldModel()->get('maximumlength');
			$range = explode(',', $maximumLength);
			if ((int) $range[0] > $textLength || (int) $range[1] < $textLength || !preg_match('/^[a-zA-Z0-9_.@-]+$/', $value)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			$this->validate = true;
		} else {
			parent::validate($value, $isUserFormat);
		}
	}
}
