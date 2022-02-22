<?php

namespace App\Conditions\RecordFields;

/**
 * Integer condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IntegerField extends BaseField
{
	use \App\Conditions\RecordTraits\Comparison;
	use \App\Conditions\RecordTraits\ComparisonField;
}
