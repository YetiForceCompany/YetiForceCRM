<?php
/**
 * MultiListFields query field.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * MultiListFieldsField class.
 */
class MultiListFieldsField extends MultipicklistField
{
	/** {@inheritdoc} */
	protected $separator = ',';
}
