<?php
/**
 * Query comparison field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryTraits;

/**
 * Query comparison field class.
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
		return ['<', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromSource())];
	}

	/**
	 * Greater than field operator.
	 *
	 * @return array
	 */
	public function operatorGf(): array
	{
		return ['>', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromSource())];
	}

	/**
	 * Less than field or equal operator.
	 *
	 * @return array
	 */
	public function operatorMf(): array
	{
		return ['<=', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromSource())];
	}

	/**
	 * Greater than field or equal operator.
	 *
	 * @return array
	 */
	public function operatorHf(): array
	{
		return ['>=', $this->getColumnName(), new \yii\db\Expression($this->getColumnNameFromSource())];
	}
}
