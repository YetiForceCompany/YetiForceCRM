<?php

namespace App\Conditions\RecordFields;

/**
 * Range time condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RangeTimeField extends BaseField
{
	use \App\Conditions\RecordTraits\Comparison;
	use \App\Conditions\RecordTraits\ComparisonField;
}
