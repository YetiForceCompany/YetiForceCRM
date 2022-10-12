<?php
/**
 * Mail server record condition field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Mail server record condition field class.
 */
class MailServerField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		return \in_array($this->getValue(), explode('##', $this->value));
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		return !\in_array($this->getValue(), explode('##', $this->value));
	}
}
