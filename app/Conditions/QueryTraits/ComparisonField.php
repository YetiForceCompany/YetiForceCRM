<?php
/**
 * Comparison field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryTraits;

/**
 * Comparison field class.
 */
trait ComparisonField
{
	/**
	 * Less than field operator.
	 *
	 * @return array
	 */
	public function operatorLf(): array
	{
		return ['<', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromValue())];
	}

	/**
	 * Greater than field operator.
	 *
	 * @return array
	 */
	public function operatorGf(): array
	{
		return ['>', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromValue())];
	}

	/**
	 * Less than field or equal operator.
	 *
	 * @return array
	 */
	public function operatorMf(): array
	{
		return ['<=', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromValue())];
	}

	/**
	 * Greater than field or equal operator.
	 *
	 * @return array
	 */
	public function operatorHf(): array
	{
		return ['>=', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromValue())];
	}
}
