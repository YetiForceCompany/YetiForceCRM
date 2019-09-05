<?php

namespace App\Map\Address;

/**
 * Base caching class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
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
	 * Custom Fields.
	 *
	 * @var array
	 */
	public $customFields = [];

	/**
	 * Provider link.
	 *
	 * @var string
	 */
	public $link = '';

	/**
	 * Function checks if provider is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		$provider = \App\Map\Address::getConfig()[$this->getName()] ?? 0;
		return (bool) $provider ? $provider['active'] ?? 0 : 0;
	}

	/**
	 * Function checks if provider is set.
	 *
	 * @return bool
	 */
	public function isSet()
	{
		return true;
	}

	/**
	 * Get provider custom fields.
	 *
	 * @return array
	 */
	public function getCustomFields(): array
	{
		return $this->customFields;
	}

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
	 * Find address.
	 *
	 * @param $value string
	 *
	 * @return array
	 */
	abstract public function find($value);
}
