<?php

namespace App\Conditions\QueryFields;

/**
 * MultiImage Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MultiImageField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorY(): array
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), '[]'],
		];
	}

	/** {@inheritdoc} */
	public function operatorNy(): array
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), '[]'],
		];
	}
}
