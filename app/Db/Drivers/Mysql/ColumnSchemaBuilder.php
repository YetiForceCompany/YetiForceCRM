<?php
/**
 * Column schema builder file is the schema builder for MySQL databases.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Db\Drivers\Mysql;

/**
 * Column schema builder class is the schema builder for MySQL databases.
 */
class ColumnSchemaBuilder extends \yii\db\mysql\ColumnSchemaBuilder
{
	use \App\Db\Drivers\ColumnSchemaBuilderTrait;

	/**
	 * Builds the full string for the column's schema.
	 *
	 * @return string
	 */
	public function __toString()
	{
		switch ($this->getTypeCategory()) {
			case self::CATEGORY_PK:
				$format = '{type}{length}{comment}{check}{append}{pos}';
				break;
			case self::CATEGORY_NUMERIC:
				$format = '{type}{length}{unsigned}{notnull}{default}{unique}{autoIncrement}{comment}{append}{pos}{check}';
				break;
			default:
				$format = '{type}{length}{notnull}{default}{unique}{comment}{append}{pos}{check}';
		}
		return $this->buildCompleteString($format);
	}

	/**
	 * Returns the complete column definition from input format.
	 *
	 * @param string $format the format of the definition.
	 *
	 * @return string a string containing the complete column definition.
	 *
	 * @since 2.0.8
	 */
	protected function buildCompleteString($format)
	{
		$placeholderValues = [
			'{type}' => $this->type,
			'{length}' => $this->buildLengthString(),
			'{unsigned}' => $this->buildUnsignedString(),
			'{notnull}' => $this->buildNotNullString(),
			'{unique}' => $this->buildUniqueString(),
			'{default}' => $this->buildDefaultString(),
			'{autoIncrement}' => $this->buildAutoIncrementString(),
			'{check}' => $this->buildCheckString(),
			'{comment}' => $this->buildCommentString(),
			'{pos}' => $this->isFirst ? $this->buildFirstString() : $this->buildAfterString(),
			'{append}' => $this->buildAppendString(),
		];
		return strtr($format, $placeholderValues);
	}

	/**
	 * Builds the autoincrement string for column.
	 *
	 * @return string
	 */
	protected function buildAutoIncrementString()
	{
		return $this->autoIncrement ? ' AUTO_INCREMENT' : '';
	}
}
