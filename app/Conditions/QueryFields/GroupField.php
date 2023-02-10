<?php
/**
 * Group Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

class GroupField extends BaseField
{
	/** {@inheritdoc} */
	public function getValue()
	{
		if (\is_array($this->value)) {
			return $this->value;
		}
		return explode('##', $this->value);
	}
}
