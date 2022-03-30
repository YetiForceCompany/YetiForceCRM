<?php
/**
 * Multi list fields condition record field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Multi list fields condition record field class.
 */
class MultiListFieldsField extends MultipicklistField
{
	/** {@inheritdoc} */
	protected $separator = ',';
}
