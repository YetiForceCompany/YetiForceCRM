<?php
/**
 * UIType Boolean Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Users_String_UIType class
 */
class Users_String_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if ($this->get('field')->getUIType() === 106) {
			if (!preg_match('/^[a-zA-Z0-9_.@]{3,32}$/', $value)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
			}
			$this->validate = true;
		} else {
			parent::validate($value, $isUserFormat);
		}
	}
}
