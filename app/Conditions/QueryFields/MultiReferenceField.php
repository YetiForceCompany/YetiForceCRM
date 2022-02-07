<?php
/**
 * MultiReference Query Field.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * MultiReferenceField class.
 */
class MultiReferenceField extends MultipicklistField
{
	/**
	 * Separator.
	 *
	 * @var string
	 */
	protected $separator = ',';

	/** {@inheritdoc} */
	public function getOperator()
	{
		return 'a' === $this->operator ? 'c' : $this->operator;
	}
}
