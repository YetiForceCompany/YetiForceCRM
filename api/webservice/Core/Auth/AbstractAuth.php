<?php
/**
 * Base abstract authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Core\Auth;

/**
 * Base abstract authorization class.
 */
abstract class AbstractAuth
{
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
	 * Set server data.
	 *
	 * @return self
	 */
	abstract protected function setServer(): self;
}
