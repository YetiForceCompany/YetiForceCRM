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
		$this->config = \App\Map\Address::getConfig()[$name] ?? [];
	}

	/**
	 * Provider name.
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Config data.
	 *
	 * @var array
	 */
	public $config;
	/**
	 * Custom Fields.
	 *
	 * @var array
	 */
	public $customFields = [];

	/**
	 * Provider documentation url address.
	 *
	 * @var string
	 */
	public $docUrl = '';

	/**
	 * Function checks if provider is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return (bool) ($this->config['active'] ?? 0);
	}

	/**
	 * Function checks if  provider been configured?
	 *
	 * @return bool
	 */
	public function isConfigured()
	{
		return !empty($this->config['key']);
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
	 * Get provider documentation url address.
	 *
	 * @return string
	 */
	public function getDocUrl(): string
	{
		return $this->docUrl;
	}

	/**
	 * Validate configuration.
	 *
	 * @return bool
	 */
	public function validate(): bool
	{
		return $this->isConfigured();
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
