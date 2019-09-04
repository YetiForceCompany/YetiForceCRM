<?php

namespace App\Map\Address;

/**
 * Base caching class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Base
{
	/**
	 * Construct.
	 *
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * Provider name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Provider link.
	 *
	 * @var string
	 */
	public $link = '';

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get provider link.
	 *
	 * @return string
	 */
	public function getLink(): string
	{
		return $this->link;
	}

	/**
	 * Function checks if provider is active.
	 *
	 * @return bool
	 */
	public static function isActive()
	{
		return true;
	}

	/**
	 * Find address.
	 *
	 * @param $value string
	 *
	 * @return array
	 */
	abstract public function find($value);
}
