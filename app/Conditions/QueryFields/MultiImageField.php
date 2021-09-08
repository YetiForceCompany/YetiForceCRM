<?php

namespace App\Conditions\QueryFields;

/**
 * MultiImage Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MultiImageField extends BaseField
{
	/**
	 * {@inheritdoc}
	 */
	public function operatorY()
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), '[]'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function operatorNy()
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), '[]'],
		];
	}
}
