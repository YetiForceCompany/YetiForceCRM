<?php

namespace App\Db\Drivers;

/**
 * ColumnSchemaBuilder is the schema builder for MySQL databases.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
trait ColumnSchemaBuilderTrait
{
	/** @var bool */
	protected $autoIncrement = false;

	/**
	 * Assign column as autoincrement.
	 *
	 * @return $this
	 */
	public function autoIncrement()
	{
		$this->autoIncrement = true;
		return $this;
	}

	/**
	 * Builds the full string for the column's schema.
	 *
	 * @return string
	 */
	public function __toString()
	{
		switch ($this->getTypeCategory()) {
			case self::CATEGORY_PK:
				$format = '{type}{length}{check}{comment}{append}{pos}';
				break;
			case self::CATEGORY_NUMERIC:
				$format = '{type}{length}{unsigned}{notnull}{unique}{default}{check}{autoIncrement}{comment}{append}{pos}';
				break;
			default:
				$format = '{type}{length}{notnull}{unique}{default}{check}{comment}{append}{pos}';
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
	 * Get object var.
	 *
	 * @param string $type
	 *
	 * @return mixed|null
	 */
	public function get($type)
	{
		return $this->$type ?? null;
	}

	/**
	 * Set object var.
	 *
	 * @param string $type
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set($type, $value)
	{
		$this->$type = $value;
		return $this;
	}

	/**
	 * Return the variable isNotNull.
	 *
	 * @return bool
	 */
	protected function getisNotNull()
	{
		return $this->isNotNull;
	}

	/**
	 * Return the variable default.
	 *
	 * @return int|string
	 */
	protected function getdefault()
	{
		return $this->default;
	}

	/**
	 * Return the variable isUnsigned.
	 *
	 * @return bool
	 */
	protected function getisUnsigned()
	{
		return $this->isUnsigned;
	}

	/**
	 * Builds the autoincrement string for column. Defaults to unsupported.
	 *
	 * @return string
	 */
	protected function buildAutoIncrementString()
	{
		return '';
	}
}
