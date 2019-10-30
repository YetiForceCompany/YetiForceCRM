<?php
/**
 * MultiReference Query Field.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * {@inheritdoc}
	 */
	public function operatorA()
	{
		return $this->operatorC();
	}
}
