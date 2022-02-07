<?php
/**
 * Column schema builder file is the schema builder for MySQL databases.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Db\Drivers;

/**
 * Column schema builder trait is the schema builder for MySQL databases.
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
	 * Get object var.
	 *
	 * @param string $type
	 *
	 * @return mixed|null
	 */
	public function get($type)
	{
		return $this->{$type} ?? null;
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
		$this->{$type} = $value;
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
	 * Return the variable autoIncrement.
	 *
	 * @return bool
	 */
	protected function getautoIncrement()
	{
		return $this->autoIncrement;
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
