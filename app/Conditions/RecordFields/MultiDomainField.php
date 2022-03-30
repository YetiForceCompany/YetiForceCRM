<?php
/**
 * Multi domain condition record field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Multi domain condition record field class.
 */
class MultiDomainField extends BaseField
{
	/** {@inheritdoc} */
	public function getValue()
	{
		return trim(parent::getValue(), ',');
	}
}
