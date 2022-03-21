<?php
/**
 *  MultiAttachment Query Field File.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * MultiAttachment Query Field Class.
 */
class MultiAttachmentField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorY(): array
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), ''],
		];
	}

	/** {@inheritdoc} */
	public function operatorNy(): array
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), ''],
		];
	}
}
