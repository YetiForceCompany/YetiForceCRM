<?php
/**
 * Base abstract authorization file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core\Auth;

/**
 * Base abstract authorization class.
 */
abstract class AbstractAuth
{
	/** @var array Current server details (w_#__servers) */
	protected $currentServer;

	/** @var \Api\Controller Controller instance */
	protected $api;

	/**
	 * Set api controller.
	 *
	 * @param \Api\Controller $api
	 *
	 * @return void
	 */
	public function setApi(\Api\Controller $api): void
	{
		$this->api = $api;
	}

	/**
	 * Authenticate function.
	 *
	 * @param string $realm
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	abstract protected function authenticate(string $realm): bool;

	/**
	 * Get current server details.
	 *
	 * @return array
	 */
	public function getCurrentServer(): array
	{
		return $this->currentServer;
	}
}
