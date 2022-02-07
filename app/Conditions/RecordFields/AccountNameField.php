<?php
/**
 * Account name record condition field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Account name record condition field class.
 */
class AccountNameField extends BaseField
{
	/** {@inheritdoc} */
	public function getValue()
	{
		$recordValue = explode('|##|', parent::getValue());
		if (\count($recordValue) > 1) {
			$recordValue = trim("$recordValue[0] $recordValue[1]");
		} else {
			$recordValue = parent::getValue();
		}
		return $recordValue;
	}
}
